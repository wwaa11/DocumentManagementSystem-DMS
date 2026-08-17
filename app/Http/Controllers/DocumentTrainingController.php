<?php

namespace App\Http\Controllers;

use App\Http\Requests\Training\ApproveTrainingAttendanceRequest;
use App\Http\Requests\Training\RemoveTrainingDateRequest;
use App\Http\Requests\Training\RemoveTrainingLecturerRequest;
use App\Http\Requests\Training\RemoveTrainingParticipantRequest;
use App\Http\Requests\Training\RemoveTrainingTimeRequest;
use App\Http\Requests\Training\StoreDocumentTrainingRequest;
use App\Http\Requests\Training\StoreTrainingDateRequest;
use App\Http\Requests\Training\StoreTrainingLecturerRequest;
use App\Http\Requests\Training\StoreTrainingParticipantRequest;
use App\Http\Requests\Training\StoreTrainingTimeRequest;
use App\Http\Requests\Training\UpdateTrainingDateRequest;
use App\Http\Requests\Training\UpdateTrainingTimeRequest;
use App\Models\DocumentTraining;
use App\Services\Training\DocumentTrainingService;
use App\Services\Training\TrainingScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentTrainingController extends Controller
{
    public function __construct(
        private DocumentTrainingService $documentTrainingService,
        private TrainingScheduleService $trainingScheduleService,
    ) {}

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

    public function createProject(Request $request): JsonResponse
    {
        return response()->json(
            $this->documentTrainingService->createProject($request->integer('project_id') ?: $request->input('project_id')),
        );
    }

    public function getAttendance(Request $request): JsonResponse|RedirectResponse
    {
        $response = $this->documentTrainingService->getAttendance($request->project_id);

        if ($response === null) {
            return redirect()->route('document.index')->with('error', 'โปรเจกต์ไม่พบ!');
        }

        return response()->json($response, 200);
    }

    public function approveAttendance(ApproveTrainingAttendanceRequest $request): JsonResponse|RedirectResponse
    {
        $response = $this->documentTrainingService->approveAttendance(
            $request->integer('project_id'),
            [
                'transaction_id' => $request->input('transaction_id'),
                'transaction_ids' => $request->input('transaction_ids', []),
                'approve_all' => $request->boolean('approve_all'),
                'userid' => $request->input('userid'),
            ],
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

    public function projectDetail(Request $request): JsonResponse
    {
        $document = DocumentTraining::find($request->project_id);

        if (! $document || $document->training_id === null) {
            return $this->scheduleError('ไม่พบโครงการฝึกอบรมในระบบ HRD', 404);
        }

        return response()->json($this->trainingScheduleService->projectDetail($document));
    }

    public function assessmentParticipants(Request $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->assessmentParticipants($document),
        );
    }

    public function storeTrainingDate(StoreTrainingDateRequest $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->addDate($document, $request->validated()),
        );
    }

    public function updateTrainingDate(UpdateTrainingDateRequest $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->updateDate($document, $request->validated()),
        );
    }

    public function removeTrainingDate(RemoveTrainingDateRequest $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->removeDate($document, $request->integer('date_id')),
        );
    }

    public function storeTrainingTime(StoreTrainingTimeRequest $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->addTime($document, $request->validated()),
        );
    }

    public function updateTrainingTime(UpdateTrainingTimeRequest $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->updateTime($document, $request->validated()),
        );
    }

    public function removeTrainingTime(RemoveTrainingTimeRequest $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->removeTime($document, $request->integer('time_id')),
        );
    }

    public function storeTrainingParticipant(StoreTrainingParticipantRequest $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->addParticipants(
                $document,
                $request->integer('time_id'),
                $request->input('users'),
            ),
        );
    }

    public function removeTrainingParticipant(RemoveTrainingParticipantRequest $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->removeParticipant($document, $request->validated()),
        );
    }

    public function storeTrainingLecturer(StoreTrainingLecturerRequest $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->addLecturers(
                $document,
                $request->integer('date_id'),
                $request->input('users'),
            ),
        );
    }

    public function removeTrainingLecturer(RemoveTrainingLecturerRequest $request): JsonResponse
    {
        return $this->runScheduleAction(
            $request->integer('project_id'),
            fn (DocumentTraining $document): array => $this->trainingScheduleService->removeLecturer($document, $request->validated()),
        );
    }

    /**
     * @param  \Closure(DocumentTraining): array<string, mixed>  $action
     */
    private function runScheduleAction(int|string $projectId, \Closure $action): JsonResponse
    {
        $document = DocumentTraining::find($projectId);

        if (! $document) {
            return $this->scheduleError('ไม่พบเอกสารการฝึกอบรม', 404);
        }

        if (! $document->isScheduleEditableBy(auth()->user())) {
            return $this->scheduleError('คุณไม่มีสิทธิ์แก้ไขรายละเอียดโครงการนี้', 403);
        }

        return response()->json($action($document));
    }

    private function scheduleError(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
