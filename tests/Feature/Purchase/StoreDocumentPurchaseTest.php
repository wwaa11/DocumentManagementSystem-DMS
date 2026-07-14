<?php

namespace Tests\Feature\Purchase;

use App\Http\Requests\Purchase\StoreDocumentPurchaseRequest;
use App\Models\DocumentPurchase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreDocumentPurchaseTest extends TestCase
{
    public function test_quotation_request_passes_validation(): void
    {
        $validator = Validator::make([
            'document_type' => 'quotation',
            'documentCode' => 'PURQ',
            'selfApprove' => 'true',
            'approver' => [
                'userid' => '650099',
                'position' => 'หัวหน้าแผนก',
                'email' => 'approver@example.com',
            ],
            'document_phone' => '1234',
            'detail' => 'ขอใบเสนอราคา',
            'document_files' => [
                UploadedFile::fake()->create('quote.pdf', 100, 'application/pdf'),
            ],
        ], (new StoreDocumentPurchaseRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_po_edit_request_requires_po_fields(): void
    {
        $validator = Validator::make([
            'document_type' => 'po_edit',
            'documentCode' => 'PUR',
            'selfApprove' => 'false',
            'approver' => [
                'userid' => '650099',
                'position' => 'หัวหน้าแผนก',
                'email' => 'approver@example.com',
            ],
            'document_phone' => '1234',
        ], (new StoreDocumentPurchaseRequest)->rules(), (new StoreDocumentPurchaseRequest)->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('po_number', $validator->errors()->toArray());
        $this->assertArrayHasKey('po_reason', $validator->errors()->toArray());
    }

    public function test_po_edit_other_reason_requires_other_text(): void
    {
        $validator = Validator::make([
            'document_type' => 'po_edit',
            'documentCode' => 'PUR',
            'selfApprove' => 'false',
            'approver' => [
                'userid' => '650099',
                'position' => 'หัวหน้าแผนก',
                'email' => 'approver@example.com',
            ],
            'document_phone' => '1234',
            'po_number' => 'PO-001',
            'po_reason' => 'อื่นๆ',
        ], (new StoreDocumentPurchaseRequest)->rules(), (new StoreDocumentPurchaseRequest)->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('po_reason_other', $validator->errors()->toArray());
    }

    public function test_create_without_phone_fails_validation(): void
    {
        $validator = Validator::make([
            'document_type' => 'code',
            'documentCode' => 'PURC',
            'selfApprove' => 'true',
            'approver' => [
                'userid' => '650099',
                'position' => 'หัวหน้าแผนก',
                'email' => 'approver@example.com',
            ],
            'detail' => 'test',
        ], (new StoreDocumentPurchaseRequest)->rules(), (new StoreDocumentPurchaseRequest)->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('document_phone', $validator->errors()->toArray());
    }

    public function test_type_codes_and_labels_are_mapped(): void
    {
        $this->assertSame([
            'code' => 'PURC',
            'quotation' => 'PURQ',
            'boq' => 'PURB',
            'po_edit' => 'PUR',
            'other' => 'PURE',
        ], DocumentPurchase::typeCodes());

        $this->assertSame('ขอเพิ่มแก้ไข Code สินค้า', DocumentPurchase::typeLabels()['code']);
        $this->assertSame('ขออนุมัติแก้ไข/ยกเลิกใบสั่งซื้อ', DocumentPurchase::typeLabels()['po_edit']);
    }
}
