<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\WebController;
use App\Http\Requests\Admin\UpdateDocumentViewPermissionRequest;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DocumentViewPermissionTest extends TestCase
{
    private function makeUser(array $attributes = []): User
    {
        return new User(array_merge([
            'userid' => '650099',
            'name' => 'Department Viewer',
            'position' => 'Staff',
            'department' => 'แผนก A',
            'division' => 'A',
            'email' => 'viewer@example.com',
            'role' => 'user',
        ], $attributes));
    }

    public function test_user_can_view_documents_for_assigned_departments_only(): void
    {
        $user = $this->makeUser([
            'can_view_department_documents' => true,
            'view_departments' => ['แผนก A', 'แผนก B'],
        ]);

        $this->assertTrue($user->canViewDepartmentDocuments('แผนก A'));
        $this->assertTrue($user->canViewDepartmentDocuments('แผนก B'));
        $this->assertFalse($user->canViewDepartmentDocuments('แผนก C'));
        $this->assertTrue($user->canViewDepartmentDocuments());
        $this->assertSame(['แผนก A', 'แผนก B'], $user->viewDepartments());
    }

    public function test_user_without_flag_cannot_view_department_documents(): void
    {
        $user = $this->makeUser([
            'can_view_department_documents' => false,
            'view_departments' => ['แผนก A'],
        ]);

        $this->assertFalse($user->canViewDepartmentDocuments('แผนก A'));
        $this->assertFalse($user->canViewDepartmentDocuments());
    }

    public function test_enabled_view_permission_without_departments_is_invalid(): void
    {
        $user = $this->makeUser([
            'can_view_department_documents' => true,
            'view_departments' => [],
        ]);

        $this->assertFalse($user->canViewDepartmentDocuments());
        $this->assertFalse($user->canViewDepartmentDocuments('แผนก A'));
        $this->assertSame([], $user->getDepartmentDocuments());
    }

    public function test_get_department_documents_returns_empty_without_permission(): void
    {
        $user = $this->makeUser([
            'can_view_department_documents' => false,
            'view_departments' => ['แผนก A'],
        ]);

        $this->assertSame([], $user->getDepartmentDocuments());
    }

    public function test_admin_menu_includes_document_view_permissions_page(): void
    {
        $user = $this->makeUser([
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

        $this->assertContains('admin.document-view-permissions', $links);
    }

    public function test_non_admin_menu_excludes_document_view_permissions_page(): void
    {
        $user = $this->makeUser([
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

        $this->assertNotContains('admin.document-view-permissions', $links);
    }

    public function test_admin_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('admin.document-view-permissions'));
        $this->assertTrue(Route::has('admin.document-view-permissions.update'));
    }

    public function test_update_request_validates_expected_fields(): void
    {
        $rules = (new UpdateDocumentViewPermissionRequest)->rules();

        $this->assertSame(['required', 'string', 'exists:users,userid'], $rules['userid']);
        $this->assertSame(['required', 'boolean'], $rules['can_view_department_documents']);
        $this->assertSame(['nullable', 'array'], $rules['view_departments']);
        $this->assertSame(['string'], $rules['view_departments.*']);
    }

    public function test_my_document_index_includes_department_flag_tab(): void
    {
        $source = file_get_contents(resource_path('views/documnet_index.blade.php'));

        $this->assertStringContainsString("\$flags['dept'] = 'เอกสารแผนก'", $source);
        $this->assertStringContainsString('$canViewDepartmentDocuments', $source);
        $this->assertStringContainsString("request('flag') === 'dept'", $source);
    }

    public function test_my_document_merges_department_documents_with_dept_flag(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/WebController.php'));

        $this->assertStringContainsString('getDepartmentDocuments()', $source);
        $this->assertStringContainsString("mapIndexDocument(\$item, 'dept')", $source);
        $this->assertStringContainsString("\$flag === 'dept'", $source);
        $this->assertStringContainsString("'canViewDepartmentDocuments' => \$canViewDepartmentDocuments", $source);
    }

    public function test_index_row_shows_department_document_badge(): void
    {
        $source = file_get_contents(resource_path('views/document/partials/index-row.blade.php'));

        $this->assertStringContainsString("\$document['flag'] == 'dept'", $source);
        $this->assertStringContainsString('เอกสารที่จากแผนก', $source);
        $this->assertStringContainsString("\$document['requester_name']", $source);
        $this->assertStringContainsString("\$document['has_chat_messages']", $source);
        $this->assertStringContainsString('fa-comments', $source);
    }

    public function test_my_document_maps_chat_message_flag(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/WebController.php'));

        $this->assertStringContainsString("'has_chat_messages' => method_exists(\$item, 'hasChatMessages') && \$item->hasChatMessages()", $source);
    }

    public function test_my_document_prioritizes_unfinished_documents_with_chat(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/WebController.php'));

        $this->assertStringContainsString('sortIndexDocumentsByPriority', $source);
        $this->assertStringContainsString('isIndexDocumentChatPriority', $source);
        $this->assertStringContainsString('isIndexDocumentFinished', $source);

        $controller = $this->app->make(WebController::class);
        $method = new \ReflectionMethod(WebController::class, 'sortIndexDocumentsByPriority');
        $method->setAccessible(true);

        $sorted = $method->invoke($controller, collect([
            [
                'has_chat_messages' => true,
                'status' => 'complete',
                'created_at' => now(),
            ],
            [
                'has_chat_messages' => true,
                'status' => 'process',
                'created_at' => now()->subDays(2),
            ],
            [
                'has_chat_messages' => false,
                'status' => 'process',
                'created_at' => now()->subDay(),
            ],
            [
                'has_chat_messages' => true,
                'status' => 'pending',
                'created_at' => now()->subDays(1),
            ],
        ]));

        $this->assertSame('pending', $sorted[0]['status']);
        $this->assertSame('process', $sorted[1]['status']);
        $this->assertTrue($sorted[0]['has_chat_messages']);
        $this->assertTrue($sorted[1]['has_chat_messages']);
        $this->assertSame('complete', $sorted[2]['status']);
        $this->assertFalse($sorted[3]['has_chat_messages']);
    }

    public function test_admin_permission_page_has_department_picker(): void
    {
        $source = file_get_contents(resource_path('views/admin/document-view-permissions.blade.php'));

        $this->assertStringContainsString('Department Document Access', $source);
        $this->assertStringContainsString('name="view_departments[]"', $source);
        $this->assertStringContainsString('name="can_view_department_documents"', $source);
        $this->assertStringContainsString('route(\'admin.document-view-permissions.update\')', $source);
    }
}
