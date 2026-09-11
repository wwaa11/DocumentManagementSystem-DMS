<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Services\StaffApiClient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApproverAdminService
{
    public function __construct(private StaffApiClient $staffApi) {}

    /**
     * @return array{depts: Collection, datas: Collection, noti: array{count: int, error: int, assigned: int, err_list: array<int, string>}}
     */
    public function listApprovers(): array
    {
        $depts = DB::connection('staff')
            ->table('departments')
            ->where('department', '!=', 'Doctor')
            ->orderBy('department', 'asc')
            ->pluck('department', 'id');

        $levelOneApprovers = DB::connection('staff')
            ->table('approvers')
            ->select('department_id', DB::raw('MAX(id) as id'))
            ->where('level', 1)
            ->groupBy('department_id');

        $datas = DB::connection('staff')
            ->table('departments')
            ->leftJoinSub($levelOneApprovers, 'level_one_approvers', function ($join): void {
                $join->on('departments.id', '=', 'level_one_approvers.department_id');
            })
            ->leftJoin('approvers', 'approvers.id', '=', 'level_one_approvers.id')
            ->leftJoin('users', 'approvers.userid', '=', 'users.userid')
            ->leftJoin('emails', 'users.userid', '=', 'emails.userid')
            ->where('departments.department', '!=', 'Doctor')
            ->select(
                'departments.id',
                'departments.department',
                'departments.updated_at as dept_last_update',
                'approvers.userid',
                'approvers.updated_at as last_update',
                'approvers.updated_userid as last_userid',
                'approvers.updated_username as last_username',
                'users.name',
                'emails.email',
                'users.position'
            )
            ->orderByRaw("CASE WHEN approvers.userid IS NULL OR approvers.userid = '-' THEN 0 ELSE 1 END")
            ->orderBy('departments.department', 'asc')
            ->get();

        $errList = [];

        foreach ($datas as $data) {
            $data->has_approver = $this->departmentHasApprover($data->userid);

            if (! $data->has_approver) {
                $errList[] = $data->department;
                $data->userid = null;
                $data->name = null;
                $data->email = null;
                $data->position = null;
                $data->last_update = null;
                $data->last_userid = null;
                $data->last_username = null;
            }
        }

        return [
            'depts' => $depts,
            'datas' => $datas,
            'noti' => [
                'count' => $datas->count(),
                'error' => count($errList),
                'assigned' => $datas->count() - count($errList),
                'err_list' => $errList,
            ],
        ];
    }

    public function fetchUser(string $userid): array
    {
        $response = $this->staffApi->getUser($userid);

        if ($response->failed()) {
            return ['error' => 'Failed to fetch user data', 'status' => 500];
        }

        return [
            'success' => true,
            'user' => $response->json()['user'],
            'status' => 200,
        ];
    }

    /**
     * @param  array{department: string, userid: string, name: string, position: string, email: string}  $validated
     */
    public function updateApprover(array $validated): void
    {
        $dept = DB::connection('staff')
            ->table('departments')
            ->where('department', $validated['department'])
            ->orderBy('department', 'asc')
            ->first();

        if (! $dept) {
            throw new InvalidArgumentException('ไม่พบแผนกนี้ในระบบ');
        }

        $existing = DB::connection('staff')
            ->table('approvers')
            ->where('department_id', $dept->id)
            ->where('level', 1)
            ->first();

        $payload = [
            'userid' => $validated['userid'],
            'updated_at' => now(),
            'updated_userid' => auth()->user()->userid,
            'updated_username' => auth()->user()->name,
        ];

        if ($existing) {
            DB::connection('staff')
                ->table('approvers')
                ->where('id', $existing->id)
                ->update($payload);
        } else {
            DB::connection('staff')->table('approvers')->insert([
                ...$payload,
                'department_id' => $dept->id,
                'level' => 1,
                'created_at' => now(),
            ]);
        }

        $this->upsertStaffEmail($validated['userid'], $validated['email']);
    }

    private function departmentHasApprover(mixed $userid): bool
    {
        return filled($userid) && $userid !== '-';
    }

    private function upsertStaffEmail(string $userid, string $email): void
    {
        $emailsQuery = DB::connection('staff')->table('emails');

        if ($emailsQuery->where('userid', $userid)->exists()) {
            DB::connection('staff')
                ->table('emails')
                ->where('userid', $userid)
                ->update([
                    'email' => $email,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::connection('staff')->table('emails')->insert([
            'userid' => $userid,
            'email' => $email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function allRoleLabels(): array
    {
        return [
            'admin' => 'Admin',
            'it' => 'IT',
            'it-hardware' => 'IT Hardware',
            'it-approve' => 'IT Approve',
            'it-hardware-approve' => 'IT Hardware + Approve',
            'lab' => 'Lab',
            'lab-approve' => 'Lab Approve',
            'pac' => 'PAC',
            'pac-approve' => 'PAC Approve',
            'heartstream' => 'Heartstream',
            'heartstream-approve' => 'Heartstream Approve',
            'register' => 'Register',
            'register-approve' => 'Register Approve',
            'purchase' => 'Purchase',
            'purchase-approve' => 'Purchase Approve',
            'purchase-head' => 'Purchase Head',
            'media' => 'Media',
            'media-head' => 'Media Head',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function roleFamilies(): array
    {
        return [
            'it' => ['it', 'it-hardware', 'it-approve', 'it-hardware-approve'],
            'lab' => ['lab', 'lab-approve'],
            'pac' => ['pac', 'pac-approve'],
            'heartstream' => ['heartstream', 'heartstream-approve'],
            'register' => ['register', 'register-approve'],
            'purchase' => ['purchase', 'purchase-approve', 'purchase-head'],
            'media' => ['media', 'media-head'],
        ];
    }

    public function canManageRoles(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user || blank($user->role)) {
            return false;
        }

        $assignable = $this->assignableRoleKeys((string) $user->role);

        return $assignable === null || $assignable !== [];
    }

    public function canManageApprovers(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user || blank($user->role)) {
            return false;
        }

        return in_array((string) $user->role, ['admin', 'dev', 'it', 'it-hardware-approve'], true);
    }

    /**
     * Roles the actor may assign. null means unrestricted (admin/dev).
     *
     * @return list<string>|null
     */
    public function assignableRoleKeys(string $actorRole): ?array
    {
        if (in_array($actorRole, ['admin', 'dev'], true)) {
            return null;
        }

        foreach ($this->roleFamilies() as $roles) {
            $managers = array_values(array_filter(
                $roles,
                fn (string $role): bool => str_ends_with($role, '-approve') || str_ends_with($role, '-head')
            ));

            if (in_array($actorRole, $managers, true)) {
                return array_values(array_unique(array_merge(['user'], $roles)));
            }
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    public function roleLabelsForActor(string $actorRole): array
    {
        $all = $this->allRoleLabels();
        $assignable = $this->assignableRoleKeys($actorRole);

        if ($assignable === null) {
            return $all;
        }

        return array_filter(
            $all,
            fn (string $label, string $role): bool => in_array($role, $assignable, true),
            ARRAY_FILTER_USE_BOTH
        );
    }

    public function canAssignRole(string $actorRole, string $targetCurrentRole, string $newRole): bool
    {
        $assignable = $this->assignableRoleKeys($actorRole);

        if ($assignable === null) {
            return $newRole === 'user' || array_key_exists($newRole, $this->allRoleLabels());
        }

        if ($assignable === []) {
            return false;
        }

        if (! in_array($newRole, $assignable, true)) {
            return false;
        }

        return in_array($targetCurrentRole, $assignable, true);
    }

    /**
     * Import a staff user into the local users table.
     *
     * @return array{status: string, user: ?User, message: ?string}
     */
    public function importUserFromStaff(string $userid): array
    {
        $userid = trim($userid);

        $existing = User::query()->where('userid', $userid)->first();
        if ($existing) {
            return [
                'status' => 'exists',
                'user' => $existing,
                'message' => null,
            ];
        }

        $response = $this->staffApi->getUser($userid);

        if (! $response->successful()) {
            return [
                'status' => 'api_error',
                'user' => null,
                'message' => 'ไม่สามารถเชื่อมต่อ Staff API ได้',
            ];
        }

        $responseData = $response->json();

        if (($responseData['status'] ?? null) != 1 || empty($responseData['user'])) {
            return [
                'status' => 'not_found',
                'user' => null,
                'message' => 'ไม่พบรหัสพนักงานนี้ใน Staff API',
            ];
        }

        $staffUser = $responseData['user'];

        $user = User::query()->create([
            'userid' => $userid,
            'name' => $staffUser['name'] ?? $userid,
            'position' => $staffUser['position'] ?? '-',
            'department' => $staffUser['department'] ?? '-',
            'division' => $staffUser['division'] ?? '-',
            'email' => $staffUser['email'] ?? null,
            'role' => 'user',
        ]);

        return [
            'status' => 'imported',
            'user' => $user,
            'message' => 'พบจาก Staff API และเพิ่มผู้ใช้แล้ว',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function roleTypeLabels(): array
    {
        return [
            'admin' => 'Admin',
            'it' => 'IT',
            'purchase' => 'Purchase',
            'media' => 'Media',
            'lab' => 'Lab',
            'pac' => 'PAC',
            'heartstream' => 'Heartstream',
            'register' => 'Register',
            'other' => 'Other',
        ];
    }

    public function roleTypeKey(string $role): string
    {
        if (in_array($role, ['admin', 'dev'], true)) {
            return 'admin';
        }

        foreach ($this->roleFamilies() as $type => $roles) {
            if (in_array($role, $roles, true)) {
                return $type;
            }
        }

        return 'other';
    }

    /**
     * @return array{
     *     users: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     groupedUsers: array<string, array{label: string, users: list<User>}>,
     *     roles: array<string, string>,
     *     allRoleLabels: array<string, string>,
     *     departments: list<string>,
     *     search: ?string,
     *     filter: string,
     *     roleFilter: ?string,
     *     canSetUser: bool,
     *     scoped: bool,
     *     canManageExtendedPermissions: bool,
     *     stats: array{total: int, roles: int, course: int, document: int},
     *     apiNotice: ?array{status: string, message: ?string}
     * }
     */
    public function listRoles(?string $search, ?string $filter = null, ?string $roleFilter = null): array
    {
        /** @var User $actor */
        $actor = auth()->user();
        $roles = $this->roleLabelsForActor($actor->role);
        $allRoleLabels = $this->allRoleLabels();
        $assignable = $this->assignableRoleKeys($actor->role);
        $canSetUser = $assignable === null || in_array('user', $assignable, true);
        $scoped = $assignable !== null;
        $canManageExtendedPermissions = in_array((string) $actor->role, ['admin', 'dev'], true);
        $apiNotice = null;
        $search = filled($search) ? trim($search) : null;
        $filter = in_array($filter, ['all', 'roles', 'course', 'document'], true) ? $filter : 'all';
        $allowedRoleFilters = array_keys($roles);

        if ($canSetUser && ! in_array('user', $allowedRoleFilters, true)) {
            $allowedRoleFilters = array_merge(['user'], $allowedRoleFilters);
        }

        $roleFilter = filled($roleFilter) && in_array($roleFilter, $allowedRoleFilters, true)
            ? $roleFilter
            : null;

        $query = $this->permissionUsersQuery($search, $assignable, $canManageExtendedPermissions, $filter, $roleFilter);

        $stats = [
            'total' => (clone $query)->count(),
            'roles' => (clone $query)->where('role', '!=', 'user')->count(),
            'course' => (clone $query)->where('can_create_course', true)->count(),
            'document' => (clone $query)->where('can_view_department_documents', true)->count(),
        ];

        $users = $query->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();

        if ($users->isEmpty() && filled($search) && $this->looksLikeUserid($search)) {
            $existing = User::query()->where('userid', $search)->first();

            if ($existing) {
                if ($this->canDisplayUserForRoleAssignment($actor->role, $existing) || $canManageExtendedPermissions) {
                    $users = new LengthAwarePaginator(
                        collect([$existing]),
                        1,
                        20,
                        1,
                        ['path' => request()->url(), 'query' => request()->query()]
                    );
                    $stats = [
                        'total' => 1,
                        'roles' => $this->userHasElevatedRole($existing) ? 1 : 0,
                        'course' => $existing->can_create_course ? 1 : 0,
                        'document' => $existing->can_view_department_documents ? 1 : 0,
                    ];
                } else {
                    $apiNotice = [
                        'status' => 'out_of_scope',
                        'message' => 'พบผู้ใช้นี้ในระบบแล้ว แต่ไม่อยู่ในกลุ่มสิทธิ์ที่คุณจัดการได้',
                    ];
                }
            } else {
                $import = $this->importUserFromStaff($search);
                $apiNotice = [
                    'status' => $import['status'],
                    'message' => $import['message'],
                ];

                if ($import['status'] === 'imported' && $import['user'] instanceof User) {
                    $importedUser = $import['user'];
                    $users = new LengthAwarePaginator(
                        collect([$importedUser]),
                        1,
                        20,
                        1,
                        ['path' => request()->url(), 'query' => request()->query()]
                    );
                    $stats = [
                        'total' => 1,
                        'roles' => $this->userHasElevatedRole($importedUser) ? 1 : 0,
                        'course' => $importedUser->can_create_course ? 1 : 0,
                        'document' => $importedUser->can_view_department_documents ? 1 : 0,
                    ];
                }
            }
        }

        $groupedUsers = in_array($filter, ['all', 'roles'], true)
            ? $this->groupUsersByRole($users->items(), $allRoleLabels)
            : $this->groupUsersFlat($users->items(), $allRoleLabels);

        return [
            'users' => $users,
            'groupedUsers' => $groupedUsers,
            'roles' => $roles,
            'allRoleLabels' => $allRoleLabels,
            'departments' => $canManageExtendedPermissions ? $this->staffDepartments() : [],
            'search' => $search,
            'filter' => $filter,
            'roleFilter' => $roleFilter,
            'canSetUser' => $canSetUser,
            'scoped' => $scoped,
            'canManageExtendedPermissions' => $canManageExtendedPermissions,
            'stats' => $stats,
            'apiNotice' => $apiNotice,
        ];
    }

    /**
     * @return list<string>
     */
    private function staffDepartments(): array
    {
        return DB::connection('staff')
            ->table('departments')
            ->where('department', '!=', 'Doctor')
            ->orderBy('department')
            ->pluck('department')
            ->all();
    }

    private function userHasElevatedRole(User $user): bool
    {
        return ! in_array((string) $user->role, ['user', ''], true);
    }

    /**
     * @param  list<User>  $users
     * @return array<string, array{label: string, users: list<User>}>
     */
    private function groupUsersByRole(array $users, array $allRoleLabels): array
    {
        $grouped = [];

        foreach ($users as $user) {
            $role = (string) $user->role;

            if (! isset($grouped[$role])) {
                $grouped[$role] = [
                    'label' => $this->roleLabelForDisplay($role, $allRoleLabels),
                    'users' => [],
                ];
            }

            $grouped[$role]['users'][] = $user;
        }

        uasort(
            $grouped,
            fn (array $a, array $b): int => strcasecmp($a['label'], $b['label'])
        );

        return $grouped;
    }

    /**
     * @param  list<User>  $users
     * @return array<string, array{label: string, users: list<User>}>
     */
    private function groupUsersFlat(array $users, array $allRoleLabels): array
    {
        if ($users === []) {
            return [];
        }

        return [
            'all' => [
                'label' => 'ผู้ใช้ทั้งหมด',
                'users' => $users,
            ],
        ];
    }

    private function roleLabelForDisplay(string $role, array $allRoleLabels): string
    {
        if ($role === 'dev') {
            return 'Admin';
        }

        return $allRoleLabels[$role] ?? $role;
    }

    /**
     * @param  Collection<int, User>  $users
     * @return array<string, array{label: string, users: list<User>}>
     */
    private function groupUsersByRoleType(Collection $users): array
    {
        $typeLabels = $this->roleTypeLabels();
        $typeOrder = array_keys($typeLabels);
        $grouped = [];

        foreach ($users as $user) {
            $type = $this->roleTypeKey((string) $user->role);

            if (! isset($grouped[$type])) {
                $grouped[$type] = [
                    'label' => $typeLabels[$type] ?? $type,
                    'users' => [],
                ];
            }

            $grouped[$type]['users'][] = $user;
        }

        uksort($grouped, function (string $a, string $b) use ($typeOrder): int {
            $posA = array_search($a, $typeOrder, true);
            $posB = array_search($b, $typeOrder, true);

            return ($posA === false ? 999 : $posA) <=> ($posB === false ? 999 : $posB);
        });

        return $grouped;
    }

    /**
     * @param  list<string>|null  $assignable
     */
    private function permissionUsersQuery(
        ?string $search,
        ?array $assignable,
        bool $includePermissionUsers,
        string $filter = 'all',
        ?string $roleFilter = null
    ) {
        return User::query()
            ->when(filled($search), function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('userid', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(! filled($search) && $assignable === null && $includePermissionUsers && $filter === 'all' && ! filled($roleFilter), function ($query): void {
                $query->where(function ($query): void {
                    $query->where('role', '!=', 'user')
                        ->orWhere('can_create_course', true)
                        ->orWhere('can_view_department_documents', true);
                });
            })
            ->when(! filled($search) && $assignable === null && ! $includePermissionUsers, function ($query): void {
                $query->where('role', '!=', 'user');
            })
            ->when(! filled($search) && $assignable !== null && ! filled($roleFilter), function ($query) use ($assignable): void {
                $displayRoles = array_values(array_filter(
                    $assignable,
                    fn (string $role): bool => $role !== 'user'
                ));
                $query->whereIn('role', $displayRoles);
            })
            ->when(! filled($search) && $filter === 'roles' && ! filled($roleFilter), function ($query): void {
                $query->where('role', '!=', 'user');
            })
            ->when(! filled($search) && $filter === 'course', function ($query): void {
                $query->where('can_create_course', true);
            })
            ->when(! filled($search) && $filter === 'document', function ($query): void {
                $query->where('can_view_department_documents', true);
            })
            ->when(filled($roleFilter), function ($query) use ($roleFilter): void {
                if ($roleFilter === 'admin') {
                    $query->whereIn('role', ['admin', 'dev']);

                    return;
                }

                $query->where('role', $roleFilter);
            });
    }

    private function canDisplayUserForRoleAssignment(string $actorRole, User $target): bool
    {
        $assignable = $this->assignableRoleKeys($actorRole);

        if ($assignable === null) {
            return true;
        }

        return in_array((string) $target->role, $assignable, true);
    }

    private function looksLikeUserid(string $search): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_-]{3,20}$/', $search);
    }

    /**
     * @param  array{userid: string, role: string}  $validated
     */
    public function updateRole(array $validated): void
    {
        /** @var User $actor */
        $actor = auth()->user();
        $target = User::query()->where('userid', $validated['userid'])->firstOrFail();

        if (! $this->canAssignRole($actor->role, (string) $target->role, $validated['role'])) {
            throw new InvalidArgumentException('You are not allowed to assign this role.');
        }

        $target->update([
            'role' => $validated['role'],
        ]);
    }
}
