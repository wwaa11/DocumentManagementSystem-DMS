<?php

namespace App\Http\Controllers;

use App\Http\Requests\Media\StoreDocumentMediaRequest;
use App\Services\Media\DocumentMediaAdminService;
use App\Services\Media\DocumentMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentMediaController extends Controller
{
    public function __construct(
        private DocumentMediaService $documentMediaService,
        private DocumentMediaAdminService $documentMediaAdminService,
    ) {}

    public function createDocument(StoreDocumentMediaRequest $request): RedirectResponse
    {
        $this->documentMediaService->createDocument($request);

        return redirect()->route('document.index')->with('success', 'สร้างเอกสารสำเร็จ!');
    }

    public function adminDocumentCount(): JsonResponse
    {
        return $this->documentMediaAdminService->adminDocumentCount();
    }

    public function adminApproveDocuments(): View
    {
        return $this->documentMediaAdminService->adminApproveDocuments();
    }

    public function adminNewDocuments(): View
    {
        return $this->documentMediaAdminService->adminNewDocuments();
    }

    public function adminMyDocuments(): View
    {
        return $this->documentMediaAdminService->adminMyDocuments();
    }

    public function adminQueueDocuments(): View
    {
        return $this->documentMediaAdminService->adminQueueDocuments();
    }

    public function adminAllDocuments(Request $request): View
    {
        return $this->documentMediaAdminService->adminAllDocuments($request);
    }

    public function adminViewDocument(int|string $documentId, string $action): View
    {
        return $this->documentMediaAdminService->adminViewDocument($documentId, $action);
    }

    public function acceptDocument(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required']);

        return $this->documentMediaAdminService->acceptDocument($request);
    }

    public function cancelDocument(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'reason' => 'required',
        ]);

        return $this->documentMediaAdminService->cancelDocument($request);
    }

    public function cancelJob(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required']);

        return $this->documentMediaAdminService->cancelJob($request);
    }

    public function markFinish(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'detail' => 'nullable|string',
        ]);

        return $this->documentMediaAdminService->markFinish($request);
    }

    public function processDocument(Request $request): RedirectResponse
    {
        return $this->documentMediaAdminService->processDocument($request);
    }

    public function completeDocument(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'status' => 'required|in:approve,reject',
            'reason' => 'required_if:status,reject',
        ]);

        return $this->documentMediaAdminService->completeDocument($request);
    }

    public function completeAllDocument(): JsonResponse
    {
        return $this->documentMediaAdminService->completeAllDocument();
    }

    public function adminReportDocuments(Request $request): View
    {
        return $this->documentMediaAdminService->adminReportDocuments($request);
    }
}
