<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\AcceptUserDocumentRequest;
use App\Http\Requests\User\CompleteUserDocumentRequest;
use App\Http\Requests\User\ProcessUserDocumentRequest;
use App\Services\User\DocumentUserAdminService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentUserController extends Controller
{
    public function __construct(private DocumentUserAdminService $documentUserAdminService) {}

    public function adminDocumentCount(string $type): JsonResponse
    {
        return $this->documentUserAdminService->adminDocumentCount($type);
    }

    public function adminApproveDocuments(string $type): View
    {
        return $this->documentUserAdminService->adminApproveDocuments($type);
    }

    public function adminNewDocuments(string $type): View
    {
        return $this->documentUserAdminService->adminNewDocuments($type);
    }

    public function adminMyDocuments(string $type): View
    {
        return $this->documentUserAdminService->adminMyDocuments($type);
    }

    public function adminAllDocuments(Request $request, string $type): View
    {
        return $this->documentUserAdminService->adminAllDocuments($request, $type);
    }

    public function viewDocument(string $type, mixed $document_id, string $action): View
    {
        return $this->documentUserAdminService->viewDocument($type, $document_id, $action);
    }

    public function acceptDocument(AcceptUserDocumentRequest $request): JsonResponse
    {
        return $this->documentUserAdminService->acceptDocument($request);
    }

    public function cancelDocument(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required',
            'reason' => 'required',
        ]);

        return $this->documentUserAdminService->cancelDocument($request);
    }

    public function cancelJob(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required',
        ]);

        return $this->documentUserAdminService->cancelJob($request);
    }

    public function processDocument(ProcessUserDocumentRequest $request): RedirectResponse
    {
        return $this->documentUserAdminService->processDocument($request);
    }

    public function completeDocument(CompleteUserDocumentRequest $request): JsonResponse
    {
        return $this->documentUserAdminService->completeDocument($request);
    }

    public function completeAllDocument(Request $request): JsonResponse
    {
        return $this->documentUserAdminService->completeAllDocument($request);
    }

    public function adminReportDocuments(Request $request): View
    {
        return $this->documentUserAdminService->adminReportDocuments($request);
    }
}
