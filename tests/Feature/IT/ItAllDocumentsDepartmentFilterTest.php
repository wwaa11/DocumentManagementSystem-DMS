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
        $method = new ReflectionMethod(DocumentITAdminService::class, 'buildFilteredAllDocuments');
        $body = file_get_contents($method->getFileName());
        $body = implode("\n", array_slice(
            explode("\n", $body),
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertStringContainsString("whereHas('creator'", $body);
        $this->assertStringContainsString("whereHas('documentUser.creator'", $body);
        $this->assertStringContainsString("->where('department', \$department)", $body);

        $adminMethod = new ReflectionMethod(DocumentITAdminService::class, 'adminAllDocuments');
        $adminBody = file_get_contents($adminMethod->getFileName());
        $adminBody = implode("\n", array_slice(
            explode("\n", $adminBody),
            $adminMethod->getStartLine() - 1,
            $adminMethod->getEndLine() - $adminMethod->getStartLine() + 1
        ));

        $this->assertStringContainsString('$this->buildFilteredAllDocuments($filters)', $adminBody);
        $this->assertStringContainsString("'department' => \$filters['department']", $adminBody);
        $this->assertStringContainsString("'departments' => \$departments", $adminBody);
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
        $method = new ReflectionMethod(DocumentITAdminService::class, 'buildFilteredAllDocuments');
        $body = file_get_contents($method->getFileName());
        $body = implode("\n", array_slice(
            explode("\n", $body),
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertStringContainsString('$this->filterByProcessLogs($itQuery, $process_userid, $process_log)', $body);
        $this->assertStringContainsString('$this->filterByProcessLogs($itUserQuery, $process_userid, $process_log)', $body);
        $this->assertStringContainsString('$this->filterByProcessLogs($borrowQuery, $process_userid, $process_log)', $body);
        $this->assertStringContainsString('$this->sortAllDocuments($documents, $process_userid, $process_log)', $body);

        $adminMethod = new ReflectionMethod(DocumentITAdminService::class, 'adminAllDocuments');
        $adminBody = file_get_contents($adminMethod->getFileName());
        $adminBody = implode("\n", array_slice(
            explode("\n", $adminBody),
            $adminMethod->getStartLine() - 1,
            $adminMethod->getEndLine() - $adminMethod->getStartLine() + 1
        ));

        $this->assertStringContainsString('filled($filters[\'process_userid\']) || filled($filters[\'process_log\']) ? 100 : 10', $adminBody);
        $this->assertStringContainsString("'process_userid' => \$filters['process_userid']", $adminBody);
        $this->assertStringContainsString("'processUsers' => \$processUsers", $adminBody);
        $this->assertStringContainsString("'process_log' => \$filters['process_log']", $adminBody);

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
        $this->assertStringContainsString("'end_date' => \$filters['end_date']", $adminBody);
        $this->assertStringContainsString("'typeCounts' => \$typeCounts", $adminBody);
    }

    public function test_admin_all_documents_defaults_type_to_all(): void
    {
        $method = new ReflectionMethod(DocumentITAdminService::class, 'resolveAllDocumentsFilters');
        $body = file_get_contents($method->getFileName());
        $body = implode("\n", array_slice(
            explode("\n", $body),
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertStringContainsString("\$request->get('type') ?: 'ALL'", $body);

        $buildMethod = new ReflectionMethod(DocumentITAdminService::class, 'buildFilteredAllDocuments');
        $buildBody = file_get_contents($buildMethod->getFileName());
        $buildBody = implode("\n", array_slice(
            explode("\n", $buildBody),
            $buildMethod->getStartLine() - 1,
            $buildMethod->getEndLine() - $buildMethod->getStartLine() + 1
        ));

        $this->assertStringContainsString('$this->mergeDocumentCollections($documents, $documentsITUser, $documentsBorrow)', $buildBody);
    }

    public function test_all_documents_list_has_subtype_filter(): void
    {
        $source = file_get_contents(resource_path('views/admin/it/list.blade.php'));

        $this->assertStringContainsString('ประเภทย่อย', $source);
        $this->assertStringContainsString('id="subtype-filter"', $source);
        $this->assertStringContainsString('name="subtype"', $source);
        $this->assertStringContainsString('id="type-filter"', $source);
        $this->assertStringContainsString('itDocumentSubtypes', $source);
    }

    public function test_admin_all_documents_filters_by_subtype(): void
    {
        $method = new ReflectionMethod(DocumentITAdminService::class, 'buildFilteredAllDocuments');
        $body = file_get_contents($method->getFileName());
        $body = implode("\n", array_slice(
            explode("\n", $body),
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertStringContainsString('$this->applyItSubtypeFilter($itQuery, $subtype)', $body);
        $this->assertStringContainsString("whereHas('documentUser'", $body);
        $this->assertStringContainsString('$this->applyBorrowSubtypeFilter($borrowQuery, $subtype)', $body);
    }

    public function test_all_documents_export_route_and_button_exist(): void
    {
        $source = file_get_contents(resource_path('views/admin/it/list.blade.php'));

        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.it.alllist.export'));
        $this->assertStringContainsString("route('admin.it.alllist.export', request()->query())", $source);
        $this->assertStringContainsString('Export Excel', $source);
    }

    public function test_export_all_documents_uses_filtered_query_and_expected_columns(): void
    {
        $exportMethod = new ReflectionMethod(DocumentITAdminService::class, 'exportAllDocuments');
        $exportBody = file_get_contents($exportMethod->getFileName());
        $exportBody = implode("\n", array_slice(
            explode("\n", $exportBody),
            $exportMethod->getStartLine() - 1,
            $exportMethod->getEndLine() - $exportMethod->getStartLine() + 1
        ));

        $this->assertStringContainsString('$this->resolveAllDocumentsFilters($request)', $exportBody);
        $this->assertStringContainsString('$this->buildFilteredAllDocuments($filters)', $exportBody);
        $this->assertStringContainsString('$this->allDocumentsExportHeaders()', $exportBody);
        $this->assertStringContainsString('$this->buildAllDocumentsExportRow($document)', $exportBody);
        $this->assertStringContainsString('HisLogExcelExporter', $exportBody);

        $rowMethod = new ReflectionMethod(DocumentITAdminService::class, 'buildAllDocumentsExportRow');
        $rowBody = file_get_contents($rowMethod->getFileName());
        $rowBody = implode("\n", array_slice(
            explode("\n", $rowBody),
            $rowMethod->getStartLine() - 1,
            $rowMethod->getEndLine() - $rowMethod->getStartLine() + 1
        ));

        $this->assertStringContainsString('$this->partitionExportProcessLogs($document)', $rowBody);
        $this->assertStringContainsString("stripos((string) \$log->details, 'GOLIVE')", file_get_contents(
            (new ReflectionMethod(DocumentITAdminService::class, 'partitionExportProcessLogs'))->getFileName()
        ));

        $headersMethod = new ReflectionMethod(DocumentITAdminService::class, 'allDocumentsExportHeaders');
        $headersMethod->setAccessible(true);
        $headers = $headersMethod->invoke($this->app->make(DocumentITAdminService::class));

        $this->assertSame([
            'เลขที่',
            'ชื่อเอกสาร',
            'รายละเอียด',
            'ผู้ขอ / แผนก / วันที่ขอ',
            'ผู้อนุมัติ',
            'สถานะ',
            'Log No',
            'บันทึกการดำเนินการ',
        ], $headers);
    }

    public function test_document_subtypes_match_create_form_options(): void
    {
        $service = $this->app->make(DocumentITAdminService::class);
        $subtypes = $service->documentSubtypes();

        $this->assertSame(
            ['HARDWARE', 'SOFTWARE', 'SSB', 'HIS', 'ERP', 'RESET_PASSWORD', 'OTHER'],
            array_keys($subtypes['IT'])
        );
        $this->assertSame(
            ['ขอแก้ไขสิทธิการใช้งาน', 'เลขาแพทย์', 'ฝ่ายบุคคล'],
            array_keys($subtypes['USER'])
        );
        $this->assertSame(
            ['Notebook', 'Computer', 'Printer', 'Projector', 'Ipad/Tablet', 'OTHER'],
            array_keys($subtypes['BORROW'])
        );
    }

    public function test_new_documents_list_has_filters(): void
    {
        $source = file_get_contents(resource_path('views/admin/it/list.blade.php'));

        $this->assertStringContainsString("in_array(\$action, ['all', 'new'])", $source);
        $this->assertStringContainsString("route('admin.it.newlist')", $source);
        $this->assertStringContainsString('name="search"', $source);
        $this->assertStringContainsString('id="type-filter"', $source);
        $this->assertStringContainsString('id="subtype-filter"', $source);
        $this->assertStringContainsString('name="department"', $source);
    }

    public function test_admin_new_documents_uses_filtered_query(): void
    {
        $method = new ReflectionMethod(DocumentITAdminService::class, 'adminNewDocuments');
        $body = file_get_contents($method->getFileName());
        $body = implode("\n", array_slice(
            explode("\n", $body),
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertStringContainsString('$this->resolveNewDocumentsFilters($request)', $body);
        $this->assertStringContainsString('$this->buildFilteredNewDocuments($filters)', $body);

        $buildMethod = new ReflectionMethod(DocumentITAdminService::class, 'buildFilteredNewDocuments');
        $buildBody = file_get_contents($buildMethod->getFileName());
        $buildBody = implode("\n", array_slice(
            explode("\n", $buildBody),
            $buildMethod->getStartLine() - 1,
            $buildMethod->getEndLine() - $buildMethod->getStartLine() + 1
        ));

        $this->assertStringContainsString("->where('status', 'pending')", $buildBody);
        $this->assertStringContainsString('->whereNull(\'assigned_user_id\')', $buildBody);
        $this->assertStringContainsString("->where('task_user', 'IT Unit Support')", $buildBody);
        $this->assertStringContainsString('$this->applyItSubtypeFilter($itQuery, $subtype)', $buildBody);
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
