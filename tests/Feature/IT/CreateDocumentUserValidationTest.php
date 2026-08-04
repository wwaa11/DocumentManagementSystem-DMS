<?php

namespace Tests\Feature\IT;

use App\Models\User;
use App\Services\IT\DocumentITService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Tests\TestCase;

class CreateDocumentUserValidationTest extends TestCase
{
    public function test_set_user_field_data_handles_null_users(): void
    {
        $service = app(DocumentITService::class);
        $method = new ReflectionMethod(DocumentITService::class, 'setUserFieldData');
        $method->setAccessible(true);

        $this->assertSame('', $method->invoke($service, null, 'ขอแก้ไขสิทธิการใช้งาน'));
    }

    public function test_create_edit_rights_document_rejects_missing_users(): void
    {
        $this->actingAs($this->makeUser());

        $request = Request::create('/it/create', 'POST', [
            'main_document_type' => 'user',
            'title' => 'ขอแก้ไขสิทธิการใช้งาน',
            'document_phone' => '1234',
            'selfApprove' => 'true',
            'approver' => [
                'userid' => '650002',
                'position' => 'Manager',
                'email' => 'approver@example.com',
            ],
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(DocumentITService::class)->createDocument($request);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('users', $exception->errors());
            throw $exception;
        }
    }

    public function test_create_edit_rights_document_rejects_empty_users(): void
    {
        $this->actingAs($this->makeUser());

        $request = Request::create('/it/create', 'POST', [
            'main_document_type' => 'user',
            'title' => 'ขอแก้ไขสิทธิการใช้งาน',
            'document_phone' => '1234',
            'selfApprove' => 'true',
            'approver' => [
                'userid' => '650002',
                'position' => 'Manager',
                'email' => 'approver@example.com',
            ],
            'users' => [],
        ]);

        $this->expectException(ValidationException::class);

        app(DocumentITService::class)->createDocument($request);
    }

    private function makeUser(): User
    {
        return new User([
            'userid' => '650001',
            'name' => 'ผู้ทดสอบระบบ',
            'position' => 'Staff',
            'department' => 'IT',
            'division' => 'ฝ่ายเทคโนโลยีสารสนเทศ',
            'email' => 'requester@example.com',
        ]);
    }
}
