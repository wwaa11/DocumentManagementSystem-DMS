<?php

namespace App\Http\Controllers;

use App\Http\Requests\IT\SetDocumentPendingRequest;
use App\Http\Requests\IT\StoreDocumentMessageRequest;
use App\Services\IT\DocumentITAdminService;
use App\Services\IT\DocumentITService;
use App\Services\IT\DocumentMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentITController extends Controller
{
    public function __construct(
        private DocumentITService $documentITService,
        private DocumentITAdminService $documentITAdminService,
        private DocumentMessageService $documentMessageService,
    ) {}

    public function createDocument(Request $request): RedirectResponse
    {
        $this->documentITService->createDocument($request);

        session()->flash('success', 'สร้างเอกสารสำเร็จ!');

        return redirect()->route('document.index');
    }

    public function adminDocumentCount(): JsonResponse
    {
        return $this->documentITAdminService->adminDocumentCount();
    }

    public function adminHardwareDocuments(): View
    {
        return $this->documentITAdminService->adminHardwareDocuments();
    }

    public function adminApproveDocuments(): View
    {
        return $this->documentITAdminService->adminApproveDocuments();
    }

    public function adminNewDocuments(): View
    {
        return $this->documentITAdminService->adminNewDocuments();
    }

    public function adminMyDocuments(): View
    {
        return $this->documentITAdminService->adminMyDocuments();
    }

    public function adminAllDocuments(Request $request): View
    {
        return $this->documentITAdminService->adminAllDocuments($request);
    }

    public function adminviewDocument(string $type, int|string $document_id, string $action): View
    {
        return $this->documentITAdminService->adminviewDocument($type, $document_id, $action);
    }

    public function approveHardwareDocument(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required',
            'status' => 'required|in:approve,reject',
            'reason' => 'required_if:status,reject',
        ]);

        return $this->documentITAdminService->approveHardwareDocument($request);
    }

    public function acceptDocument(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required|in:IT,USER',
        ]);

        return $this->documentITAdminService->acceptDocument($request);
    }

    public function cancelDocument(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required',
            'reason' => 'required',
        ]);

        return $this->documentITAdminService->cancelDocument($request);
    }

    public function cancelJob(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required',
        ]);

        return $this->documentITAdminService->cancelJob($request);
    }

    public function processDocument(Request $request): RedirectResponse
    {
        return $this->documentITAdminService->processDocument($request);
    }

    public function completeDocument(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required',
            'status' => 'required|in:approve,reject',
        ]);

        return $this->documentITAdminService->completeDocument($request);
    }

    public function completeAllDocument(Request $request): JsonResponse
    {
        return $this->documentITAdminService->completeAllDocument();
    }

    public function adminBorrowDocuments(): View
    {
        return $this->documentITAdminService->adminBorrowDocuments();
    }

    public function adminBorrowAdd(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'date' => 'required',
            'serial' => 'required',
        ]);

        return $this->documentITAdminService->adminBorrowAdd($request);
    }

    public function adminBorrowRemove(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
        ]);

        return $this->documentITAdminService->adminBorrowRemove($request);
    }

    public function adminBorrowSummary(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
        ]);

        return $this->documentITAdminService->adminBorrowSummary($request);
    }

    public function adminBorrowApprove(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required|in:borrow,return',
        ]);

        return $this->documentITAdminService->adminBorrowApprove($request);
    }

    public function borrowReturn(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
        ]);

        return $this->documentITAdminService->borrowReturn($request);
    }

    public function adminBorrowRetrieve(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
        ]);

        return $this->documentITAdminService->adminBorrowRetrieve($request);
    }

    public function adminReportDocuments(Request $request): View
    {
        return $this->documentITAdminService->adminReportDocuments($request);
    }

    public function documentMessages(string $type, int|string $document_id): JsonResponse
    {
        return $this->documentMessageService->getMessages($type, $document_id);
    }

    public function storeDocumentMessage(StoreDocumentMessageRequest $request, string $type, int|string $document_id): JsonResponse
    {
        return $this->documentMessageService->storeMessage($request, $type, $document_id);
    }

    public function setDocumentPending(SetDocumentPendingRequest $request): JsonResponse
    {
        return $this->documentMessageService->setPending($request);
    }
}
