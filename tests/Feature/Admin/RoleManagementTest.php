<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Admin\ApproverAdminService;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    public function test_media_head_can_only_assign_media_family_roles(): void
    {
        $service = app(ApproverAdminService::class);

        $this->assertSame(
            ['user', 'media', 'media-head'],
            $service->assignableRoleKeys('media-head')
        );

        $this->assertTrue($service->canAssignRole('media-head', 'user', 'media'));
        $this->assertTrue($service->canAssignRole('media-head', 'media', 'media-head'));
        $this->assertFalse($service->canAssignRole('media-head', 'user', 'purchase'));
        $this->assertFalse($service->canAssignRole('media-head', 'it', 'media'));
    }

    public function test_purchase_approve_and_head_share_purchase_family(): void
    {
        $service = app(ApproverAdminService::class);

        $this->assertSame(
            ['user', 'purchase', 'purchase-approve', 'purchase-head'],
            $service->assignableRoleKeys('purchase-approve')
        );
        $this->assertSame(
            ['user', 'purchase', 'purchase-approve', 'purchase-head'],
            $service->assignableRoleKeys('purchase-head')
        );
    }

    public function test_worker_roles_cannot_manage_roles(): void
    {
        $service = app(ApproverAdminService::class);

        $this->assertSame([], $service->assignableRoleKeys('media'));
        $mediaUser = new User;
        $mediaUser->role = 'media';
        $mediaHead = new User;
        $mediaHead->role = 'media-head';
        $admin = new User;
        $admin->role = 'admin';

        $this->assertFalse($service->canManageRoles($mediaUser));
        $this->assertTrue($service->canManageRoles($mediaHead));
        $this->assertTrue($service->canManageRoles($admin));
    }

    public function test_admin_has_unrestricted_role_assignment(): void
    {
        $service = app(ApproverAdminService::class);

        $this->assertNull($service->assignableRoleKeys('admin'));
        $this->assertTrue($service->canAssignRole('admin', 'user', 'media-head'));
        $this->assertTrue($service->canAssignRole('admin', 'it', 'purchase'));
    }

    public function test_it_hardware_approve_is_in_it_family_and_can_manage_roles(): void
    {
        $service = app(ApproverAdminService::class);

        $this->assertSame(
            ['user', 'it', 'it-hardware', 'it-approve', 'it-hardware-approve'],
            $service->assignableRoleKeys('it-hardware-approve')
        );
        $this->assertArrayHasKey('it-hardware-approve', $service->allRoleLabels());
        $this->assertSame('IT Hardware + Approve', $service->allRoleLabels()['it-hardware-approve']);
        $this->assertContains('it-hardware-approve', $service->roleFamilies()['it']);

        $user = new User;
        $user->role = 'it-hardware-approve';
        $this->assertTrue($service->canManageRoles($user));
    }

    public function test_role_labels_for_media_head_are_scoped(): void
    {
        $service = app(ApproverAdminService::class);
        $labels = $service->roleLabelsForActor('media-head');

        $this->assertSame(['media' => 'Media', 'media-head' => 'Media Head'], $labels);
        $this->assertArrayNotHasKey('admin', $labels);
        $this->assertArrayNotHasKey('purchase', $labels);
    }
}
