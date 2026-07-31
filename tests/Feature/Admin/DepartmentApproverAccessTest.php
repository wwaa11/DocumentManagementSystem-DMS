<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Admin\ApproverAdminService;
use Tests\TestCase;

class DepartmentApproverAccessTest extends TestCase
{
    private function makeUser(string $role): User
    {
        $user = new User([
            'userid' => '650001',
            'name' => 'Test User',
            'position' => 'Staff',
            'department' => 'IT',
            'division' => 'IT',
        ]);
        $user->role = $role;

        return $user;
    }

    public function test_it_role_can_manage_department_approvers(): void
    {
        $service = app(ApproverAdminService::class);

        $this->assertTrue($service->canManageApprovers($this->makeUser('admin')));
        $this->assertTrue($service->canManageApprovers($this->makeUser('dev')));
        $this->assertTrue($service->canManageApprovers($this->makeUser('it')));
        $this->assertFalse($service->canManageApprovers($this->makeUser('it-approve')));
        $this->assertFalse($service->canManageApprovers($this->makeUser('media')));
        $this->assertFalse($service->canManageApprovers($this->makeUser('user')));
    }

    public function test_it_role_menu_includes_department_approvers_section(): void
    {
        $menu = $this->makeUser('it')->menu;
        $titles = collect($menu['lists'])->pluck('title')->all();

        $this->assertContains('Approvers', $titles);
        $this->assertTrue(collect($menu['lists'])->contains(
            fn (array $item): bool => ($item['link'] ?? null) === 'approvers.list'
                && ($item['title'] ?? null) === 'Department Approvers'
        ));
    }

    public function test_admin_menu_keeps_department_approvers_in_admin_group(): void
    {
        $menu = $this->makeUser('admin')->menu;
        $adminMenus = collect($menu['groups'])->firstWhere('key', 'admin')['menus'];

        $this->assertTrue(collect($adminMenus)->contains(
            fn (array $item): bool => ($item['link'] ?? null) === 'approvers.list'
        ));
    }
}
