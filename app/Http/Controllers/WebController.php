<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\File;
use App\Models\User;
use App\Services\Course\CoursePlanService;
use App\Services\DocumentResolver;
use App\Services\DocumentWorkflowService;
use App\Services\StaffApiClient;
use App\Services\Training\DocumentTrainingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WebController extends Controller
{
    public function __construct(
        private DocumentWorkflowService $workflow,
        private DocumentResolver $documentResolver,
        private StaffApiClient $staffApi,
        private DocumentTrainingService $documentTrainingService,
        private CoursePlanService $coursePlanService,
    ) {
        mb_internal_encoding('UTF-8');
    }

    public function fileShow(File $file): StreamedResponse|BinaryFileResponse|Response
    {
        $path = $file->stored_path;

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response(
            $path,
            $file->original_filename,
            [
                'Content-Type' => $file->resolvedMimeType(),
            ],
            'inline'
        );
    }

    public function fileDownload(File $file): StreamedResponse|BinaryFileResponse|Response
    {
        $path = $file->stored_path;

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path, $file->original_filename);
    }

    public function myDocument(Request $request): View
    {
        $my_documents = auth()->user()->getMyDocuments();
        $approveDocuments = auth()->user()->getApproveDocument();
        $documents = [];

        foreach ($approveDocuments as $item) {
            $documentData = $item->document;
            if (! $documentData) {
                continue;
            }

            $flag = $item->status == 'wait' ? 'approve' : 'my';
            $tag = $documentData->document_tag['document_tag'] ?? '';

            // Training documents appear only while waiting for this user's approval.
            if ($tag === 'Training' && $flag !== 'approve') {
                continue;
            }

            $document_id = $tag.$documentData->id;
            $detail = strlen($documentData->detail) > 100 ? mb_substr($documentData->detail, 0, 100).'...' : $documentData->detail;
            $documents[$document_id] = [
                'flag' => $flag,
                'id' => $documentData->id,
                'document_tag' => $documentData->document_tag,
                'document_number' => $documentData->document_number,
                'document_type_name' => $documentData->document_type_name,
                'title' => $documentData->title,
                'detail' => $detail,
                'status' => $documentData->status,
                'created_at' => $documentData->created_at,
            ];
        }

        foreach ($my_documents as $item) {
            $document_id = $item->document_tag['document_tag'].$item->id;
            if (! isset($documents[$document_id])) {
                $detail = strlen($item->detail) > 100 ? mb_substr($item->detail, 0, 100).'...' : $item->detail;
                $documents[$document_id] = [
                    'flag' => 'my',
                    'id' => $item->id,
                    'document_tag' => $item->document_tag,
                    'document_number' => $item->document_number,
                    'document_type_name' => $item->document_type_name,
                    'title' => $item->title,
                    'detail' => $detail,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                ];
            }
        }

        foreach ($this->coursePlanService->pendingApprovalsFor(auth()->user()) as $plan) {
            $documents['COURSE'.$plan->id] = [
                'flag' => 'approve',
                'id' => $plan->id,
                'document_tag' => $plan->document_tag,
                'document_number' => $plan->document_number,
                'document_type_name' => $plan->document_type_name,
                'title' => $plan->title,
                'detail' => $plan->detail,
                'status' => $plan->status,
                'created_at' => $plan->created_at,
                'approve_url' => route('document.course.show', $plan),
                'view_url' => route('document.course.show', $plan),
            ];
        }

        $documents = collect($documents)->filter(function ($document) use ($request) {
            $documentNumber = $request->input('document_number');
            $detail = $request->input('detail');
            $document_tag = $request->input('document_tag');
            $status = $request->input('status');
            $createdAtStart = $request->input('created_at_start');
            $createdAtEnd = $request->input('created_at_end');

            if ($documentNumber && ! str_contains(strtolower($document['document_number'] ?? ''), strtolower($documentNumber))) {
                return false;
            }
            if ($detail && ! str_contains(strtolower($document['detail']), strtolower($detail))) {
                return false;
            }
            if ($document_tag && ! str_contains(strtolower($document['document_tag']['document_tag']), strtolower($document_tag))) {
                return false;
            }
            if ($status && $document['status'] !== $status) {
                return false;
            }
            if ($createdAtStart && strtotime($document['created_at']) < strtotime($createdAtStart)) {
                return false;
            }
            if ($createdAtEnd && strtotime($document['created_at']) > strtotime($createdAtEnd.' 23:59:59')) {
                return false;
            }

            return true;
        });

        $flag = $request->input('flag');
        $pendingApprovals = $documents
            ->where('flag', 'approve')
            ->sortByDesc('created_at')
            ->values();
        $otherDocuments = $documents
            ->where('flag', '!=', 'approve')
            ->sortByDesc('created_at')
            ->values();

        if ($flag === 'approve') {
            $otherDocuments = collect();
        } elseif ($flag === 'my') {
            $pendingApprovals = collect();
            $otherDocuments = $otherDocuments->where('flag', 'my')->values();
        }

        $paginatedDocuments = $this->workflow->paginateCollection($otherDocuments->all(), 10, $request);

        return view('documnet_index', [
            'documents' => $paginatedDocuments,
            'pendingApprovals' => $pendingApprovals,
        ]);
    }

    public function createDocument(): View
    {
        $document = Document::query()->orderBy('id')->get();

        return view('document_create', compact('document'));
    }

    public function createDocumentByType(Request $request, string $document_type): View|RedirectResponse
    {
        $data = [];

        switch ($document_type) {
            case 'it':
                $view = 'document.it.create';
                $it_admins = User::query()
                    ->whereIn('role', ['admin', 'it', 'it-hardware', 'it-approve', 'it-hardware-approve'])
                    ->orderBy('department')
                    ->orderBy('name')
                    ->get()
                    ->groupBy(
                        fn (User $user): string => filled($user->department) ? $user->department : 'ไม่ระบุแผนก'
                    );
                $data = compact('it_admins');
                break;
            case 'media':
                $view = 'document.media.create';
                break;
            case 'purchase':
                $view = 'document.purchase.create';
                break;
            case 'cqi':
                $view = 'document.cqi.create';
                break;
            case 'training':
                $view = 'document.training.create';
                $deptResponse = $this->staffApi->getDepartments();
                $departments = [];
                if ($deptResponse->successful()) {
                    foreach ($deptResponse->json()['departments'] as $value) {
                        $departments[] = $value['department'];
                    }
                }

                $coursePlanItem = null;
                $coursePlanItemId = $request->integer('course_plan_item_id') ?: null;

                if ($coursePlanItemId) {
                    $coursePlanItem = \App\Models\CoursePlanItem::query()
                        ->with(['coursePlan', 'instructors', 'training'])
                        ->find($coursePlanItemId);

                    if (! $coursePlanItem) {
                        return redirect()
                            ->route('document.course')
                            ->with('error', 'ไม่พบหลักสูตรในแผนประจำปี');
                    }

                    $plan = $coursePlanItem->coursePlan;
                    $user = auth()->user();

                    if (! $user->canCreateCourseForDepartment($plan->department)) {
                        return redirect()
                            ->route('document.course.show', $plan)
                            ->with('error', 'คุณไม่มีสิทธิ์สร้างฝึกอบรมจากหลักสูตรนี้');
                    }

                    if ($coursePlanItem->hasTrainingDocument()) {
                        return redirect()
                            ->route('document.type.view', [
                                'document_type' => 'Training',
                                'document_id' => $coursePlanItem->training->id,
                            ])
                            ->with('error', 'หลักสูตรนี้มีใบบันทึกฝึกอบรมแล้ว');
                    }
                }

                $substituteCourses = $this->coursePlanService->courseItemsForTraining(auth()->user());
                $courseDepartments = auth()->user()->courseDepartments();
                $defaultCourseDepartment = $request->string('department')->toString();

                if ($defaultCourseDepartment === '' && in_array(auth()->user()->department, $courseDepartments, true)) {
                    $defaultCourseDepartment = auth()->user()->department;
                }

                if ($defaultCourseDepartment === '') {
                    $defaultCourseDepartment = $courseDepartments[0] ?? '';
                }

                $defaultCourseYear = $request->integer('year') ?: (int) now()->year;
                $data = compact(
                    'departments',
                    'coursePlanItem',
                    'substituteCourses',
                    'courseDepartments',
                    'defaultCourseDepartment',
                    'defaultCourseYear',
                );
                break;
            default:
                return redirect()->route('document.create');
        }

        return view($view, $data);
    }

    public function userSearch(Request $request): array|JsonResponse
    {
        $response = $this->staffApi->getUser((string) $request->input('userid'))->json();

        if (! isset($response['status']) || $response['status'] != 1) {
            return ['status' => false];
        }

        return response()->json(['status' => true, 'user' => $response['user']]);
    }

    public function userPosition(Request $request): array|JsonResponse
    {
        $response = $this->staffApi->getDepartmentPositions((string) $request->input('department'))->json();

        if (! isset($response['status']) || $response['status'] != 1) {
            return ['status' => false];
        }

        $positions = [];
        foreach ($response['positions'] as $value) {
            if (isset($value['position'])) {
                $positions[] = $value['position'];
            }
        }

        return response()->json(['status' => true, 'positions' => $positions]);
    }

    public function getuserFormDepartment(Request $request): JsonResponse
    {
        $response = $this->staffApi->getDepartmentUsers((string) $request->input('department'))->json();

        if (! isset($response['status']) || $response['status'] != 1) {
            return response()->json(['status' => 0, 'messgae' => 'error', 'users' => []]);
        }

        $users = [];
        foreach ($response['users'] as $value) {
            $users[] = [
                'userid' => $value['userid'],
                'name' => $value['name'],
                'position' => $value['position'],
            ];
        }

        return response()->json([
            'status' => 1,
            'messgae' => 'success',
            'users' => $users,
        ]);
    }

    public function getuserFormDepartmentPosition(Request $request): JsonResponse
    {
        $response = $this->staffApi->getDepartmentUsersByPosition(
            (string) $request->input('department'),
            (string) $request->input('position'),
        )->json();

        if (! isset($response['status']) || $response['status'] != 1) {
            return response()->json(['status' => 0, 'messgae' => 'error', 'users' => []]);
        }

        $users = [];
        foreach ($response['users'] as $value) {
            $users[] = [
                'userid' => $value['userid'],
                'name' => $value['name'],
                'position' => $value['position'],
            ];
        }

        return response()->json([
            'status' => 1,
            'messgae' => 'success',
            'users' => $users,
        ]);
    }

    public function viewDocument(string $type, int|string $document_id): View|RedirectResponse
    {
        $document = $this->documentResolver->resolve($type, $document_id);
        if (! $document) {
            return redirect()->route('document.index')->with('error', 'ไม่พบประเภทเอกสาร');
        }

        return view('document.view', compact('document', 'type'));
    }

    public function cancelDocument(Request $request, string $document_type, int|string $document_id): JsonResponse|RedirectResponse
    {
        $document = $this->documentResolver->resolve($document_type, $document_id);
        if (! $document) {
            return redirect()->route('document.index')->with('error', 'ไม่พบประเภทเอกสาร');
        }

        if ($request->type == 'USER') {
            $document->approvers()->update(['status' => 'cancel']);

            foreach ($document->getAllDocuments() as $subDocument) {
                $subDocument->status = 'cancel';
                $subDocument->save();
                $subDocument->tasks()->update(['status' => 'cancel', 'task_name' => 'ยกเลิกเอกสาร']);
                $subDocument->logs()->create([
                    'userid' => auth()->user()->userid,
                    'action' => 'cancel',
                    'details' => 'ยกเลิกเอกสาร '.$request->input('reason'),
                ]);
            }
        } else {
            $document->status = 'cancel';
            $document->save();
            $document->approvers()->update(['status' => 'cancel']);

            $document->tasks()->update(['status' => 'cancel', 'task_name' => 'ยกเลิกเอกสาร']);
            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'cancel',
                'details' => 'ยกเลิกเอกสาร '.$request->input('reason'),
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function approveDocument(string $type, int|string $document_id): View|RedirectResponse
    {
        $document = $this->documentResolver->resolve($type, $document_id);
        if (! $document) {
            return redirect()->route('document.index')->with('error', 'ไม่พบประเภทเอกสาร');
        }

        $approveList = $document->approvers()->where('userid', auth()->user()->userid)->where('status', 'wait')->first();
        if (! $approveList) {
            return redirect()->route('document.index')->with('error', 'ไม่มีสิทธิ์อนุมัติเอกสารนี้');
        }

        if ($approveList->step > 1) {
            $previousStep = $document->approvers()->where('step', $approveList->step - 1)->first();
            if (! $previousStep || $previousStep->status !== 'approve') {
                return redirect()->route('document.index')->with('error', 'ผู้อนุมัติขั้นก่อนหน้า สำหรับเอกสารนี้ยังไม่ถูกอนุมัติ');
            }
        }

        return view('document.approve', compact('document', 'type'));
    }

    public function approveDocumentRequest(Request $request, string $document_type, int|string $document_id): JsonResponse|RedirectResponse
    {
        $document = $this->documentResolver->resolve($document_type, $document_id);
        if (! $document) {
            return redirect()->route('document.index')->with('error', 'ไม่พบประเภทเอกสาร');
        }

        if ($document_type == 'USER') {
            $approveList = $document->approvers()->where('userid', auth()->user()->userid)->where('status', 'wait')->first();
            $approveList->status = $request->status;
            $approveList->save();

            foreach ($document->getAllDocuments() as $subDocument) {
                if ($request->status == 'approve') {
                    $status_change = filled($subDocument->assigned_user_id) ? 'process' : 'pending';
                } elseif ($request->status == 'reject') {
                    $status_change = 'not_approval';
                }
                $subDocument->status = $status_change;
                $subDocument->save();

                $subDocument->tasks()->where('step', $approveList->step)->update([
                    'status' => $request->status,
                    'task_name' => $request->status == 'approve' ? 'อนุมัติ' : 'ไม่อนุมัติ',
                    'task_user' => auth()->user()->userid,
                    'task_position' => auth()->user()->position,
                    'date' => date('Y-m-d H:i:s'),
                ]);

                $subDocument->logs()->create([
                    'userid' => auth()->user()->userid,
                    'action' => $request->status,
                    'details' => $request->status == 'approve' ? 'อนุมัติเอกสาร' : $request->reason,
                ]);
            }
        } else {
            $approveList = $document->approvers()->where('userid', auth()->user()->userid)->where('status', 'wait')->first();
            $approveList->status = $request->status;
            $approveList->save();

            $checkNextStep = $document->approvers()->where('step', $approveList->step + 1)->first();
            if ($request->status == 'approve' && ! $checkNextStep) {
                if ($document->assigned_user_id != null) {
                    $status_change = 'process';
                } else {
                    $status_change = 'pending';
                }

                if ($document_type == 'Training') {
                    $this->documentTrainingService->createProject($document->id);
                }
            } elseif ($request->status == 'reject') {
                $status_change = 'not_approval';
            }
            $document->status = $status_change;
            $document->save();

            $document->tasks()->where('step', $approveList->step)->update([
                'status' => $request->status,
                'task_name' => $request->status == 'approve' ? 'อนุมัติ' : 'ไม่อนุมัติ',
                'task_user' => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date' => date('Y-m-d H:i:s'),
            ]);

            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => $request->status,
                'details' => $request->status == 'approve' ? 'อนุมัติเอกสาร' : $request->reason,
            ]);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }
}
