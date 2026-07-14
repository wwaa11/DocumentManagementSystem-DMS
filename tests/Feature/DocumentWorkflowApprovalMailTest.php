<?php

namespace Tests\Feature;

use App\Models\DocumentPurchase;
use App\Models\User;
use App\Services\CollectionPaginator;
use App\Services\DocumentWorkflowService;
use ReflectionMethod;
use Tests\TestCase;

class DocumentWorkflowApprovalMailTest extends TestCase
{
    public function test_approval_email_view_contains_structured_content_and_cta(): void
    {
        $html = view('emails.document-approval', [
            'appName' => 'DMS',
            'subject' => '[DMS] ขออนุมัติ: ขอใบเสนอราคา',
            'title' => 'ขอใบเสนอราคา',
            'documentType' => 'ขอใบเสนอราคา',
            'documentId' => 42,
            'requesterName' => 'สมชาย ใจดี',
            'submittedAt' => '14/07/2026 16:00',
            'detail' => 'รายละเอียดงานทดสอบ',
            'approveUrl' => 'https://example.test/document/PURCHASE/approve/42',
        ])->render();

        $this->assertStringContainsString('มีเอกสารรอการอนุมัติ', $html);
        $this->assertStringContainsString('ขอใบเสนอราคา', $html);
        $this->assertStringContainsString('สมชาย ใจดี', $html);
        $this->assertStringContainsString('รายละเอียดงานทดสอบ', $html);
        $this->assertStringContainsString('เปิดเอกสารเพื่ออนุมัติ', $html);
        $this->assertStringContainsString('https://example.test/document/PURCHASE/approve/42', $html);
    }

    public function test_build_approval_email_body_includes_document_context(): void
    {
        $user = new User([
            'userid' => '650001',
            'name' => 'ผู้ทดสอบระบบ',
            'position' => 'Staff',
            'department' => 'IT',
            'division' => 'ฝ่ายเทคโนโลยีสารสนเทศ',
            'email' => 'requester@example.com',
        ]);
        $this->actingAs($user);

        $document = new DocumentPurchase([
            'requester' => '650001',
            'type' => 'quotation',
            'title' => 'ขอใบเสนอราคาทดสอบ',
            'detail' => 'ต้องการใบเสนอราคาอุปกรณ์',
            'status' => 'wait_approval',
        ]);
        $document->id = 99;
        $document->created_at = now();

        $service = new DocumentWorkflowService(app(CollectionPaginator::class));
        $method = new ReflectionMethod(DocumentWorkflowService::class, 'buildApprovalEmailBody');
        $method->setAccessible(true);

        $body = $method->invoke(
            $service,
            $document,
            'ขอใบเสนอราคาทดสอบ',
            '[DMS] ขออนุมัติ: ขอใบเสนอราคาทดสอบ'
        );

        $this->assertStringContainsString('มีเอกสารรอการอนุมัติ', $body);
        $this->assertStringContainsString('เปิดเอกสารเพื่ออนุมัติ', $body);
        $this->assertStringContainsString('ผู้ทดสอบระบบ', $body);
        $this->assertStringContainsString('ต้องการใบเสนอราคาอุปกรณ์', $body);
        $this->assertStringContainsString('/document/PURCHASE/approve/99', $body);
        $this->assertStringContainsString('#99', $body);
    }

    public function test_resolve_document_title_joins_array_titles(): void
    {
        $document = new DocumentPurchase([
            'type' => 'code',
            'title' => 'ส่วนที่หนึ่ง|ส่วนที่สอง',
        ]);

        $service = new DocumentWorkflowService(app(CollectionPaginator::class));
        $method = new ReflectionMethod(DocumentWorkflowService::class, 'resolveDocumentTitle');
        $method->setAccessible(true);

        $this->assertSame('ส่วนที่หนึ่ง ส่วนที่สอง', $method->invoke($service, $document));
    }
}
