<?php

namespace Tests\Feature\Media;

use App\Http\Requests\Media\StoreDocumentMediaRequest;
use App\Models\DocumentMedia;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreDocumentMediaTest extends TestCase
{
    public function test_sign_request_passes_validation(): void
    {
        $validator = Validator::make([
            'title' => 'งานป้ายประชาสัมพันธ์',
            'document_type' => 'sign',
            'documentCode' => 'MED',
            'selfApprove' => 'false',
            'approver' => [
                'userid' => '650099',
                'position' => 'หัวหน้าแผนก',
                'email' => 'approver@example.com',
            ],
            'document_phone' => '1234',
            'required_date' => '2026-07-20',
            'sign_types' => ['standee', 'poster'],
            'sign_location' => 'โถงชั้น 1',
            'detail' => 'รายละเอียด',
        ], (new StoreDocumentMediaRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_brochure_requires_size_and_print_type(): void
    {
        $validator = Validator::make([
            'title' => 'โบรชัวร์',
            'document_type' => 'brochure',
            'documentCode' => 'MED',
            'selfApprove' => 'false',
            'approver' => [
                'userid' => '650099',
                'position' => 'หัวหน้าแผนก',
                'email' => 'a@example.com',
            ],
            'document_phone' => '1234',
            'required_date' => '2026-07-20',
        ], (new StoreDocumentMediaRequest)->rules(), (new StoreDocumentMediaRequest)->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('brochure_sizes', $validator->errors()->toArray());
        $this->assertArrayHasKey('brochure_print_type', $validator->errors()->toArray());
    }

    public function test_type_labels_are_mapped(): void
    {
        $this->assertSame('ป้าย', DocumentMedia::typeLabels()['sign']);
        $this->assertSame('ถ่ายภาพ / วิดีโอ', DocumentMedia::typeLabels()['photo_video']);
        $this->assertArrayHasKey('standee', DocumentMedia::signTypeLabels());
    }
}
