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

    public function test_all_documents_list_has_process_userid_filter(): void
    {
        $source = file_get_contents(resource_path('views/admin/it/list.blade.php'));

        $this->assertStringContainsString('ดำเนินการโดย', $source);
        $this->assertStringContainsString('name="process_userid"', $source);
        $this->assertStringContainsString('@foreach ($processUsers ?? [] as $processUser)', $source);
        $this->assertStringContainsString('รายการดำเนินงาน', $source);
        $this->assertStringContainsString('name="process_log"', $source);
    }

    public function test_admin_all_documents_filters_by_process_log_userid(): void
    {
        $method = new ReflectionMethod(DocumentITAdminService::class, 'adminAllDocuments');
        $body = file_get_contents($method->getFileName());
        $body = implode("\n", array_slice(
            explode("\n", $body),
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertStringContainsString("\$request->get('process_userid')", $body);
        $this->assertStringContainsString("\$request->get('process_log')", $body);
        $this->assertStringContainsString('$this->filterByProcessLogs($itQuery, $process_userid, $process_log)', $body);
        $this->assertStringContainsString('$this->filterByProcessLogs($itUserQuery, $process_userid, $process_log)', $body);
        $this->assertStringContainsString('$this->filterByProcessLogs($borrowQuery, $process_userid, $process_log)', $body);
        $this->assertStringContainsString("'process_userid', 'processUsers', 'process_log'", $body);

        $filterMethod = new ReflectionMethod(DocumentITAdminService::class, 'filterByProcessLogs');
        $filterBody = file_get_contents($filterMethod->getFileName());
        $filterBody = implode("\n", array_slice(
            explode("\n", $filterBody),
            $filterMethod->getStartLine() - 1,
            $filterMethod->getEndLine() - $filterMethod->getStartLine() + 1
        ));

        $this->assertStringContainsString("whereHas('logs'", $filterBody);
        $this->assertStringContainsString("->where('action', 'process')", $filterBody);
        $this->assertStringContainsString("->where('userid', \$processUserid)", $filterBody);
        $this->assertStringContainsString("->where('details', 'LIKE', \"%{\$processLog}%\")", $filterBody);
        $this->assertStringContainsString("withMax(['logs as last_process_at'", $filterBody);
        $this->assertStringContainsString('$this->sortAllDocuments($documents, $process_userid, $process_log)', $body);
        $this->assertStringContainsString('filled($process_userid) || filled($process_log) ? 100 : 10', $body);
        $this->assertStringContainsString("'end_date', 'typeCounts'", $body);
    }

    public function test_admin_all_documents_defaults_type_to_all(): void
    {
        $method = new ReflectionMethod(DocumentITAdminService::class, 'adminAllDocuments');
        $body = file_get_contents($method->getFileName());
        $body = implode("\n", array_slice(
            explode("\n", $body),
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertStringContainsString("\$request->get('type') ?: 'ALL'", $body);
        $this->assertStringContainsString('$this->mergeDocumentCollections($documents, $documentsITUser, $documentsBorrow)', $body);
    }

    public function test_merge_document_collections_keeps_overlapping_ids(): void
    {
        $service = $this->app->make(DocumentITAdminService::class);
        $method = new ReflectionMethod(DocumentITAdminService::class, 'mergeDocumentCollections');
        $method->setAccessible(true);

        $itDocuments = collect([5 => (object) ['id' => 5, 'tag' => 'IT']]);
        $ituDocuments = collect([5 => (object) ['id' => 5, 'tag' => 'USER']]);

        $merged = $method->invoke($service, $itDocuments, $ituDocuments);

        $this->assertCount(2, $merged);
        $this->assertSame(['IT', 'USER'], $merged->pluck('tag')->all());
    }

    public function test_sort_all_documents_by_process_log_puts_recent_itu_first(): void
    {
        $service = $this->app->make(DocumentITAdminService::class);
        $method = new ReflectionMethod(DocumentITAdminService::class, 'sortAllDocuments');
        $method->setAccessible(true);

        $itDocument = (object) [
            'tag' => 'IT',
            'created_at' => '2026-08-19 10:00:00',
            'last_process_at' => '2026-01-01 10:00:00',
        ];
        $ituDocument = (object) [
            'tag' => 'USER',
            'created_at' => '2025-01-01 10:00:00',
            'last_process_at' => '2026-08-18 10:00:00',
        ];

        $sorted = $method->invoke($service, collect([$itDocument, $ituDocument]), '440106');

        $this->assertSame(['USER', 'IT'], $sorted->pluck('tag')->all());
    }
}
