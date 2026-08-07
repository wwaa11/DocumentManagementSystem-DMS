<?php

namespace Tests\Feature\Course;

use App\Http\Requests\Course\StoreCoursePlanRequest;
use App\Models\CourseApprover;
use App\Models\CoursePlan;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CoursePlanTest extends TestCase
{
    /**
     * @param  list<array{level: int, userid: string, status?: string}>  $approvers
     */
    private function makePlanWithApprovers(array $approvers): CoursePlan
    {
        $plan = new CoursePlan([
            'year' => 2026,
            'department' => 'แผนก A',
            'created_by' => '650000',
            'status' => 'wait_approval',
        ]);

        $plan->setRelation('approvers', collect($approvers)->map(fn (array $row): CourseApprover => new CourseApprover([
            'level' => $row['level'],
            'userid' => $row['userid'],
            'status' => $row['status'] ?? 'wait',
        ])));

        return $plan;
    }

    public function test_only_the_current_level_approver_is_pending(): void
    {
        $plan = $this->makePlanWithApprovers([
            ['level' => 1, 'userid' => 'A'],
            ['level' => 2, 'userid' => 'B'],
            ['level' => 3, 'userid' => 'C'],
        ]);

        $this->assertSame('A', $plan->currentApprover()->userid);
        $this->assertCount(1, $plan->pendingLevelsFor('A'));
        $this->assertTrue($plan->pendingLevelsFor('B')->isEmpty());
        $this->assertTrue($plan->pendingLevelsFor('C')->isEmpty());
    }

    public function test_same_approver_on_consecutive_levels_is_stamped_in_one_go(): void
    {
        $plan = $this->makePlanWithApprovers([
            ['level' => 1, 'userid' => 'A'],
            ['level' => 2, 'userid' => 'A'],
            ['level' => 3, 'userid' => 'A'],
        ]);

        $this->assertSame([1, 2, 3], $plan->pendingLevelsFor('A')->pluck('level')->all());
    }

    public function test_stamping_stops_at_a_different_approver(): void
    {
        $plan = $this->makePlanWithApprovers([
            ['level' => 1, 'userid' => 'A'],
            ['level' => 2, 'userid' => 'A'],
            ['level' => 3, 'userid' => 'B'],
        ]);

        $this->assertSame([1, 2], $plan->pendingLevelsFor('A')->pluck('level')->all());
    }

    public function test_next_level_becomes_pending_after_previous_approval(): void
    {
        $plan = $this->makePlanWithApprovers([
            ['level' => 1, 'userid' => 'A', 'status' => 'approve'],
            ['level' => 2, 'userid' => 'B'],
            ['level' => 3, 'userid' => 'B'],
        ]);

        $this->assertSame('B', $plan->currentApprover()->userid);
        $this->assertSame([2, 3], $plan->pendingLevelsFor('B')->pluck('level')->all());
    }

    public function test_rejected_level_blocks_further_approval(): void
    {
        $plan = $this->makePlanWithApprovers([
            ['level' => 1, 'userid' => 'A', 'status' => 'reject'],
            ['level' => 2, 'userid' => 'B'],
        ]);

        $this->assertNull($plan->currentApprover());
        $this->assertTrue($plan->pendingLevelsFor('B')->isEmpty());
    }

    public function test_fully_approved_plan_has_no_current_approver(): void
    {
        $plan = $this->makePlanWithApprovers([
            ['level' => 1, 'userid' => 'A', 'status' => 'approve'],
            ['level' => 2, 'userid' => 'B', 'status' => 'approve'],
            ['level' => 3, 'userid' => 'C', 'status' => 'approve'],
        ]);

        $this->assertNull($plan->currentApprover());
    }

    public function test_store_course_plan_validation_requires_core_fields(): void
    {
        $validator = Validator::make([], (new StoreCoursePlanRequest)->rules(), (new StoreCoursePlanRequest)->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('year', $validator->errors()->toArray());
        $this->assertArrayHasKey('department', $validator->errors()->toArray());
        $this->assertArrayHasKey('courses', $validator->errors()->toArray());
        $this->assertArrayHasKey('approver_level2', $validator->errors()->toArray());
        $this->assertArrayHasKey('approver_level3', $validator->errors()->toArray());
    }

    public function test_store_course_plan_validation_passes_with_multiple_courses(): void
    {
        $validator = Validator::make([
            'year' => 2026,
            'department' => 'แผนกเทคโนโลยีสารสนเทศ',
            'courses' => [
                [
                    'number' => '1',
                    'name' => 'หลักสูตร A',
                    'origin' => 'แผนพัฒนา',
                    'objective' => 'เพิ่มทักษะ',
                    'training_type' => 'internal',
                    'schedule_months' => [1, 2],
                    'estimated_cost' => null,
                    'instructors' => [
                        [
                            'userid' => '650001',
                            'name' => 'วิทยากร',
                            'position' => 'Trainer',
                            'source_type' => 'internal',
                        ],
                    ],
                    'target_positions' => ['พยาบาล'],
                    'responsibles' => [
                        [
                            'userid' => '650002',
                            'name' => 'ผู้รับผิดชอบ',
                            'position' => 'หัวหน้างาน',
                        ],
                    ],
                ],
                [
                    'number' => '2',
                    'name' => 'หลักสูตร B',
                    'origin' => 'ความต้องการแผนก',
                    'objective' => 'พัฒนาทีม',
                    'training_type' => 'elearning',
                    'schedule_months' => [6],
                    'estimated_cost' => 1500,
                    'instructors' => [
                        [
                            'userid' => 'EXT-1',
                            'name' => 'วิทยากรภายนอก',
                            'position' => 'Consultant',
                            'source_type' => 'external',
                        ],
                    ],
                    'target_positions' => ['เจ้าหน้าที่'],
                    'responsibles' => [
                        [
                            'userid' => '650003',
                            'name' => 'ผู้รับผิดชอบ 2',
                            'position' => 'Supervisor',
                        ],
                    ],
                ],
            ],
            'approver_level1' => [
                'userid' => '650010',
                'name' => 'Approver 1',
                'position' => 'Head',
                'email' => 'a1@example.com',
            ],
            'approver_level2' => [
                'userid' => '650011',
                'name' => 'Approver 2',
                'position' => 'Director',
                'email' => 'a2@example.com',
            ],
            'approver_level3' => [
                'userid' => '650012',
                'name' => 'Approver 3',
                'position' => 'CEO',
                'email' => 'a3@example.com',
            ],
        ], (new StoreCoursePlanRequest)->rules());

        $this->assertTrue($validator->passes(), $validator->errors()->toJson());
    }

    public function test_user_can_create_course_for_assigned_departments_only(): void
    {
        $user = new User([
            'userid' => '650099',
            'name' => 'Course Creator',
            'position' => 'Staff',
            'department' => 'แผนก A',
            'division' => 'A',
            'email' => 'creator@example.com',
            'role' => 'user',
            'can_create_course' => true,
            'course_departments' => ['แผนก A', 'แผนก B'],
        ]);

        $this->assertTrue($user->canCreateCourseForDepartment('แผนก A'));
        $this->assertTrue($user->canCreateCourseForDepartment('แผนก B'));
        $this->assertFalse($user->canCreateCourseForDepartment('แผนก C'));
        $this->assertTrue($user->canCreateCourseForDepartment());
    }

    public function test_user_without_flag_cannot_create_course(): void
    {
        $user = new User([
            'userid' => '650098',
            'name' => 'No Permission',
            'position' => 'Staff',
            'department' => 'แผนก A',
            'division' => 'A',
            'can_create_course' => false,
            'course_departments' => ['แผนก A'],
        ]);

        $this->assertFalse($user->canCreateCourseForDepartment('แผนก A'));
        $this->assertFalse($user->canCreateCourseForDepartment());
    }

    public function test_course_module_access_requires_create_permission(): void
    {
        $service = app(\App\Services\Course\CoursePlanService::class);

        $allowed = new User([
            'can_create_course' => true,
            'course_departments' => ['แผนก A', 'แผนก B'],
        ]);
        $denied = new User([
            'can_create_course' => false,
            'course_departments' => ['แผนก A'],
        ]);
        $noDepartments = new User([
            'can_create_course' => true,
            'course_departments' => [],
        ]);

        $this->assertTrue($service->canAccessCourseModule($allowed));
        $this->assertFalse($service->canAccessCourseModule($denied));
        $this->assertFalse($service->canAccessCourseModule($noDepartments));
    }

    public function test_course_plan_view_allowed_for_department_permission_or_approver(): void
    {
        $service = app(\App\Services\Course\CoursePlanService::class);

        $plan = new CoursePlan([
            'year' => 2026,
            'department' => 'แผนก A',
            'created_by' => '650000',
            'status' => 'wait_approval',
        ]);
        $plan->setRelation('approvers', collect([
            new CourseApprover(['level' => 1, 'userid' => 'APPROVER1', 'status' => 'wait']),
        ]));

        $departmentUser = new User([
            'userid' => '650099',
            'can_create_course' => true,
            'course_departments' => ['แผนก A', 'แผนก B'],
        ]);
        $approver = new User([
            'userid' => 'APPROVER1',
            'can_create_course' => false,
            'course_departments' => [],
        ]);
        $stranger = new User([
            'userid' => '650000',
            'can_create_course' => true,
            'course_departments' => ['แผนก C'],
        ]);

        $this->assertTrue($service->canViewPlan($departmentUser, $plan));
        $this->assertTrue($service->canViewPlan($approver, $plan));
        $this->assertFalse($service->canViewPlan($stranger, $plan));
    }

    public function test_training_type_and_month_labels_exist(): void
    {
        $this->assertSame('ภายใน', \App\Models\CoursePlan::trainingTypeLabels()['internal']);
        $this->assertSame('E-learning', \App\Models\CoursePlan::trainingTypeLabels()['elearning']);
        $this->assertSame('มกราคม', \App\Models\CoursePlan::monthLabels()[1]);
    }

    public function test_enabled_course_permission_without_departments_is_invalid(): void
    {
        $user = new User([
            'userid' => '650097',
            'name' => 'Needs Departments',
            'position' => 'Staff',
            'department' => 'แผนก A',
            'division' => 'A',
            'can_create_course' => true,
            'course_departments' => [],
        ]);

        $this->assertFalse($user->canCreateCourseForDepartment());
        $this->assertFalse($user->canCreateCourseForDepartment('แผนก A'));
    }

    public function test_admin_menu_includes_course_permissions_page(): void
    {
        $user = new User([
            'userid' => '650090',
            'name' => 'Admin',
            'position' => 'Admin',
            'department' => 'IT',
            'division' => 'IT',
            'role' => 'admin',
        ]);

        $links = collect($user->menu['groups'] ?? [])
            ->flatMap(fn (array $group) => $group['menus'] ?? [])
            ->pluck('link')
            ->filter()
            ->values()
            ->all();

        $this->assertContains('admin.course-permissions', $links);
    }

    public function test_non_admin_menu_excludes_course_permissions_page(): void
    {
        $user = new User([
            'userid' => '650091',
            'name' => 'Media Head',
            'position' => 'Head',
            'department' => 'Media',
            'division' => 'Media',
            'role' => 'media-head',
        ]);

        $links = collect($user->menu['lists'] ?? [])
            ->pluck('link')
            ->filter()
            ->values()
            ->all();

        $this->assertNotContains('admin.course-permissions', $links);
    }
}
