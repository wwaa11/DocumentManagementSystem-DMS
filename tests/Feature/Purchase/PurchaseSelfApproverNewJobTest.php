<?php

namespace Tests\Feature\Purchase;

use App\Http\Requests\Purchase\StoreDocumentPurchaseRequest;
use App\Models\DocumentListApprover;
use App\Models\DocumentListTask;
use App\Models\User;
use App\Services\Purchase\DocumentPurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PurchaseSelfApproverNewJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DocumentListApprover::query()->create([
            'document_type' => 'purchase',
            'userid' => 'head_of_department',
            'step' => 1,
        ]);

        foreach ([
            [
                'document_type' => 'purchase',
                'step' => 1,
                'task_user' => 'purchase',
                'task_position' => 'ฝ่ายจัดซื้อ',
                'task_name' => 'รอดำเนินการจากฝ่ายจัดซื้อ',
            ],
            [
                'document_type' => 'purchase',
                'step' => 2,
                'task_user' => 'purchase-approve',
                'task_position' => 'ผู้อนุมัติฝ่ายจัดซื้อ',
                'task_name' => 'รออนุมัติจากฝ่ายจัดซื้อ',
            ],
        ] as $task) {
            DocumentListTask::query()->create($task);
        }
    }

    public function test_purchase_document_with_self_department_approver_keeps_purchase_task_waiting(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('users')) {
                $this->artisan('migrate', ['--no-interaction' => true]);
            }
        } catch (\Throwable) {
            $this->markTestSkipped('Database driver is not available in this environment.');
        }

        $user = User::query()->create([
            'userid' => '650099',
            'name' => 'หัวหน้าแผนกทดสอบ',
            'position' => 'หัวหน้าแผนก',
            'department' => 'แผนกทดสอบ',
            'division' => 'ฝ่ายทดสอบ',
            'email' => 'head@example.com',
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $payload = [
            'document_type' => 'quotation',
            'documentCode' => 'PURQ',
            'selfApprove' => 'true',
            'approver' => [
                'userid' => $user->userid,
                'position' => $user->position,
                'email' => $user->email,
            ],
            'document_phone' => '1234',
            'detail' => 'ขอใบเสนอราคา',
            'document_files' => [
                UploadedFile::fake()->create('quote.pdf', 100, 'application/pdf'),
            ],
        ];

        $validator = Validator::make($payload, (new StoreDocumentPurchaseRequest)->rules());
        $this->assertTrue($validator->passes());

        $request = StoreDocumentPurchaseRequest::create('/document/purchase/create', 'POST', $payload);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $document = app(DocumentPurchaseService::class)->createDocument($request);

        $document->refresh()->load(['tasks', 'approvers']);

        $this->assertSame('pending', $document->status);
        $this->assertTrue(
            $document->approvers->where('status', 'approve')->isNotEmpty(),
            'Department approver record should be auto-approved when requester is the approver.'
        );
        $this->assertTrue(
            $document->tasks
                ->where('task_user', 'purchase')
                ->where('status', 'wait')
                ->isNotEmpty(),
            'Purchase admin task should remain waiting so the document appears in New Jobs.'
        );
    }
}
