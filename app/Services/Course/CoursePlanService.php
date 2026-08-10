<?php

namespace App\Services\Course;

use App\Models\CourseApprover;
use App\Models\CoursePlan;
use App\Models\User;
use App\Services\Admin\ApproverAdminService;
use App\Services\StaffApiClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CoursePlanService
{
    public function __construct(
        private StaffApiClient $staffApi,
        private ApproverAdminService $approverAdminService,
    ) {}

    /**
     * Plans the user may view: every department they are allowed to create for.
     *
     * @return Collection<int, CoursePlan>
     */
    public function listPlans(User $user, ?int $year = null): Collection
    {
        $departments = $this->departmentsForUser($user);

        if ($departments === []) {
            return collect();
        }

        return CoursePlan::query()
            ->with([
                'creator',
                'approvers',
                'items.instructors',
                'items.responsibles',
                'items.targetPositions',
                'items.trainings',
            ])
            ->whereIn('department', $departments)
            ->when($year, fn ($query) => $query->where('year', $year))
            ->orderBy('department')
            ->orderByDesc('year')
            ->get();
    }

    public function canAccessCourseModule(User $user): bool
    {
        return $user->canCreateCourseForDepartment();
    }

    public function canViewPlan(User $user, CoursePlan $plan): bool
    {
        if ($user->canCreateCourseForDepartment($plan->department)) {
            return true;
        }

        if ($plan->relationLoaded('approvers')) {
            return $plan->approvers->contains(
                fn (CourseApprover $approver): bool => $approver->userid === $user->userid
            );
        }

        return $plan->approvers()->where('userid', $user->userid)->exists();
    }

    /**
     * Course items available for linking / substituting when creating training.
     *
     * @return Collection<int, array{id: int, label: string, number: string, name: string, department: string, year: int, instructors: list<array{userid: string, name: string, position: string|null}>}>
     */
    public function courseItemsForTraining(User $user, ?int $year = null): Collection
    {
        $departments = $this->departmentsForUser($user);

        if ($departments === []) {
            return collect();
        }

        $year ??= (int) now()->year;

        return CoursePlan::query()
            ->with(['items' => fn ($query) => $query->whereDoesntHave('trainings')->with('instructors')])
            ->whereIn('department', $departments)
            ->where('year', $year)
            ->orderBy('department')
            ->get()
            ->flatMap(function (CoursePlan $plan) {
                return $plan->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'label' => $plan->department.' · '.$item->number.'. '.$item->name,
                    'number' => (string) $item->number,
                    'name' => $item->name,
                    'department' => $plan->department,
                    'year' => (int) $plan->year,
                    'instructors' => $item->instructors->map(fn ($instructor): array => [
                        'userid' => $instructor->userid,
                        'name' => $instructor->name,
                        'position' => $instructor->position,
                    ])->values()->all(),
                ]);
            })
            ->values();
    }

    /**
     * @return list<string>
     */
    public function departmentsForUser(User $user): array
    {
        return $user->courseDepartments();
    }

    /**
     * Departments the user may create a new annual form for in the given year.
     *
     * @return list<string>
     */
    public function availableDepartmentsForCreate(User $user, int $year, ?int $ignorePlanId = null): array
    {
        $used = CoursePlan::query()
            ->where('year', $year)
            ->when($ignorePlanId, fn ($query) => $query->where('id', '!=', $ignorePlanId))
            ->pluck('department')
            ->all();

        return array_values(array_filter(
            $this->departmentsForUser($user),
            fn (string $department): bool => ! in_array($department, $used, true)
        ));
    }

    public function findExistingPlan(int $year, string $department): ?CoursePlan
    {
        return CoursePlan::query()
            ->where('year', $year)
            ->where('department', $department)
            ->first();
    }

    /**
     * @return list<string>
     */
    public function positionsForDepartment(string $department): array
    {
        $response = $this->staffApi->getDepartmentPositions($department);

        if (! $response->successful()) {
            return [];
        }

        $payload = $response->json();

        if (($payload['status'] ?? null) != 1) {
            return [];
        }

        return collect($payload['positions'] ?? [])
            ->pluck('position')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{level2: ?object, level3: ?object}
     */
    public function staffApproversForDepartment(string $department): array
    {
        $dept = DB::connection('staff')
            ->table('departments')
            ->where('department', $department)
            ->first();

        if (! $dept) {
            return ['level2' => null, 'level3' => null];
        }

        $approvers = DB::connection('staff')
            ->table('approvers')
            ->where('department_id', $dept->id)
            ->whereIn('level', [2, 3])
            ->get()
            ->keyBy('level');

        return [
            'level2' => $approvers->get(2),
            'level3' => $approvers->get(3),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createPlan(User $user, array $validated): CoursePlan
    {
        $this->assertCanManageDepartment($user, $validated['department']);

        if ($this->findExistingPlan((int) $validated['year'], $validated['department'])) {
            throw new InvalidArgumentException('แผนกนี้มีแบบฟอร์มหลักสูตรประจำปีนี้อยู่แล้ว');
        }

        return DB::transaction(function () use ($user, $validated): CoursePlan {
            $this->syncStaffApproverLevel($validated['department'], 2, $validated['approver_level2']);
            $this->syncStaffApproverLevel($validated['department'], 3, $validated['approver_level3']);

            $plan = CoursePlan::query()->create([
                'year' => (int) $validated['year'],
                'department' => $validated['department'],
                'created_by' => $user->userid,
                'status' => 'wait_approval',
            ]);

            $this->syncCourses($plan, $validated['courses']);
            $this->syncApprovers($plan, $validated);

            return $plan->load(['items.instructors', 'items.targetPositions', 'items.responsibles', 'approvers']);
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updatePlan(User $user, CoursePlan $plan, array $validated): CoursePlan
    {
        $this->assertCanManageDepartment($user, $plan->department);

        if (
            ((int) $validated['year'] !== (int) $plan->year || $validated['department'] !== $plan->department)
            && $this->findExistingPlan((int) $validated['year'], $validated['department'])
        ) {
            throw new InvalidArgumentException('แผนกนี้มีแบบฟอร์มหลักสูตรประจำปีนี้อยู่แล้ว');
        }

        return DB::transaction(function () use ($plan, $validated): CoursePlan {
            $this->syncStaffApproverLevel($validated['department'], 2, $validated['approver_level2']);
            $this->syncStaffApproverLevel($validated['department'], 3, $validated['approver_level3']);

            $plan->update([
                'year' => (int) $validated['year'],
                'department' => $validated['department'],
                'status' => 'wait_approval',
            ]);

            $this->syncCourses($plan, $validated['courses']);
            $this->syncApprovers($plan, $validated);

            return $plan->fresh()->load(['items.instructors', 'items.targetPositions', 'items.responsibles', 'approvers']);
        });
    }

    /**
     * Existing items are updated in place so linked training documents keep their reference.
     *
     * @param  list<array<string, mixed>>  $courses
     */
    private function syncCourses(CoursePlan $plan, array $courses): void
    {
        $existingItems = $plan->items()->get()->keyBy('id');
        $keptItemIds = [];

        foreach (array_values($courses) as $index => $course) {
            $attributes = [
                'number' => $course['number'],
                'name' => $course['name'],
                'origin' => $course['origin'],
                'objective' => $course['objective'],
                'training_type' => $course['training_type'],
                'schedule_months' => array_map('intval', $course['schedule_months']),
                'estimated_cost' => $course['estimated_cost'] ?? null,
                'sort_order' => $index,
            ];

            $item = isset($course['id']) ? $existingItems->get((int) $course['id']) : null;

            if ($item) {
                $item->update($attributes);
                $item->instructors()->delete();
                $item->targetPositions()->delete();
                $item->responsibles()->delete();
            } else {
                $item = $plan->items()->create($attributes);
            }

            $keptItemIds[] = $item->id;

            foreach ($course['instructors'] as $instructor) {
                $item->instructors()->create([
                    'userid' => $instructor['userid'],
                    'name' => $instructor['name'],
                    'position' => $instructor['position'],
                    'source_type' => $instructor['source_type'] ?? 'internal',
                ]);
            }

            foreach ($course['target_positions'] as $position) {
                $item->targetPositions()->create([
                    'position' => $position,
                ]);
            }

            foreach ($course['responsibles'] as $responsible) {
                $item->responsibles()->create([
                    'userid' => $responsible['userid'],
                    'name' => $responsible['name'],
                    'position' => $responsible['position'],
                ]);
            }
        }

        $plan->items()->whereNotIn('id', $keptItemIds)->delete();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncApprovers(CoursePlan $plan, array $validated): void
    {
        $plan->approvers()->delete();

        foreach ([1, 2, 3] as $level) {
            $approver = $validated['approver_level'.$level];
            $plan->approvers()->create([
                'level' => $level,
                'userid' => $approver['userid'],
                'name' => $approver['name'] ?? null,
                'position' => $approver['position'] ?? null,
                'email' => $approver['email'] ?? null,
                'status' => 'wait',
                'approved_at' => null,
            ]);
        }
    }

    /**
     * Annual plans currently waiting on this user's approval.
     *
     * @return Collection<int, CoursePlan>
     */
    public function pendingApprovalsFor(User $user): Collection
    {
        return CoursePlan::query()
            ->with(['creator', 'items', 'approvers'])
            ->where('status', 'wait_approval')
            ->whereHas('approvers', function ($query) use ($user): void {
                $query->where('userid', $user->userid)->where('status', 'wait');
            })
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (CoursePlan $plan): bool => $plan->pendingLevelsFor($user->userid)->isNotEmpty())
            ->values();
    }

    /**
     * Approve or reject on behalf of a user. When the same user holds several
     * consecutive levels, all of them are stamped in a single action.
     */
    public function decide(CoursePlan $plan, User $user, string $decision, ?string $reason = null): CoursePlan
    {
        $levels = $plan->pendingLevelsFor($user->userid);

        if ($levels->isEmpty()) {
            throw new InvalidArgumentException('คุณไม่มีสิทธิ์อนุมัติแบบฟอร์มนี้ในขณะนี้');
        }

        return DB::transaction(function () use ($plan, $levels, $decision, $reason): CoursePlan {
            if ($decision === 'reject') {
                $levels->first()->update([
                    'status' => 'reject',
                    'approved_at' => now(),
                    'reason' => $reason,
                ]);

                $plan->update(['status' => 'not_approval']);

                return $plan->fresh(['approvers']);
            }

            foreach ($levels as $approver) {
                $approver->update([
                    'status' => 'approve',
                    'approved_at' => now(),
                    'reason' => null,
                ]);
            }

            if ($plan->approvers()->where('status', '!=', 'approve')->doesntExist()) {
                $plan->update(['status' => 'complete']);
            }

            return $plan->fresh(['approvers']);
        });
    }

    private function assertCanManageDepartment(User $user, string $department): void
    {
        if (! $user->canCreateCourseForDepartment($department)) {
            throw new InvalidArgumentException('คุณไม่มีสิทธิ์สร้างหลักสูตรสำหรับแผนกนี้');
        }
    }

    /**
     * @param  array{userid: string, name?: string, position?: string, email?: string}  $approver
     */
    public function syncStaffApproverLevel(string $department, int $level, array $approver): void
    {
        $dept = DB::connection('staff')
            ->table('departments')
            ->where('department', $department)
            ->first();

        if (! $dept) {
            throw new InvalidArgumentException('ไม่พบแผนกใน Staff DB');
        }

        $existing = DB::connection('staff')
            ->table('approvers')
            ->where('department_id', $dept->id)
            ->where('level', $level)
            ->first();

        $payload = [
            'userid' => $approver['userid'],
            'name' => $approver['name'] ?? null,
            'email' => $approver['email'] ?? null,
            'updated_at' => now(),
            'updated_userid' => auth()->user()?->userid,
            'updated_username' => auth()->user()?->name,
        ];

        if ($existing) {
            if (
                $existing->userid === $approver['userid']
                && ($existing->name ?? null) === ($approver['name'] ?? null)
                && ($existing->email ?? null) === ($approver['email'] ?? null)
            ) {
                return;
            }

            DB::connection('staff')
                ->table('approvers')
                ->where('id', $existing->id)
                ->update($payload);

            return;
        }

        DB::connection('staff')->table('approvers')->insert([
            'department_id' => $dept->id,
            'userid' => $approver['userid'],
            'name' => $approver['name'] ?? null,
            'email' => $approver['email'] ?? null,
            'level' => $level,
            'created_at' => now(),
            'updated_at' => now(),
            'updated_userid' => auth()->user()?->userid,
            'updated_username' => auth()->user()?->name,
        ]);
    }

    /**
     * @return array{users: \Illuminate\Support\Collection<int, User>, departments: list<string>, search: ?string, apiNotice: ?array{status: string, message: ?string}}
     */
    public function listPermissionUsers(?string $search = null): array
    {
        $search = filled($search) ? trim($search) : null;
        $apiNotice = null;

        $users = User::query()
            ->when(filled($search), function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('userid', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            }, function ($query): void {
                $query->where('can_create_course', true);
            })
            ->orderByDesc('can_create_course')
            ->orderBy('name')
            ->get();

        if ($users->isEmpty() && filled($search) && preg_match('/^[A-Za-z0-9_-]{3,20}$/', $search)) {
            $existing = User::query()->where('userid', $search)->first();

            if ($existing) {
                $users = collect([$existing]);
            } else {
                $import = $this->approverAdminService->importUserFromStaff($search);
                $apiNotice = [
                    'status' => $import['status'],
                    'message' => $import['message'],
                ];

                if ($import['status'] === 'imported' && $import['user'] instanceof User) {
                    $users = collect([$import['user']]);
                }
            }
        }

        $departments = DB::connection('staff')
            ->table('departments')
            ->where('department', '!=', 'Doctor')
            ->orderBy('department')
            ->pluck('department')
            ->all();

        return [
            'users' => $users,
            'departments' => $departments,
            'search' => $search,
            'apiNotice' => $apiNotice,
        ];
    }

    /**
     * @param  array{userid: string, can_create_course: bool, course_departments?: list<string>|null}  $validated
     */
    public function updateUserCoursePermission(array $validated): User
    {
        $user = User::query()->where('userid', $validated['userid'])->firstOrFail();

        $canCreate = (bool) $validated['can_create_course'];
        $departments = $canCreate
            ? array_values(array_unique(array_filter($validated['course_departments'] ?? [])))
            : [];

        if ($canCreate && $departments === []) {
            throw new InvalidArgumentException('กรุณาเลือกแผนกที่สามารถสร้างหลักสูตรได้อย่างน้อย 1 แผนก');
        }

        $user->update([
            'can_create_course' => $canCreate,
            'course_departments' => $departments === [] ? null : $departments,
        ]);

        return $user->fresh();
    }
}
