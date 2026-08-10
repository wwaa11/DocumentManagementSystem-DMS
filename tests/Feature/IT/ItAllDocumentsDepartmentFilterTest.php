<?php

namespace Tests\Feature\IT;

use App\Services\IT\DocumentITAdminService;
use ReflectionMethod;
use Tests\TestCase;

class ItAllDocumentsDepartmentFilterTest extends TestCase
{
    public function test_all_documents_list_has_creator_department_filter(): void
    {
        $source = file_get_contents(resource_path('views/admin/it/list.blade.php'));

        $this->assertStringContainsString('แผนกที่สร้าง', $source);
        $this->assertStringContainsString('name="department"', $source);
        $this->assertStringContainsString('@foreach ($departments ?? [] as $dept)', $source);
    }

    public function test_admin_all_documents_filters_by_creator_department(): void
    {
        $method = new ReflectionMethod(DocumentITAdminService::class, 'adminAllDocuments');
        $body = file_get_contents($method->getFileName());
        $body = implode("\n", array_slice(
            explode("\n", $body),
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertStringContainsString("\$request->get('department')", $body);
        $this->assertStringContainsString("whereHas('creator'", $body);
        $this->assertStringContainsString("whereHas('documentUser.creator'", $body);
        $this->assertStringContainsString("->where('department', \$department)", $body);
        $this->assertStringContainsString("'department', 'departments'", $body);
    }
}
