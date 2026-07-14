<?php

namespace App\Services\User;

use App\Models\Log;
use App\Models\User;
use App\Services\DocumentTypeRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentUserAdminService
{
    public function __construct(private DocumentTypeRegistry $documentTypes) {}

    public function adminDocumentCount(string $type): JsonResponse
    {
        $documentAlls = $this->documentTypes->query($type)
            ->whereIn('status', ['pending', 'process', 'done'])
            ->get();
        $task_user = $this->documentTypes->taskUser($type);

        $documentNew = $documentAlls->where('status', 'pending')->filter(function (Model $item) use ($task_user) {
            return $item->tasks()->where('step', 2)->where('task_user', $task_user)->first();
        })->count();
        $documentApprove = $documentAlls->where('status', 'done')->count();
        $documentMy = $documentAlls->where('assigned_user_id', Auth::user()->userid)->where('status', 'process')->count();

        return response()->json([
            $type.'.approve' => $documentApprove,
            $type.'.new' => $documentNew,
            $type.'.my' => $documentMy,
        ]);
    }

    public function adminApproveDocuments(string $type): View
    {
        $documents = $this->documentTypes->query($type)->where('status', 'done')->get();
        $action = 'approve';

        return view('admin.user.list', compact('documents', 'action', 'type'));
    }

    public function adminNewDocuments(string $type): View
    {
        $task_user = $this->documentTypes->taskUser($type);
        $documentListAll = $this->documentTypes->query($type)->where('status', 'pending')->get();

        $documents = $documentListAll->filter(function (Model $item) use ($task_user) {
            return $item->tasks()->where('step', 2)->where('task_user', $task_user)->first();
        });
        $action = 'new';

        return view('admin.user.list', compact('documents', 'action', 'type'));
    }

    public function adminMyDocuments(string $type): View
    {
        $documents = $this->documentTypes->query($type)
            ->where('assigned_user_id', Auth::user()->userid)
            ->where('status', 'process')
            ->get();
        $action = 'my';

        return view('admin.user.list', compact('documents', 'action', 'type'));
    }

    public function adminAllDocuments(Request $request, string $type): View
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $query = $this->documentTypes->query($type);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('documentUser', function ($sq) use ($search) {
                        $sq->where('title', 'LIKE', "%{$search}%")
                            ->orWhere('detail', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($start_date) {
            $query->whereDate('created_at', '>=', $start_date);
        }

        if ($end_date) {
            $query->whereDate('created_at', '<=', $end_date);
        }

        $documents = $query->orderByDesc('id')->paginate(10);
        $action = 'all';

        return view('admin.user.list', compact('documents', 'action', 'type', 'search', 'status', 'start_date', 'end_date'));
    }

    public function viewDocument(string $type, mixed $document_id, string $action): View
    {
        $document = $this->documentTypes->find($type, $document_id);

        $userList = [];
        if ($action == 'my') {
            $userList = User::whereIn('role', ['admin', $type])->get();
        }

        return view('admin.user.view', compact('document', 'action', 'userList', 'type'));
    }

    public function acceptDocument(Request $request): JsonResponse
    {
        $document = $this->documentTypes->find($request->type, $request->id);

        if ($document->assigned_user_id !== null) {
            return response()->json([
                'status' => 'error',
                'message' => 'เอกสารนี้ได้ถูกรับงานแล้ว!',
            ]);
        }
        $document->status = 'process';
        $document->assigned_user_id = Auth::user()->userid;
        $document->save();

        $document->logs()->create([
            'userid' => Auth::user()->userid,
            'action' => 'accept',
            'details' => 'รับงาน',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'รับงานสำเร็จ!',
        ]);
    }

    public function cancelDocument(Request $request): JsonResponse
    {
        $document = $this->documentTypes->find($request->type, $request->id);
        $task_user = $this->documentTypes->taskUser($request->type);

        $document->status = 'reject';
        $document->save();

        $document->tasks()->where('task_user', $task_user)->update([
            'status' => 'reject',
            'task_name' => 'ปฏิเสธ',
            'task_user' => Auth::user()->userid,
            'task_position' => Auth::user()->position,
            'date' => date('Y-m-d H:i:s'),
        ]);
        $document->logs()->create([
            'userid' => Auth::user()->userid,
            'action' => 'cancel',
            'details' => 'ยกเลิกเอกสาร : '.$request->reason,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ยกเลิกเอกสารสำเร็จ!',
        ]);
    }

    public function cancelJob(Request $request): JsonResponse
    {
        $document = $this->documentTypes->find($request->type, $request->id);

        if ($document->status !== 'process' || $document->assigned_user_id !== Auth::user()->userid) {
            return response()->json([
                'status' => 'error',
                'message' => 'เอกสารนี้ไม่สามารถยกเลิกงานได้!',
            ]);
        }

        $document->status = 'pending';
        $document->assigned_user_id = null;
        $document->save();

        $document->logs()->create([
            'userid' => Auth::user()->userid,
            'action' => 'transfer',
            'details' => 'ยกเลิกการรับงาน ส่งใบงานไปยังใบงานใหม่',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ยกเลิกการรับงานสำเร็จ!',
        ]);
    }

    public function processDocument(Request $request): RedirectResponse
    {
        $document = $this->documentTypes->find($request->type, $request->id);
        $task_user = $this->documentTypes->taskUser($request->type);

        $this->storeUploadedFiles($request, $document);

        $document->logs()->create([
            'userid' => Auth::user()->userid,
            'action' => 'process',
            'details' => $request->detail,
        ]);

        $assigned_user_id = null;
        $status = null;

        if ($request->transfer_userid == null) {
            $status = 'done';
            $document->tasks()->where('task_user', $task_user)->update([
                'status' => 'approve',
                'task_name' => 'ดำเนินการเสร็จสิ้น',
                'task_user' => Auth::user()->userid,
                'task_position' => Auth::user()->position,
                'date' => date('Y-m-d H:i:s'),
            ]);
        } elseif ($request->transfer_userid === 'new') {
            $status = 'pending';
            $document->logs()->create([
                'userid' => Auth::user()->userid,
                'action' => 'work',
                'details' => 'ดำเนินการเสร็จสิ้น ส่งใบงานไปยังใบงานใหม่',
            ]);
        } else {
            $status = 'process';
            $assigned_user_id = $request->transfer_userid;
            $document->logs()->create([
                'userid' => Auth::user()->userid,
                'action' => 'transfer',
                'details' => 'ส่งใบงานไปยัง '.$request->transfer_userid,
            ]);
        }

        $document->status = $status;
        $document->assigned_user_id = $assigned_user_id;
        $document->save();

        return redirect()->route('admin.user.mylist', ['type' => $request->type])->with('success', 'ดำเนินการสำเร็จ!');
    }

    public function completeDocument(Request $request): JsonResponse
    {
        $document = $this->documentTypes->find($request->type, $request->id);

        if ($document->status !== 'done') {
            return response()->json([
                'status' => 'error',
                'message' => 'เอกสารนี้ไม่สามารถดำเนินการได้!',
            ]);
        }

        if ($request->status === 'approve') {
            $this->approveCompletedDocument($document);
        } else {
            $document->status = 'pending';
            $document->save();

            $document->logs()->create([
                'userid' => Auth::user()->userid,
                'action' => 'reject',
                'details' => 'ไม่อนุมัติเอกสาร : '.$request->reason,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'อนุมัติเอกสารเสร็จสิ้น!',
        ]);
    }

    public function completeAllDocument(Request $request): JsonResponse
    {
        $documents = $this->documentTypes->query($request->type)->where('status', 'done')->get();

        foreach ($documents as $document) {
            $this->approveCompletedDocument($document);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'อนุมัติเอกสารเสร็จสิ้น!',
        ]);
    }

    public function adminReportDocuments(Request $request): View
    {
        $start_date = $request->get('start_date', date('Y-m-01'));
        $end_date = $request->get('end_date', date('Y-m-d'));
        $type = $request->get('type');
        $queries = $this->reportQueries($start_date, $end_date);
        [$deptStats, $allDocs] = $this->departmentStats($queries, $type);
        $adminStats = $this->adminStats($start_date, $end_date, $type);
        $statsByType = $this->statsByType($allDocs);
        $keys = ['wait_approval', 'pending', 'process', 'done', 'complete', 'reject', 'total'];
        $allStats = array_fill_keys($keys, 0);

        foreach ($statsByType as $key => $stats) {
            if ($type && $type !== $key && $type !== 'ALL') {
                continue;
            }
            foreach ($keys as $statusKey) {
                $allStats[$statusKey] += $stats[$statusKey];
            }
        }

        return view('admin.user.report', compact('deptStats', 'adminStats', 'allStats', 'statsByType', 'start_date', 'end_date', 'type'));
    }

    private function storeUploadedFiles(Request $request, Model $document): void
    {
        $uploadedFiles = $request->file('document_files');
        if ($uploadedFiles) {
            foreach ($uploadedFiles as $file) {
                $originalFilename = $request->type.'_'.$file->getClientOriginalName();
                $mimeType = $file->getMimeType();
                $size = $file->getSize();
                $storedPath = $file->store('uploads', 'public');

                $document->documentUser->files()->create([
                    'original_filename' => $originalFilename,
                    'stored_path' => $storedPath,
                    'mime_type' => $mimeType,
                    'size' => $size,
                ]);
            }
        }
    }

    private function approveCompletedDocument(Model $document): void
    {
        $document->status = 'complete';
        $document->save();

        $document->logs()->create([
            'userid' => Auth::user()->userid,
            'action' => 'complete',
            'details' => 'อนุมัติเอกสารเสร็จสิ้น',
        ]);

        $document->tasks()->orderBy('step', 'desc')->first()->update([
            'status' => 'approve',
            'task_name' => 'อนุมัติเอกสารเสร็จสิ้น',
            'task_user' => Auth::user()->userid,
            'task_position' => Auth::user()->position,
            'date' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<string, \Illuminate\Database\Eloquent\Builder>
     */
    private function reportQueries(?string $start_date, ?string $end_date): array
    {
        $queries = [];

        foreach ($this->documentTypes->allTypes() as $type) {
            $query = $this->documentTypes->query($type);

            if ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            }
            if ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            }

            $queries[$type] = $query;
        }

        return $queries;
    }

    /**
     * @param  array<string, \Illuminate\Database\Eloquent\Builder>  $queries
     * @return array{0: array<string, int>, 1: array<string, \Illuminate\Support\Collection<int, Model>>}
     */
    private function departmentStats(array $queries, ?string $type): array
    {
        $deptStats = [];
        $allDocs = [];

        foreach ($queries as $key => $query) {
            if ($type && $type !== $key && $type !== 'ALL') {
                continue;
            }
            $docs = $query->with('documentUser.creator')->get();
            $allDocs[$key] = $docs;
            foreach ($docs as $doc) {
                $dept = $doc->documentUser->creator->department ?? 'N/A';
                $deptStats[$dept] = ($deptStats[$dept] ?? 0) + 1;
            }
        }
        arsort($deptStats);

        return [$deptStats, $allDocs];
    }

    /**
     * @return array<string, array{take: int, close: int, transfer: int}>
     */
    private function adminStats(?string $start_date, ?string $end_date, ?string $type): array
    {
        $logsQuery = Log::whereIn('action', ['accept', 'process', 'transfer', 'work']);

        if ($start_date) {
            $logsQuery->whereDate('created_at', '>=', $start_date);
        }
        if ($end_date) {
            $logsQuery->whereDate('created_at', '<=', $end_date);
        }

        if ($type && $type !== 'ALL') {
            if (in_array($type, $this->documentTypes->allTypes())) {
                $logsQuery->where('loggable_type', $this->documentTypes->modelClass($type));
            }
        } else {
            $logsQuery->whereIn('loggable_type', array_map(
                fn (string $documentType): string => $this->documentTypes->modelClass($documentType),
                $this->documentTypes->allTypes()
            ));
        }

        $logs = $logsQuery->with('user')->get();
        $adminStats = [];
        foreach ($logs as $log) {
            $admin = $log->user->name ?? $log->userid;
            if (! isset($adminStats[$admin])) {
                $adminStats[$admin] = ['take' => 0, 'close' => 0, 'transfer' => 0];
            }
            if ($log->action == 'accept') {
                $adminStats[$admin]['take']++;
            } elseif ($log->action == 'process' || $log->action == 'work') {
                $adminStats[$admin]['close']++;
            } elseif ($log->action == 'transfer') {
                $adminStats[$admin]['transfer']++;
            }
        }

        return $adminStats;
    }

    /**
     * @param  array<string, \Illuminate\Support\Collection<int, Model>>  $allDocs
     * @return array<string, array<string, int>>
     */
    private function statsByType(array $allDocs): array
    {
        $keys = ['wait_approval', 'pending', 'process', 'done', 'complete', 'reject', 'total'];
        $statsByType = [];
        foreach ($this->documentTypes->allTypes() as $type) {
            $statsByType[$type] = array_fill_keys($keys, 0);
        }

        foreach ($allDocs as $type => $docs) {
            foreach ($docs as $doc) {
                $statsByType[$type]['total']++;
                if ($doc->status == 'wait_approval') {
                    $statsByType[$type]['wait_approval']++;
                } elseif ($doc->status == 'pending') {
                    $statsByType[$type]['pending']++;
                } elseif ($doc->status == 'process') {
                    $statsByType[$type]['process']++;
                } elseif ($doc->status == 'done') {
                    $statsByType[$type]['done']++;
                } elseif ($doc->status == 'complete') {
                    $statsByType[$type]['complete']++;
                } elseif (in_array($doc->status, ['reject', 'cancel', 'not_approval'])) {
                    $statsByType[$type]['reject']++;
                }
            }
        }

        return $statsByType;
    }
}
