<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Admin\ApproverAdminService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class ApproverListTest extends TestCase
{
    private function makeAdmin(): User
    {
        $user = new User([
            'userid' => '999999',
            'name' => 'Admin Viewer',
            'position' => 'Admin',
            'department' => 'IT',
            'division' => 'IT',
            'email' => 'admin@example.com',
        ]);
        $user->role = 'admin';

        return $user;
    }

    public function test_list_includes_every_department_even_without_level_one_approver(): void
    {
        $departments = DB::connection('staff')
            ->table('departments')
            ->where('department', '!=', 'Doctor')
            ->orderBy('department')
            ->pluck('department');

        $this->assertNotEmpty($departments, 'Staff DB must have departments for this test.');

        $result = app(ApproverAdminService::class)->listApprovers();

        $this->assertSame($departments->count(), $result['datas']->count());
        $this->assertSame($departments->count(), $result['noti']['count']);
        $this->assertEqualsCanonicalizing(
            $departments->all(),
            $result['datas']->pluck('department')->all()
        );
        $this->assertEqualsCanonicalizing(
            $departments->all(),
            $result['depts']->values()->all()
        );
    }

    public function test_list_marks_departments_without_approver_as_missing(): void
    {
        $withoutLevelOne = DB::connection('staff')
            ->table('departments')
            ->where('department', '!=', 'Doctor')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('approvers')
                    ->whereColumn('approvers.department_id', 'departments.id')
                    ->where('approvers.level', 1)
                    ->whereNotNull('approvers.userid')
                    ->where('approvers.userid', '!=', '-');
            })
            ->pluck('department');

        $result = app(ApproverAdminService::class)->listApprovers();
        $listed = $result['datas']->keyBy('department');

        $this->assertSame($withoutLevelOne->count(), $result['noti']['error']);
        $this->assertSame(
            $result['datas']->count() - $withoutLevelOne->count(),
            $result['noti']['assigned']
        );

        foreach ($withoutLevelOne as $department) {
            $this->assertTrue($listed->has($department), "Department [{$department}] should appear in the list.");
            $this->assertContains($department, $result['noti']['err_list']);
            $this->assertFalse((bool) $listed[$department]->has_approver);
        }
    }

    public function test_approvers_view_shows_departments_without_approver(): void
    {
        $this->withoutVite();
        $this->actingAs($this->makeAdmin());
        session(['user_approver' => (object) ['status' => false]]);
        view()->share('errors', new ViewErrorBag);

        $this->view('admin.approvers', [
            'depts' => collect([1 => 'IT', 2 => 'New Department']),
            'datas' => collect([
                (object) [
                    'id' => 2,
                    'department' => 'New Department',
                    'has_approver' => false,
                    'userid' => null,
                    'name' => null,
                    'email' => null,
                    'position' => null,
                    'last_update' => null,
                    'last_userid' => null,
                    'last_username' => null,
                ],
                (object) [
                    'id' => 1,
                    'department' => 'IT',
                    'has_approver' => true,
                    'userid' => '123456',
                    'name' => 'Jane Doe',
                    'email' => 'jane@example.com',
                    'position' => 'Manager',
                    'last_update' => '2026-01-01 10:00:00',
                    'last_userid' => '999999',
                    'last_username' => 'Admin Viewer',
                ],
            ]),
            'noti' => [
                'count' => 2,
                'error' => 1,
                'assigned' => 1,
                'err_list' => ['New Department'],
            ],
        ])
            ->assertSee('New Department')
            ->assertSee('data-department="New Department"', false)
            ->assertSee('data-has-approver="0"', false)
            ->assertSee('data-has-approver="1"', false)
            ->assertSee('ยังไม่มีผู้อนุมัติ')
            ->assertSee('คลิกเพื่อกำหนดผู้อนุมัติ');
    }
}
