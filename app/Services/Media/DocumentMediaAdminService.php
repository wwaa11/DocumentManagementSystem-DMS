<?php

namespace App\Services\Media;

use App\Models\DocumentMedia;
use App\Models\Log;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentMediaAdminService
{
    public function adminDocumentCount(): JsonResponse
    {
        $documents = DocumentMedia::query()
            ->whereIn('status', ['pending', 'done'])
            ->with('tasks')
            ->get();

        $documentQueue = $documents->where('status', 'pending')->filter(function (DocumentMedia $document) {
            return $this->hasWaitingTask($document, 'media');
        })->count();

        $documentApprove = $documents->where('status', 'done')->filter(function (DocumentMedia $document) {
            return $this->hasWaitingTask($document, 'media-head');
        })->count();

        return response()->json([
            'media.queue' => $documentQueue,
            'media.approve' => $documentApprove,
        ]);
    }

    public function adminApproveDocuments(): View
    {
        $documents = DocumentMedia::query()
            ->where('status', 'done')
            ->with(['creator', 'approvers.user', 'tasks'])
            ->get()
            ->filter(fn (DocumentMedia $document): bool => $this->hasWaitingTask($document, 'media-head'))
            ->values();
        $action = 'approve';

        return view('admin.media.list', compact('documents', 'action'));
    }

    public function adminNewDocuments(): View
    {
        $documents = DocumentMedia::query()
            ->where('status', 'pending')
            ->with(['creator', 'approvers.user', 'tasks'])
            ->get()
            ->filter(fn (DocumentMedia $document): bool => $this->hasWaitingTask($document, 'media'))
            ->values();
        $action = 'new';

        return view('admin.media.list', compact('documents', 'action'));
    }

    public function adminMyDocuments(): View
    {
        $documents = DocumentMedia::query()
            ->where('assigned_user_id', Auth::user()->userid)
            ->where('status', 'process')
            ->with(['creator', 'approvers.user', 'assigned_user'])
            ->orderByDesc('created_at')
            ->get();
        $action = 'my';

        return view('admin.media.list', compact('documents', 'action'));
    }

    public function adminQueueDocuments(): View
    {
        $documents = DocumentMedia::query()
            ->where('status', 'pending')
            ->with(['creator', 'approvers.user', 'tasks'])
            ->get()
            ->filter(fn (DocumentMedia $document): bool => $this->hasWaitingTask($document, 'media'))
            ->sortBy('required_date')
            ->values();
        $action = 'queue';

        return view('admin.media.list', compact('documents', 'action'));
    }

    public function adminAllDocuments(Request $request): View
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        $query = DocumentMedia::query()->with(['creator', 'approvers.user', 'assigned_user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('detail', 'LIKE', "%{$search}%");
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

        return view('admin.media.list', compact('documents', 'action', 'search', 'status', 'start_date', 'end_date'));
    }

    public function adminViewDocument(int|string $documentId, string $action): View
    {
        $document = DocumentMedia::query()
            ->with(['creator', 'files', 'tasks', 'logs', 'signItems', 'assigned_user'])
            ->findOrFail($documentId);

        $type = 'MEDIA';

        return view('admin.media.view', compact('document', 'action', 'type'));
    }

    public function acceptDocument(Request $request): JsonResponse
    {
        $document = DocumentMedia::query()->findOrFail($request->id);

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
        $document = DocumentMedia::query()->findOrFail($request->id);
        $document->status = 'reject';
        $document->save();

        $document->tasks()->where('task_user', 'media')->where('status', 'wait')->update([
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
        $document = DocumentMedia::query()->findOrFail($request->id);

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

    public function markFinish(Request $request): JsonResponse
    {
        $document = DocumentMedia::query()->findOrFail($request->id);

        if ($document->status !== 'pending' || ! $this->hasWaitingTask($document->load('tasks'), 'media')) {
            return response()->json([
                'status' => 'error',
                'message' => 'เอกสารนี้ไม่สามารถทำเครื่องหมายเสร็จสิ้นได้!',
            ]);
        }

        $document->tasks()->where('task_user', 'media')->where('status', 'wait')->update([
            'status' => 'approve',
            'task_name' => 'ดำเนินการเสร็จสิ้น',
            'task_user' => Auth::user()->userid,
            'task_position' => Auth::user()->position,
            'date' => date('Y-m-d H:i:s'),
        ]);

        $document->status = 'done';
        $document->assigned_user_id = Auth::user()->userid;
        $document->save();

        $document->logs()->create([
            'userid' => Auth::user()->userid,
            'action' => 'process',
            'details' => $request->input('detail', 'ทำเครื่องหมายเสร็จสิ้น'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ทำเครื่องหมายเสร็จสิ้นสำเร็จ!',
        ]);
    }

    public function processDocument(Request $request): RedirectResponse
    {
        $document = DocumentMedia::query()->findOrFail($request->id);
        $this->storeUploadedFiles($request, $document);

        $document->logs()->create([
            'userid' => Auth::user()->userid,
            'action' => 'process',
            'details' => $request->detail,
        ]);

        $assignedUserId = null;
        $status = null;

        if ($request->transfer_userid == null) {
            $status = 'done';
            $document->tasks()->where('task_user', 'media')->where('status', 'wait')->update([
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
            $assignedUserId = $request->transfer_userid;
            $document->logs()->create([
                'userid' => Auth::user()->userid,
                'action' => 'transfer',
                'details' => 'ส่งใบงานไปยัง '.$request->transfer_userid,
            ]);
        }

        $document->status = $status;
        $document->assigned_user_id = $assignedUserId;
        $document->save();

        return redirect()->route('admin.media.mylist')->with('success', 'ดำเนินการสำเร็จ!');
    }

    public function completeDocument(Request $request): JsonResponse
    {
        $document = DocumentMedia::query()->findOrFail($request->id);

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
            $document->assigned_user_id = null;
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

    public function completeAllDocument(): JsonResponse
    {
        $documents = DocumentMedia::query()
            ->where('status', 'done')
            ->with('tasks')
            ->get()
            ->filter(fn (DocumentMedia $document): bool => $this->hasWaitingTask($document, 'media-head'));

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

        $documents = DocumentMedia::query()
            ->with('creator')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->get();

        $keys = ['wait_approval', 'pending', 'process', 'done', 'complete', 'reject', 'total'];
        $allStats = array_fill_keys($keys, 0);
        $deptStats = [];

        foreach ($documents as $document) {
            $allStats['total']++;
            $dept = $document->creator->department ?? 'N/A';
            $deptStats[$dept] = ($deptStats[$dept] ?? 0) + 1;

            if (in_array($document->status, ['reject', 'cancel', 'not_approval'], true)) {
                $allStats['reject']++;
            } elseif (isset($allStats[$document->status])) {
                $allStats[$document->status]++;
            }
        }

        arsort($deptStats);

        $logs = Log::query()
            ->where('loggable_type', DocumentMedia::class)
            ->whereIn('action', ['accept', 'process', 'transfer', 'work'])
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->with('user')
            ->get();

        $adminStats = [];
        foreach ($logs as $log) {
            $admin = $log->user->name ?? $log->userid;
            if (! isset($adminStats[$admin])) {
                $adminStats[$admin] = ['take' => 0, 'close' => 0, 'transfer' => 0];
            }
            if ($log->action === 'accept') {
                $adminStats[$admin]['take']++;
            } elseif (in_array($log->action, ['process', 'work'], true)) {
                $adminStats[$admin]['close']++;
            } elseif ($log->action === 'transfer') {
                $adminStats[$admin]['transfer']++;
            }
        }

        return view('admin.media.report', compact('deptStats', 'adminStats', 'allStats', 'start_date', 'end_date'));
    }

    private function hasWaitingTask(DocumentMedia $document, string $taskUser): bool
    {
        return $document->tasks
            ->where('task_user', $taskUser)
            ->where('status', 'wait')
            ->isNotEmpty();
    }

    private function storeUploadedFiles(Request $request, DocumentMedia $document): void
    {
        $uploadedFiles = $request->file('document_files');
        if (! $uploadedFiles) {
            return;
        }

        foreach ($uploadedFiles as $file) {
            $document->files()->create([
                'original_filename' => 'MEDIA_'.$file->getClientOriginalName(),
                'stored_path' => $file->store('uploads', 'public'),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    private function approveCompletedDocument(DocumentMedia $document): void
    {
        $waitingTask = $document->tasks()
            ->where('task_user', 'media-head')
            ->where('status', 'wait')
            ->orderBy('step')
            ->first();

        if (! $waitingTask) {
            return;
        }

        $waitingTask->update([
            'status' => 'approve',
            'task_name' => 'อนุมัติเอกสารเสร็จสิ้น',
            'task_user' => Auth::user()->userid,
            'task_position' => Auth::user()->position,
            'date' => date('Y-m-d H:i:s'),
        ]);

        $document->status = 'complete';
        $document->save();

        $document->logs()->create([
            'userid' => Auth::user()->userid,
            'action' => 'complete',
            'details' => 'อนุมัติเอกสารเสร็จสิ้น',
        ]);
    }
}
