<?php

namespace App\Services\Training;

use App\Models\CourseInstructor;
use App\Models\CoursePlan;
use App\Models\CoursePlanItem;
use App\Models\CourseResponsible;
use App\Models\DocumentTraining;
use App\Models\DocumentTrainingDate;
use App\Models\DocumentTrainingMentor;
use App\Models\DocumentTrainingParticipant;
use App\Models\User;
use App\Services\DocumentWorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentTrainingService
{
    public function __construct(
        private DocumentWorkflowService $workflow,
        private TrainingApiClient $trainingApi,
    ) {}

    public function createDocument(Request $request): DocumentTraining
    {
        return DB::transaction(function () use ($request): DocumentTraining {
            $coursePlanItem = null;
            $linkedItemId = $request->integer('course_plan_item_id')
                ?: $request->integer('substitute_course_plan_item_id')
                ?: null;

            if ($linkedItemId) {
                $coursePlanItem = CoursePlanItem::query()->find($linkedItemId);

                if ($coursePlanItem?->hasTrainingDocument()) {
                    throw new \InvalidArgumentException('หลักสูตรนี้มีใบบันทึกฝึกอบรมแล้ว ไม่สามารถสร้างซ้ำได้');
                }
            }

            if ($coursePlanItem === null && $request->source_type === 'out_of_plan') {
                $coursePlanItem = $this->createOutOfPlanCourseItem($request, auth()->user());
            }

            $substituteTopic = $request->input('substitute_topic')
                ?: ($coursePlanItem && $request->source_type === 'substitute' ? $coursePlanItem->name : null);

            $detail = match ($request->source_type) {
                'in_plan' => 'จัดในแผน ลำดับที่ '.($coursePlanItem?->number ?? $request->plan_no).'(อ้างอิงลำดับที่ในแผนการฝึกอบรมประจำปี)',
                'substitute' => 'จัดแทนในแผน เรื่อง '.($substituteTopic ?? '-').' เนื่องจาก '.$request->substitute_reason,
                'out_of_plan' => 'จัดนอกแผน เนื่องจาก '.$request->out_of_plan_reason,
                default => '',
            };

            if ($coursePlanItem) {
                $detail = trim($detail.' · หลักสูตรแผน: '.$coursePlanItem->name);
            }

            $document = new DocumentTraining;
            $document->requester = auth()->user()->userid;
            $document->title = $request->training_name;
            $document->hours = $request->duration_hours;
            $document->minutes = $request->duration_minutes ?? 0;
            $document->detail = $detail;
            $document->project_type = 'multiple';
            $document->course_plan_item_id = $coursePlanItem?->id;
            $document->save();

            $this->createDates($document, $request);

            $approverField = [
                'selfApprove' => false,
                'approver' => $request['approver'],
            ];
            $taskData = [
                'document_type' => 'training',
                'selfApprove' => false,
                'approver' => $request['approver'],
            ];

            $isApprove = $this->workflow->createApprover('training', $approverField, $document);
            $this->workflow->createFile($request, $document);
            $this->workflow->createTask($taskData, $document);

            foreach ($request->mentors_userid ?? [] as $index => $mentor) {
                $documentTrainingMentor = new DocumentTrainingMentor;
                $documentTrainingMentor->document_training_id = $document->id;
                $documentTrainingMentor->mentor = $mentor;
                $documentTrainingMentor->mentor_name = $request->mentors_name[$index];
                $documentTrainingMentor->mentor_position = $request->mentors_position[$index];
                $documentTrainingMentor->save();
            }

            foreach ($request->participants_userid as $index => $participant) {
                $documentTrainingParticipant = new DocumentTrainingParticipant;
                $documentTrainingParticipant->document_training_id = $document->id;
                $documentTrainingParticipant->participant = $participant;
                $documentTrainingParticipant->participant_name = $request->participants_name[$index];
                $documentTrainingParticipant->participant_position = $request->participants_position[$index];
                $documentTrainingParticipant->participant_department = $request->participants_dept[$index];
                $documentTrainingParticipant->save();
            }

            if ($isApprove) {
                $this->createProject($document->id);
            }

            return $document->fresh(['coursePlanItem']);
        });
    }

    /**
     * Create a course-plan item (and plan if needed) so out-of-plan training appears in แผนการฝึก.
     */
    private function createOutOfPlanCourseItem(Request $request, User $user): CoursePlanItem
    {
        $allowedDepartments = $user->courseDepartments();
        $requestedDepartment = (string) $request->input('department');
        $department = $requestedDepartment;

        if ($department === '' && in_array($user->department, $allowedDepartments, true)) {
            $department = $user->department;
        }

        if ($department === '') {
            $department = $allowedDepartments[0] ?? '';
        }

        $year = (int) ($request->input('year') ?: now()->year);

        if ($department === '' || ! $user->canCreateCourseForDepartment($department)) {
            throw new \InvalidArgumentException('กรุณาเลือกแผนกที่คุณมีสิทธิ์สร้างแผนหลักสูตร');
        }

        $plan = CoursePlan::query()->firstOrCreate(
            [
                'year' => $year,
                'department' => $department,
            ],
            [
                'created_by' => $user->userid,
                'status' => 'complete',
            ]
        );

        $nextNumber = ((int) $plan->items()->max('sort_order')) + 1;
        $scheduleMonths = $this->scheduleMonthsFromRequest($request);

        $item = $plan->items()->create([
            'number' => (string) $nextNumber,
            'name' => $request->training_name,
            'origin' => 'จัดนอกแผน: '.$request->out_of_plan_reason,
            'objective' => CoursePlanItem::OUT_OF_PLAN_OBJECTIVE,
            'training_type' => 'internal',
            'schedule_months' => $scheduleMonths !== [] ? $scheduleMonths : [(int) now()->month],
            'estimated_cost' => null,
            'sort_order' => $nextNumber,
        ]);

        foreach ($request->mentors_userid ?? [] as $index => $mentorUserid) {
            CourseInstructor::query()->create([
                'course_plan_item_id' => $item->id,
                'userid' => $mentorUserid,
                'name' => $request->mentors_name[$index] ?? $mentorUserid,
                'position' => $request->mentors_position[$index] ?? null,
                'source_type' => 'internal',
            ]);
        }

        CourseResponsible::query()->create([
            'course_plan_item_id' => $item->id,
            'userid' => $user->userid,
            'name' => $user->name,
            'position' => $user->position,
        ]);

        return $item;
    }

    /**
     * @return list<int>
     */
    private function scheduleMonthsFromRequest(Request $request): array
    {
        $months = [];

        if ($request->date_mode === 'range' && $request->filled('start_date') && $request->filled('end_date')) {
            $current = new DateTime($request->start_date);
            $end = new DateTime($request->end_date);

            while ($current <= $end) {
                $months[] = (int) $current->format('n');
                $current->modify('+1 day');
            }
        } else {
            foreach ($request->input('specific_date', []) as $date) {
                if ($date) {
                    $months[] = (int) (new DateTime($date))->format('n');
                }
            }
        }

        return array_values(array_unique($months));
    }

    private function createDates(DocumentTraining $document, Request $request): void
    {
        if ($request->date_mode === 'range') {
            $current = new DateTime($request->start_date);
            $end = new DateTime($request->end_date);

            while ($current <= $end) {
                DocumentTrainingDate::create([
                    'document_training_id' => $document->id,
                    'date' => $current->format('Y-m-d'),
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                ]);
                $current->modify('+1 day');
            }

            return;
        }

        foreach ($request->specific_date as $index => $date) {
            if ($date) {
                DocumentTrainingDate::create([
                    'document_training_id' => $document->id,
                    'date' => $date,
                    'start_time' => $request->specific_start_time[$index],
                    'end_time' => $request->specific_end_time[$index],
                ]);
            }
        }
    }

    public function createProject(int|string $projectId): array
    {
        $project = DocumentTraining::find($projectId);
        $participants = $project->participants()->pluck('participant')->toArray();
        $lecturers = $project->mentors()->pluck('mentor')->filter()->values()->toArray();
        $dates = $project->dates()->get();

        $firstDate = $dates->first();
        $lastDate = $dates->last();

        $postData = [
            'document_id' => $project->id,
            'type' => $project->project_type ?: 'multiple',
            'title' => $project->title,
            'detail' => $project->detail,
            'project_start_register' => $firstDate->dateString.' '.$firstDate->start_time,
            'project_end_register' => $lastDate->dateString.' '.$lastDate->end_time,
            'dates' => $dates->map(fn ($d) => [
                'dateString' => $d->dateString,
                'start_time' => $d->start_time,
                'end_time' => $d->end_time,
            ])->toArray(),
            'users' => $participants,
        ];

        if ($lecturers !== []) {
            $postData['lecturers'] = $lecturers;
        }

        $response = $this->trainingApi->createProject($postData);

        if ($response->successful()) {
            $res = $response->json();
            $project->training_id = $res['project']['id'];
            $project->save();

            $project->tasks()->where('step', 2)->update([
                'status' => 'approve',
                'task_name' => 'ระหว่างการฝึกอบรม',
                'task_user' => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date' => date('Y-m-d H:i:s'),
            ]);

            $project->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'create_project',
                'details' => 'สร้างโครงการฝึกอบรม '.$project->title.' สำเร็จ!',
            ]);

            return [
                'status' => 'success',
                'message' => 'สร้างโปรเจกต์สำเร็จ!',
            ];
        }

        return [
            'status' => 'failed',
            'message' => 'สร้างโปรเจกต์ไม่สำเร็จ!',
        ];
    }

    public function getAttendance(int|string $projectId): ?array
    {
        $project = DocumentTraining::find($projectId);

        if (! $project) {
            return null;
        }

        $response = $this->trainingApi->getTransactions($project->training_id);

        if ($response->successful()) {
            return $response->json();
        }

        return [
            'status' => 'failed',
            'message' => 'ไม่พบข้อมูลการฝึกอบรม!',
        ];
    }

    public function approveAttendance(int|string $projectId, array $options = []): ?array
    {
        $project = DocumentTraining::find($projectId);

        if (! $project || $project->training_id === null) {
            return null;
        }

        $payload = $this->buildApprovePayload($project, $options);
        $response = $this->trainingApi->approveTransactions($payload);

        if (! $response->successful()) {
            $body = $response->json();

            return [
                'success' => false,
                'status' => 'failed',
                'message' => is_array($body) ? ($body['message'] ?? 'อนุมัติการเข้าร่วมไม่สำเร็จ!') : 'อนุมัติการเข้าร่วมไม่สำเร็จ!',
                'approved' => $body['approved'] ?? [],
                'skipped' => $body['skipped'] ?? [],
                'failed' => $body['failed'] ?? [],
            ];
        }

        $result = $response->json() ?? [];
        $approvedCount = count($result['approved'] ?? []);
        $failedCount = count($result['failed'] ?? []);

        $project->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'approve_attendance',
            'details' => $this->approveLogDetail($options, $approvedCount, $failedCount),
        ]);

        return array_merge([
            'success' => ($result['failed'] ?? []) === [],
            'message' => $result['message'] ?? 'อนุมัติการเข้าร่วมสำเร็จ!',
        ], $result);
    }

    /**
     * @param  array{transaction_id?: int|string|null, transaction_ids?: list<int|string>|null, approve_all?: bool, userid?: ?string}  $options
     * @return array<string, mixed>
     */
    private function buildApprovePayload(DocumentTraining $project, array $options): array
    {
        if (! empty($options['approve_all'])) {
            return [
                'project_id' => $project->training_id,
                'approve_all' => true,
            ];
        }

        $transactionIds = array_values(array_filter(
            array_map('intval', $options['transaction_ids'] ?? []),
            fn (int $id): bool => $id > 0,
        ));

        if ($transactionIds !== []) {
            return ['transaction_ids' => $transactionIds];
        }

        if (! empty($options['transaction_id'])) {
            return ['transaction_id' => (int) $options['transaction_id']];
        }

        return [
            'project_id' => $project->training_id,
            'approve_all' => true,
        ];
    }

    /**
     * @param  array{transaction_id?: int|string|null, transaction_ids?: list<int|string>|null, approve_all?: bool, userid?: ?string}  $options
     */
    private function approveLogDetail(array $options, int $approvedCount, int $failedCount): string
    {
        if (! empty($options['approve_all'])) {
            return "อนุมัติการเข้าร่วมทั้งโครงการ สำเร็จ {$approvedCount} รายการ".($failedCount > 0 ? " / ไม่สำเร็จ {$failedCount}" : '');
        }

        if (! empty($options['transaction_ids'])) {
            return 'อนุมัติการเข้าร่วมแบบกลุ่ม '.count($options['transaction_ids']).' รายการ (สำเร็จ '.$approvedCount.')';
        }

        $userid = $options['userid'] ?? $options['transaction_id'] ?? '-';

        return 'อนุมัติการเข้าร่วม '.$userid.' สำเร็จ!';
    }

    public function closeProject(int|string $projectId): ?array
    {
        $project = DocumentTraining::find($projectId);

        if (! $project) {
            return null;
        }

        $project->status = 'done';
        $project->save();

        $project->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'close_project',
            'details' => 'ปิดโครงการฝึกอบรม '.$project->title.' สำเร็จ!',
        ]);

        return [
            'status' => 'success',
            'message' => 'ปิดโครงการฝึกอบรมสำเร็จ!',
        ];
    }

    public function saveAssessment(int|string $projectId, array $assessments): void
    {
        foreach ($assessments as $participantId => $data) {
            DocumentTrainingParticipant::where('id', $participantId)
                ->where('document_training_id', $projectId)
                ->update([
                    'assetment_date' => $data['date'] ?: null,
                    'assetment_type' => $this->normalizeAssessmentType($data['type'] ?? null),
                    'score' => $data['score'] !== '' && $data['score'] !== null ? (string) $data['score'] : null,
                ]);
        }
    }

    /**
     * Persist assessment methods as a sorted unique string, e.g. "P+I".
     */
    private function normalizeAssessmentType(mixed $type): ?string
    {
        $values = is_array($type)
            ? $type
            : preg_split('/[+,|\\s]+/', (string) $type, -1, PREG_SPLIT_NO_EMPTY);

        $allowed = ['P', 'O', 'I'];
        $selected = [];

        foreach ($values as $value) {
            $code = strtoupper(trim((string) $value));
            if (in_array($code, $allowed, true) && ! in_array($code, $selected, true)) {
                $selected[] = $code;
            }
        }

        usort($selected, fn (string $a, string $b): int => array_search($a, $allowed, true) <=> array_search($b, $allowed, true));

        return $selected === [] ? null : implode('+', $selected);
    }

    public function downloadPdf(int|string $id): BinaryFileResponse|StreamedResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $project = DocumentTraining::with(['participants', 'mentors', 'creator'])->find($id);

        if (! $project) {
            return redirect()->back()->with('error', 'โปรเจกต์ไม่พบ!');
        }

        if ($project->status !== 'complete') {
            return response()->json(['message' => 'Project is not closed.'], 403);
        }

        $closeTask = $project->tasks()->where('step', 3)->first();
        $project_owner = $closeTask->user;
        $project_date = $closeTask->date;

        $pdf = Pdf::loadView('document.training.pdf', compact('project', 'project_owner', 'project_date'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Training_{$project->id}.pdf");
    }

    public function cancelProject(int|string $projectId): ?array
    {
        $project = DocumentTraining::find($projectId);

        if (! $project) {
            return null;
        }

        $project->status = 'cancel';
        $project->save();

        $project->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'cancel_project',
            'details' => 'ยกเลิกโครงการฝึกอบรม '.$project->title.' สำเร็จ!',
        ]);

        $project->tasks()->where('step', '>=', 2)->update([
            'status' => 'cancel',
            'task_name' => 'ยกเลิกโครงการฝึกอบรม',
            'task_user' => auth()->user()->userid,
            'task_position' => auth()->user()->position,
            'date' => date('Y-m-d H:i:s'),
        ]);

        if ($project->training_id != null) {
            $this->trainingApi->cancelProject($project->training_id);
        }

        return [
            'status' => 'success',
            'message' => 'ยกเลิกโครงการฝึกอบรมสำเร็จ!',
        ];
    }
}
