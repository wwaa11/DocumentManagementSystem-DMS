<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class NewJobsViewDocumentTest extends TestCase
{
    public function test_new_jobs_lists_include_view_document_button(): void
    {
        $lists = [
            resource_path('views/admin/it/list.blade.php'),
            resource_path('views/admin/user/list.blade.php'),
            resource_path('views/admin/media/list.blade.php'),
            resource_path('views/admin/purchase/list.blade.php'),
        ];

        foreach ($lists as $list) {
            $source = file_get_contents($list);

            $this->assertStringContainsString('ดูเอกสาร', $source, $list);
            $this->assertStringContainsString('รับงาน', $source, $list);
            $this->assertMatchesRegularExpression('/action\s*==\s*[\'"]new[\'"]/', $source, $list);
        }
    }

    public function test_new_job_view_shows_accept_button_when_unassigned(): void
    {
        $document = (object) [
            'id' => 10,
            'assigned_user_id' => null,
            'document_tag' => ['document_tag' => 'IT'],
        ];

        $actions = [
            'admin.it.actions.new' => [],
            'admin.user.actions.new' => ['type' => 'USER'],
            'admin.media.actions.new' => [],
            'admin.purchase.actions.new' => [],
        ];

        foreach ($actions as $view => $extra) {
            $html = view($view, array_merge(['document' => $document], $extra))->render();

            $this->assertStringContainsString('รับงาน', $html, $view);
            $this->assertStringContainsString('acceptDocument', $html, $view);
        }
    }

    public function test_new_job_view_hides_accept_button_when_already_assigned(): void
    {
        $document = (object) [
            'id' => 10,
            'assigned_user_id' => '650001',
            'document_tag' => ['document_tag' => 'IT'],
        ];

        $actions = [
            'admin.it.actions.new' => [],
            'admin.user.actions.new' => ['type' => 'USER'],
            'admin.media.actions.new' => [],
            'admin.purchase.actions.new' => [],
        ];

        foreach ($actions as $view => $extra) {
            $html = view($view, array_merge(['document' => $document], $extra))->render();

            $this->assertStringNotContainsString('รับงาน', $html, $view);
        }
    }
}
