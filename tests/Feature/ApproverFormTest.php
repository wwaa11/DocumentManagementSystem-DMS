<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ApproverFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = new User([
            'userid' => '650001',
            'name' => 'ผู้ทดสอบระบบ',
            'position' => 'Staff',
            'department' => 'IT',
            'division' => 'ฝ่ายเทคโนโลยีสารสนเทศ',
            'email' => 'requester@example.com',
        ]);

        $this->actingAs($user);

        session([
            'user_approver' => (object) [
                'status' => true,
                'approver' => (object) [
                    'userid' => '640001',
                    'name' => 'ผู้อนุมัติทดสอบ',
                    'position' => 'Manager',
                    'email' => 'approver@example.com',
                ],
            ],
        ]);
    }

    public function test_approver_form_shows_change_button_by_default(): void
    {
        $html = view('components.document.approver-form')->render();

        $this->assertStringContainsString('เปลี่ยนผู้อนุมัติ', $html);
        $this->assertStringContainsString('change-approver-btn', $html);
        $this->assertStringNotContainsString('ติดต่อแผนก IT เพื่อเปลี่ยนผู้อนุมัติ', $html);
    }

    public function test_approver_form_shows_contact_it_message_when_change_disabled(): void
    {
        $html = view('components.document.approver-form', [
            'canChangeApprover' => false,
        ])->render();

        $this->assertStringContainsString('ติดต่อแผนก IT เพื่อเปลี่ยนผู้อนุมัติ', $html);
        $this->assertStringNotContainsString('change-approver-btn', $html);
        $this->assertStringNotContainsString('id="approver-selection"', $html);
    }

    public function test_it_create_view_disables_approver_change(): void
    {
        $source = file_get_contents(resource_path('views/document/it/create.blade.php'));

        $this->assertStringContainsString(':can-change-approver="false"', $source);
    }
}
