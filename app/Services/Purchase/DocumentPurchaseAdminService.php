<?php

namespace App\Services\Purchase;

use App\Models\DocumentPurchase;
use App\Models\Log;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentPurchaseAdminService
{
    public function adminDocumentCount(): JsonResponse
    {
        $documents = DocumentPurchase::query()
            ->whereIn('status', ['pending', 'process', 'done'])
            ->with('tasks')
            ->get();

        $documentNew = $documents->where('status', 'pending')->filter(function (DocumentPurchase $document) {
            return $this->hasWaitingTask($document, 'purchase');
        })->count();

        $documentMy = $documents
            ->where('assigned_user_id', Auth::user()->userid)
            ->where('status', 'process')
            ->count();

        $documentApprove = $documents->where('status', 'done')->filter(function (DocumentPurchase $document) {
            return $this->hasWaitingTask($document, 'purchase-approve');
        })->count();

        $documentHead = $documents->where('status', 'done')->filter(function (DocumentPurchase $document) {
            return $this->hasWaitingTask($document, 'purchase-head');
        })->count();

        return response()->json([
            'purchase.new' => $documentNew,
            'purchase.my' => $documentMy,
            'purchase.approve' => $documentApprove,
            'purchase.head' => $documentHead,
        ]);
    }

    public function adminApproveDocuments(): View
    {
        $documents = DocumentPurchase::query()
            ->where('status', 'done')
            ->with(['creator', 'approvers.user', 'tasks'])
            ->get()
            ->filter(fn (DocumentPurchase $document): bool => $this->hasWaitingTask($document, 'purchase-approve'))
            ->values();
        $action = 'approve';

        return view('admin.purchase.list', compact('documents', 'action'));
    }

    public function adminHeadDocuments(): View
    {
        $documents = DocumentPurchase::query()
            ->where('status', 'done')
            ->with(['creator', 'approvers.user', 'tasks'])
            ->get()
            ->filter(fn (DocumentPurchase $document): bool => $this->hasWaitingTask($document, 'purchase-head'))
            ->values();
        $action = 'head';

        return view('admin.purchase.list', compact('documents', 'action'));
    }

    public function adminNewDocuments(): View
    {
        $documents = DocumentPurchase::query()
            ->where('status', 'pending')
            ->with(['creator', 'approvers.user', 'tasks'])
            ->get()
            ->filter(fn (DocumentPurchase $document): bool => $this->hasWaitingTask($document, 'purchase'))
            ->values();
        $action = 'new';

        return view('admin.purchase.list', compact('documents', 'action'));
    }

    public function adminMyDocuments(): View
    {
        $documents = DocumentPurchase::query()
            ->where('assigned_user_id', Auth::user()->userid)
            ->where('status', 'process')
            ->with(['creator', 'approvers.user', 'assigned_user'])
            ->orderBy('created_at')
            ->get();
        $action = 'my';

        return view('admin.purchase.list', compact('documents', 'action'));
    }

    public function adminAllDocuments(Request $request): View
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        $query = DocumentPurchase::query()->with(['creator', 'approvers.user', 'assigned_user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('detail', 'LIKE', "%{$search}%")
                    ->orWhere('po_number', 'LIKE', "%{$search}%");
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

        return view('admin.purchase.list', compact('documents', 'action', 'search', 'status', 'start_date', 'end_date'));
    }

    public function adminViewDocument(int|string $documentId, string $action): View
    {
        $document = DocumentPurchase::query()->findOrFail($documentId);
        $userList = [];

        if ($action === 'my') {
            $userList = User::query()->whereIn('role', ['admin', 'purchase'])->get();
        }

        $type = 'PURCHASE';

        return view('admin.purchase.view', compact('document', 'action', 'userList', 'type'));
    }

    public function acceptDocument(Request $request): JsonResponse
    {
        $document = DocumentPurchase::query()->findOrFail($request->id);

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
        $document = DocumentPurchase::query()->findOrFail($request->id);

        $document->status = 'reject';
        $document->save();

        $document->tasks()->where('task_user', 'purchase')->where('status', 'wait')->update([
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
        $document = DocumentPurchase::query()->findOrFail($request->id);

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
        $document = DocumentPurchase::query()->findOrFail($request->id);

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
            $document->tasks()->where('task_user', 'purchase')->where('status', 'wait')->update([
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

        return redirect()->route('admin.purchase.mylist')->with('success', 'ดำเนินการสำเร็จ!');
    }

    public function completeDocument(Request $request): JsonResponse
    {
        $document = DocumentPurchase::query()->findOrFail($request->id);

        if ($document->status !== 'done') {
            return response()->json([
                'status' => 'error',
                'message' => 'เอกสารนี้ไม่สามารถดำเนินการได้!',
            ]);
        }

        if ($request->status === 'approve') {
            $this->approveCompletedDocument($document, (string) $request->input('role', 'purchase-approve'));
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

    public function completeAllDocument(Request $request): JsonResponse
    {
        $role = (string) $request->input('role', 'purchase-approve');
        $documents = DocumentPurchase::query()
            ->where('status', 'done')
            ->with('tasks')
            ->get()
            ->filter(fn (DocumentPurchase $document): bool => $this->hasWaitingTask($document, $role));

        foreach ($documents as $document) {
            $this->approveCompletedDocument($document, $role);
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

        $documents = DocumentPurchase::query()
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
            ->where('loggable_type', DocumentPurchase::class)
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

        return view('admin.purchase.report', compact('deptStats', 'adminStats', 'allStats', 'start_date', 'end_date'));
    }

    private function hasWaitingTask(DocumentPurchase $document, string $taskUser): bool
    {
        return $document->tasks
            ->where('task_user', $taskUser)
            ->where('status', 'wait')
            ->isNotEmpty();
    }

    private function storeUploadedFiles(Request $request, DocumentPurchase $document): void
    {
        $uploadedFiles = $request->file('document_files');
        if (! $uploadedFiles) {
            return;
        }

        foreach ($uploadedFiles as $file) {
            $document->files()->create([
                'original_filename' => 'PURCHASE_'.$file->getClientOriginalName(),
                'stored_path' => $file->store('uploads', 'public'),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    private function approveCompletedDocument(DocumentPurchase $document, string $role): void
    {
        $waitingTask = $document->tasks()
            ->where('task_user', $role)
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

        $hasNextWaitingTask = $document->tasks()
            ->where('status', 'wait')
            ->where('step', '>', $waitingTask->step)
            ->exists();

        if ($hasNextWaitingTask) {
            $document->status = 'done';
        } else {
            $document->status = 'complete';
        }

        $document->save();

        $document->logs()->create([
            'userid' => Auth::user()->userid,
            'action' => 'complete',
            'details' => 'อนุมัติเอกสารเสร็จสิ้น',
        ]);
    }
}
