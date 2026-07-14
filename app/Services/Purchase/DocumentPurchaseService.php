<?php

namespace App\Services\Purchase;

use App\Http\Requests\Purchase\StoreDocumentPurchaseRequest;
use App\Models\DocumentNumber;
use App\Models\DocumentPurchase;
use App\Services\DocumentWorkflowService;

class DocumentPurchaseService
{
    public function __construct(private DocumentWorkflowService $workflow) {}

    public function createDocument(StoreDocumentPurchaseRequest $request): DocumentPurchase
    {
        $type = $request->string('document_type')->toString();
        $needsDepartmentApproval = $type === 'po_edit';

        $title = $this->buildTitle($request, $type);
        $detail = trim((string) $request->input('detail', ''));

        $document = new DocumentPurchase;
        $document->requester = auth()->user()->userid;
        $document->document_phone = $request->string('document_phone')->toString();
        $document->document_number = DocumentNumber::getNextNumber($request->string('documentCode')->toString());
        $document->type = $type;
        $document->title = $title;
        $document->detail = $detail;
        $document->po_number = $type === 'po_edit' ? $request->input('po_number') : null;
        $document->po_reason = $type === 'po_edit' ? $this->resolvePoReason($request) : null;
        $document->status = 'wait_approval';
        $document->save();

        $dataField = [
            'selfApprove' => $needsDepartmentApproval ? 'false' : 'true',
            'approver' => $request->input('approver'),
        ];

        $taskData = [
            'document_type' => $needsDepartmentApproval ? 'purchase-edit' : 'purchase',
            'selfApprove' => ! $needsDepartmentApproval,
            'approver' => $request->input('approver'),
        ];

        $this->workflow->createApprover('purchase', $dataField, $document);
        $this->workflow->createTask($taskData, $document);
        $this->workflow->createFile($request, $document);

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'create',
            'details' => 'สร้างเอกสารจัดซื้อ',
        ]);

        return $document->fresh(['files', 'tasks', 'approvers', 'logs']);
    }

    private function buildTitle(StoreDocumentPurchaseRequest $request, string $type): string
    {
        if ($type === 'other') {
            return $request->string('title_other_text')->toString();
        }

        if ($type === 'po_edit') {
            return $this->resolvePoReason($request);
        }

        return DocumentPurchase::typeLabels()[$type] ?? $type;
    }

    private function resolvePoReason(StoreDocumentPurchaseRequest $request): string
    {
        $reason = $request->string('po_reason')->toString();

        if ($reason === 'อื่นๆ') {
            return 'อื่นๆ '.$request->string('po_reason_other')->toString();
        }

        return $reason;
    }
}
