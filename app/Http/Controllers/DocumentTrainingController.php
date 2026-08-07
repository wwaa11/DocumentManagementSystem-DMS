<?php

namespace App\Http\Controllers;

use App\Http\Requests\Training\StoreDocumentTrainingRequest;
use App\Services\Training\DocumentTrainingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentTrainingController extends Controller
{
    public function __construct(private DocumentTrainingService $documentTrainingService) {}

    public function createDocument(StoreDocumentTrainingRequest $request): RedirectResponse
    {
        try {
            $document = $this->documentTrainingService->createDocument($request);
        } catch (\InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        if ($document->course_plan_item_id) {
            $document->load('coursePlanItem');
            $planId = $document->coursePlanItem?->course_plan_id;

            if ($planId) {
                return redirect()
                    ->route('document.course.show', $planId)
                    ->with('success', 'สร้างใบบันทึกฝึกอบรมและอัปเดตแผนหลักสูตรแล้ว');
            }
        }

        return redirect()->route('document.course')->with('success', 'สร้างเอกสารฝึกอบรมสำเร็จ!');
    }

    public function createProject(int|string $projectId): array
    {
        return $this->documentTrainingService->createProject($projectId);
    }

    public function getAttendance(Request $request): JsonResponse|RedirectResponse
    {
        $response = $this->documentTrainingService->getAttendance($request->project_id);

        if ($response === null) {
            return redirect()->route('document.index')->with('error', 'โปรเจกต์ไม่พบ!');
        }

        return response()->json($response, 200);
    }

    public function approveAttendance(Request $request): JsonResponse|RedirectResponse
    {
        $response = $this->documentTrainingService->approveAttendance(
            $request->project_id,
            $request->id,
            $request->userid,
        );

        if ($response === null) {
            return redirect()->route('document.index')->with('error', 'โปรเจกต์ไม่พบ!');
        }

        return response()->json($response, 200);
    }

    public function closeProject(Request $request): JsonResponse|RedirectResponse
    {
        $response = $this->documentTrainingService->closeProject($request->project_id);

        if ($response === null) {
            return redirect()->route('document.index')->with('error', 'โปรเจกต์ไม่พบ!');
        }

        return response()->json($response, 200);
    }

    public function saveAssessment(Request $request): JsonResponse
    {
        $this->documentTrainingService->saveAssessment($request->project_id, $request->assessments);

        return response()->json([
            'status' => 'success',
            'message' => 'บันทึกข้อมูลการประเมินสำเร็จ!',
        ]);
    }

    public function downloadPDF(int|string $id): BinaryFileResponse|StreamedResponse|\Illuminate\Http\Response|JsonResponse|RedirectResponse
    {
        return $this->documentTrainingService->downloadPdf($id);
    }

    public function cancelProject(Request $request): JsonResponse|RedirectResponse
    {
        $response = $this->documentTrainingService->cancelProject($request->project_id);

        if ($response === null) {
            return redirect()->route('document.index')->with('error', 'โปรเจกต์ไม่พบ!');
        }

        return response()->json($response, 200);
    }
}
