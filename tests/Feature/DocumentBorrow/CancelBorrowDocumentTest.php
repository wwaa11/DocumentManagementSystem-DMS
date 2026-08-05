<?php

namespace Tests\Feature\DocumentBorrow;

use ReflectionMethod;
use Tests\TestCase;

class CancelBorrowDocumentTest extends TestCase
{
    public function test_borrow_action_view_includes_cancel_document_button(): void
    {
        $document = new class
        {
            public int $id = 12;

            public string $status = 'pending';

            public function allHardwareRetrieve(): bool
            {
                return false;
            }
        };

        $html = view('admin.it.actions.borrow', ['document' => $document])->render();
        $source = file_get_contents(resource_path('views/admin/it/actions/borrow.blade.php'));

        $this->assertStringContainsString('ยกเลิกเอกสารนี้', $html);
        $this->assertStringContainsString('cancelDocument', $html);
        $this->assertStringContainsString('route("admin.it.cancel")', $source);
        $this->assertStringContainsString('type: "BORROW"', $source);
        $this->assertStringContainsString('route("admin.it.borrowlist")', $source);
    }

    public function test_borrow_action_view_hides_cancel_for_completed_status(): void
    {
        $document = new class
        {
            public int $id = 12;

            public string $status = 'complete';

            public function allHardwareRetrieve(): bool
            {
                return true;
            }
        };

        $html = view('admin.it.actions.borrow', ['document' => $document])->render();

        $this->assertStringNotContainsString('ยกเลิกเอกสารนี้', $html);
    }

    public function test_cancel_document_service_supports_borrow_type(): void
    {
        $method = new ReflectionMethod(\App\Services\IT\DocumentITAdminService::class, 'cancelDocument');
        $source = file($method->getFileName());
        $body = implode('', array_slice($source, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));

        $this->assertStringContainsString("type == 'BORROW'", $body);
        $this->assertStringContainsString('DocumentBorrow::find', $body);
        $this->assertStringContainsString('IT Unit Support', $body);
    }
}
