<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Services\StaffApiClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApproverAdminService
{
    public function __construct(private StaffApiClient $staffApi) {}

    /**
     * @return array{depts: Collection, datas: Collection, noti: array{count: int, error: int, err_list: array<int, string>}}
     */
    public function listApprovers(): array
    {
        $depts = DB::connection('staff')
            ->table('departments')
            ->where('department', '!=', 'Doctor')
            ->orderBy('department', 'asc')
            ->pluck('department', 'id');

        $datas = DB::connection('staff')
            ->table('departments')
            ->leftJoin('approvers', 'departments.id', '=', 'approvers.department_id')
            ->leftJoin('users', 'approvers.userid', '=', 'users.userid')
            ->leftJoin('emails', 'users.userid', '=', 'emails.userid')
            ->where('departments.department', '!=', 'Doctor')
            ->where('approvers.level', 1)
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
            ->orderBy('departments.department', 'asc')
            ->get();

        $errList = [];

        foreach ($datas as $data) {
            if (empty($data->userid) || $data->userid === '-') {
                $errList[] = $data->department;
                $data->name = 'ไม่พบข้อมูล';
                $data->email = 'ไม่พบข้อมูล';
                $data->position = 'ไม่พบข้อมูล';
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

        DB::connection('staff')
            ->table('approvers')
            ->where('department_id', $dept->id)
            ->where('level', 1)
            ->update([
                'userid' => $validated['userid'],
                'updated_at' => now(),
                'updated_userid' => auth()->user()->userid,
                'updated_username' => auth()->user()->name,
            ]);

        $this->upsertStaffEmail($validated['userid'], $validated['email']);
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
     * @return array{groupedUsers: array<string, array{label: string, users: list<User>}>, roles: array<string, string>, allRoleLabels: array<string, string>, search: ?string, canSetUser: bool, scoped: bool, apiNotice: ?array{status: string, message: ?string}}
     */
    public function listRoles(?string $search): array
    {
        /** @var User $actor */
        $actor = auth()->user();
        $roles = $this->roleLabelsForActor($actor->role);
        $allRoleLabels = $this->allRoleLabels();
        $assignable = $this->assignableRoleKeys($actor->role);
        $canSetUser = $assignable === null || in_array('user', $assignable, true);
        $scoped = $assignable !== null;
        $apiNotice = null;
        $search = filled($search) ? trim($search) : null;

        $users = $this->roleUsersQuery($search, $assignable)
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        if ($users->isEmpty() && filled($search) && $this->looksLikeUserid($search)) {
            $existing = User::query()->where('userid', $search)->first();

            if ($existing) {
                if ($this->canDisplayUserForRoleAssignment($actor->role, $existing)) {
                    $users = collect([$existing]);
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
                    $users = collect([$import['user']]);
                }
            }
        }

        $groupedUsers = $this->groupUsersByRoleType($users);

        return compact('groupedUsers', 'roles', 'allRoleLabels', 'search', 'canSetUser', 'scoped', 'apiNotice');
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
    private function roleUsersQuery(?string $search, ?array $assignable)
    {
        return User::query()
            ->when($assignable === null, function ($query): void {
                $query->where('role', '!=', 'user');
            })
            ->when($assignable !== null, function ($query) use ($assignable): void {
                $displayRoles = array_values(array_filter(
                    $assignable,
                    fn (string $role): bool => $role !== 'user'
                ));
                $query->whereIn('role', $displayRoles);
            })
            ->when(filled($search), function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('userid', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
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
