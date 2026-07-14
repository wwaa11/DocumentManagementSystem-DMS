<?php

namespace App\Services\Media;

use App\Http\Requests\Media\StoreDocumentMediaRequest;
use App\Models\DocumentMedia;
use App\Models\DocumentMediaSignItem;
use App\Models\DocumentNumber;
use App\Services\DocumentWorkflowService;

class DocumentMediaService
{
    public function __construct(private DocumentWorkflowService $workflow) {}

    public function createDocument(StoreDocumentMediaRequest $request): DocumentMedia
    {
        $type = $request->string('document_type')->toString();

        $document = new DocumentMedia;
        $document->requester = auth()->user()->userid;
        $document->document_phone = $request->string('document_phone')->toString();
        $document->document_number = DocumentNumber::getNextNumber('MED');
        $document->type = $type;
        $document->title = $request->string('title')->toString();
        $document->detail = trim((string) $request->input('detail', ''));
        $document->required_date = $request->input('required_date');
        $document->other_text = $type === 'other' ? $request->input('other_text') : null;
        $document->sign_location = $type === 'sign' ? $request->input('sign_location') : null;
        $document->brochure_sizes = $type === 'brochure' ? $request->input('brochure_sizes') : null;
        $document->brochure_print_type = $type === 'brochure' ? $request->input('brochure_print_type') : null;
        $document->photo_work_types = $type === 'photo_video' ? $request->input('photo_work_types') : null;
        $document->photo_date = $type === 'photo_video' ? $request->input('photo_date') : null;
        $document->photo_time = $type === 'photo_video' ? $request->input('photo_time') : null;
        $document->photo_location = $type === 'photo_video' ? $request->input('photo_location') : null;
        $document->status = 'wait_approval';
        $document->save();

        if ($type === 'sign') {
            $this->storeSignItems($request, $document);
        }

        $dataField = [
            'selfApprove' => 'false',
            'approver' => $request->input('approver'),
        ];

        $taskData = [
            'document_type' => 'media',
            'selfApprove' => false,
            'approver' => $request->input('approver'),
        ];

        $this->workflow->createApprover('media', $dataField, $document);
        $this->workflow->createTask($taskData, $document);
        $this->workflow->createFile($request, $document);

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'create',
            'details' => 'สร้างเอกสารขออนุมัติผลิตสื่อ',
        ]);

        return $document->fresh(['files', 'tasks', 'approvers', 'logs', 'signItems']);
    }

    private function storeSignItems(StoreDocumentMediaRequest $request, DocumentMedia $document): void
    {
        $signTypes = $request->input('sign_types', []);
        $signDetails = $request->input('sign_details', []);

        foreach ($signTypes as $signType) {
            $document->signItems()->save(new DocumentMediaSignItem([
                'sign_type' => $signType,
                'detail' => $signDetails[$signType] ?? null,
            ]));
        }
    }
}
