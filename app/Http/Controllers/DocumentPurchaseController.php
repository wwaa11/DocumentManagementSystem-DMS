<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\StoreDocumentPurchaseRequest;
use App\Services\Purchase\DocumentPurchaseAdminService;
use App\Services\Purchase\DocumentPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentPurchaseController extends Controller
{
    public function __construct(
        private DocumentPurchaseService $documentPurchaseService,
        private DocumentPurchaseAdminService $documentPurchaseAdminService,
    ) {}

    public function createDocument(StoreDocumentPurchaseRequest $request): RedirectResponse
    {
        $this->documentPurchaseService->createDocument($request);

        return redirect()->route('document.index')->with('success', 'สร้างเอกสารสำเร็จ!');
    }

    public function adminDocumentCount(): JsonResponse
    {
        return $this->documentPurchaseAdminService->adminDocumentCount();
    }

    public function adminApproveDocuments(): View
    {
        return $this->documentPurchaseAdminService->adminApproveDocuments();
    }

    public function adminHeadDocuments(): View
    {
        return $this->documentPurchaseAdminService->adminHeadDocuments();
    }

    public function adminNewDocuments(): View
    {
        return $this->documentPurchaseAdminService->adminNewDocuments();
    }

    public function adminMyDocuments(): View
    {
        return $this->documentPurchaseAdminService->adminMyDocuments();
    }

    public function adminAllDocuments(Request $request): View
    {
        return $this->documentPurchaseAdminService->adminAllDocuments($request);
    }

    public function adminViewDocument(int|string $documentId, string $action): View
    {
        return $this->documentPurchaseAdminService->adminViewDocument($documentId, $action);
    }

    public function acceptDocument(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
        ]);

        return $this->documentPurchaseAdminService->acceptDocument($request);
    }

    public function cancelDocument(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'reason' => 'required',
        ]);

        return $this->documentPurchaseAdminService->cancelDocument($request);
    }

    public function cancelJob(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
        ]);

        return $this->documentPurchaseAdminService->cancelJob($request);
    }

    public function processDocument(Request $request): RedirectResponse
    {
        return $this->documentPurchaseAdminService->processDocument($request);
    }

    public function completeDocument(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'status' => 'required|in:approve,reject',
            'reason' => 'required_if:status,reject',
            'role' => 'nullable|in:purchase-approve,purchase-head',
        ]);

        return $this->documentPurchaseAdminService->completeDocument($request);
    }

    public function completeAllDocument(Request $request): JsonResponse
    {
        $request->validate([
            'role' => 'nullable|in:purchase-approve,purchase-head',
        ]);

        return $this->documentPurchaseAdminService->completeAllDocument($request);
    }

    public function adminReportDocuments(Request $request): View
    {
        return $this->documentPurchaseAdminService->adminReportDocuments($request);
    }
}
