<?php
namespace App\Http\Controllers;

use App\Models\DocumentHc;
use App\Models\DocumentHeartstream;
use App\Models\DocumentPac;
use App\Models\DocumentRegister;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentUserController extends Controller
{
    public function adminDocumentCount($type)
    {
        switch ($type) {
            case 'pac':
                $documentAlls = DocumentPac::whereIn('status', ['pending', 'process', 'done'])->get();
                $task_user    = 'Xray';
                break;
            case 'lab':
                $documentAlls = DocumentHc::whereIn('status', ['pending', 'process', 'done'])->get();
                $task_user    = 'LAB';
                break;
            case 'heartstream':
                $documentAlls = DocumentHeartstream::whereIn('status', ['pending', 'process', 'done'])->get();
                $task_user    = 'HeartSteam';
                break;
            case 'register':
                $documentAlls = DocumentRegister::whereIn('status', ['pending', 'process', 'done'])->get();
                $task_user    = 'Registration';
                break;
        }

        $documentNew = $documentAlls->where('status', 'pending')->filter(function ($item) use ($task_user) {
            return $item->tasks()->where('step', 2)->where('task_user', $task_user)->first();
        })->count();
        $documentApprove = $documentAlls->where('status', 'done')->count();
        $documentMy      = $documentAlls->where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->count();

        return response()->json([
            $type . '.approve' => $documentApprove,
            $type . '.new'     => $documentNew,
            $type . '.my'      => $documentMy,
        ]);
    }

    public function adminApproveDocuments($type)
    {
        switch ($type) {
            case 'pac':
                $documents = DocumentPac::where('status', 'done')->get();
                break;
            case 'lab':
                $documents = DocumentHc::where('status', 'done')->get();
                break;
            case 'heartstream':
                $documents = DocumentHeartstream::where('status', 'done')->get();
                break;
            case 'register':
                $documents = DocumentRegister::where('status', 'done')->get();
                break;
        }

        $action = 'approve';

        return view('admin.user.list', compact('documents', 'action', 'type'));
    }

    public function adminNewDocuments($type)
    {

        switch ($type) {
            case 'pac':
                $documentListAll = DocumentPac::where('status', 'pending')->get();
                $task_user       = 'Xray';
                break;
            case 'lab':
                $documentListAll = DocumentHc::where('status', 'pending')->get();
                $task_user       = 'LAB';
                break;
            case 'heartstream':
                $documentListAll = DocumentHeartstream::where('status', 'pending')->get();
                $task_user       = 'HeartSteam';
                break;
            case 'register':
                $documentListAll = DocumentRegister::where('status', 'pending')->get();
                $task_user       = 'Registration';
                break;
        }

        $documents = $documentListAll->filter(function ($item) use ($task_user) {
            return $item->tasks()->where('step', 2)->where('task_user', $task_user)->first();
        });
        $action = 'new';

        return view('admin.user.list', compact('documents', 'action', 'type'));
    }

    public function adminMyDocuments($type)
    {
        switch ($type) {
            case 'pac':
                $documents = DocumentPac::where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->get();
                break;
            case 'lab':
                $documents = DocumentHc::where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->get();
                break;
            case 'heartstream':
                $documents = DocumentHeartstream::where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->get();
                break;
            case 'register':
                $documents = DocumentRegister::where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->get();
                break;
        }
        $action = 'my';

        return view('admin.user.list', compact('documents', 'action', 'type'));
    }

    public function adminAllDocuments(Request $request, $type)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        switch ($type) {
            case 'pac':
                $query = DocumentPac::query();
                break;
            case 'lab':
                $query = DocumentHc::query();
                break;
            case 'heartstream':
                $query = DocumentHeartstream::query();
                break;
            case 'register':
                $query = DocumentRegister::query();
                break;
        }

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

    public function viewDocument($type, $document_id, $action)
    {
        switch ($type) {
            case 'pac':
                $document = DocumentPac::find($document_id);
                break;
            case 'lab':
                $document = DocumentHc::find($document_id);
                break;
            case 'heartstream':
                $document = DocumentHeartstream::find($document_id);
                break;
            case 'register':
                $document = DocumentRegister::find($document_id);
                break;
        }

        $userList = [];
        if ($action == 'my') {
            $userList = User::whereIn('role', ['admin', $type])->get();
        }

        return view('admin.user.view', compact('document', 'action', 'userList', 'type'));
    }

    public function acceptDocument(Request $request)
    {
        $request->validate([
            'id'   => 'required',
            'type' => 'required',
        ]);

        switch ($request->type) {
            case 'pac':
                $document = DocumentPac::find($request->id);
                break;
            case 'lab':
                $document = DocumentHc::find($request->id);
                break;
            case 'heartstream':
                $document = DocumentHeartstream::find($request->id);
                break;
            case 'register':
                $document = DocumentRegister::find($request->id);
                break;
        }

        if ($document->assigned_user_id !== null) {
            return response()->json([
                'status'  => 'error',
                'message' => 'เอกสารนี้ได้ถูกรับงานแล้ว!',
            ]);
        }
        $document->status           = 'process';
        $document->assigned_user_id = auth()->user()->userid;
        $document->save();

        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'accept',
            'details' => 'รับงาน',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'รับงานสำเร็จ!',
        ]);
    }

    public function cancelDocument(Request $request)
    {
        $request->validate([
            'id'     => 'required',
            'type'   => 'required',
            'reason' => 'required',
        ]);

        switch ($request->type) {
            case 'pac':
                $document  = DocumentPac::find($request->id);
                $task_user = 'Xray';
                break;
            case 'lab':
                $document  = DocumentHc::find($request->id);
                $task_user = 'LAB';
                break;
            case 'heartstream':
                $document  = DocumentHeartstream::find($request->id);
                $task_user = 'HeartSteam';
                break;
            case 'register':
                $document  = DocumentRegister::find($request->id);
                $task_user = 'Registration';
                break;
        }

        $document->status = 'reject';
        $document->save();

        $document->tasks()->where('task_user', $task_user)->update([
            'status'        => 'reject',
            'task_name'     => 'ปฏิเสธ',
            'task_user'     => auth()->user()->userid,
            'task_position' => auth()->user()->position,
            'date'          => date('Y-m-d H:i:s'),
        ]);
        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'cancel',
            'details' => 'ยกเลิกเอกสาร : ' . $request->reason,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'ยกเลิกเอกสารสำเร็จ!',
        ]);
    }

    public function cancelJob(Request $request)
    {
        $request->validate([
            'id'   => 'required',
            'type' => 'required',
        ]);

        switch ($request->type) {
            case 'pac':
                $document = DocumentPac::find($request->id);
                break;
            case 'lab':
                $document = DocumentHc::find($request->id);
                break;
            case 'heartstream':
                $document = DocumentHeartstream::find($request->id);
                break;
            case 'register':
                $document = DocumentRegister::find($request->id);
                break;
        }

        if ($document->status !== 'process' || $document->assigned_user_id !== auth()->user()->userid) {
            return response()->json([
                'status'  => 'error',
                'message' => 'เอกสารนี้ไม่สามารถยกเลิกงานได้!',
            ]);
        }

        $document->status           = 'pending';
        $document->assigned_user_id = null;
        $document->save();

        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'transfer',
            'details' => 'ยกเลิกการรับงาน ส่งใบงานไปยังใบงานใหม่',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'ยกเลิกการรับงานสำเร็จ!',
        ]);
    }

    public function processDocument(Request $request)
    {
        $request->validate([
            'id'     => 'required',
            'type'   => 'required',
            'detail' => 'required',
        ]);

        switch ($request->type) {
            case 'pac':
                $document  = DocumentPac::find($request->id);
                $task_user = 'Xray';
                break;
            case 'lab':
                $document  = DocumentHc::find($request->id);
                $task_user = 'LAB';
                break;
            case 'heartstream':
                $document  = DocumentHeartstream::find($request->id);
                $task_user = 'HeartSteam';
                break;
            case 'register':
                $document  = DocumentRegister::find($request->id);
                $task_user = 'Registration';
                break;
        }

        $uploadedFiles = $request->file('document_files');
        if ($uploadedFiles) {
            foreach ($uploadedFiles as $file) {
                $originalFilename = $request->type . '_' . $file->getClientOriginalName();
                $mimeType         = $file->getMimeType();
                $size             = $file->getSize();
                $storedPath       = $file->store('uploads', 'public');

                $document->documentUser->files()->create([
                    'original_filename' => $originalFilename,
                    'stored_path'       => $storedPath,
                    'mime_type'         => $mimeType,
                    'size'              => $size,
                ]);
            }
        }

        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'process',
            'details' => $request->detail,
        ]);

        $assigned_user_id = null;
        $status           = null;

        if ($request->transfer_userid == null) {
            $status = 'done';
            $document->tasks()->where('task_user', $task_user)->update([
                'status'        => 'approve',
                'task_name'     => 'ดำเนินการเสร็จสิ้น',
                'task_user'     => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date'          => date('Y-m-d H:i:s'),
            ]);
        } elseif ($request->transfer_userid === 'new') {
            $status = 'pending';
            $document->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'work',
                'details' => 'ดำเนินการเสร็จสิ้น ส่งใบงานไปยังใบงานใหม่',
            ]);
        } else {
            $status           = 'process';
            $assigned_user_id = $request->transfer_userid;
            $document->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'transfer',
                'details' => 'ส่งใบงานไปยัง ' . $request->transfer_userid,
            ]);
        }

        $document->status           = $status;
        $document->assigned_user_id = $assigned_user_id;
        $document->save();

        return redirect()->route('admin.user.mylist', ['type' => $request->type])->with('success', 'ดำเนินการสำเร็จ!');
    }

    public function completeDocument(Request $request)
    {
        $request->validate([
            'id'     => 'required',
            'type'   => 'required',
            'status' => 'required|in:approve,reject',
        ]);

        switch ($request->type) {
            case 'pac':
                $document = DocumentPac::find($request->id);
                break;
            case 'lab':
                $document = DocumentHc::find($request->id);
                break;
            case 'heartstream':
                $document = DocumentHeartstream::find($request->id);
                break;
            case 'register':
                $document = DocumentRegister::find($request->id);
                break;
        }

        if ($document->status !== 'done') {
            return response()->json([
                'status'  => 'error',
                'message' => 'เอกสารนี้ไม่สามารถดำเนินการได้!',
            ]);
        }

        if ($request->status === 'approve') {
            $document->status = 'complete';
            $document->save();

            $document->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'complete',
                'details' => 'อนุมัติเอกสารเสร็จสิ้น',
            ]);

            $document->tasks()->orderBy('step', 'desc')->first()->update([
                'status'        => 'approve',
                'task_name'     => 'อนุมัติเอกสารเสร็จสิ้น',
                'task_user'     => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date'          => date('Y-m-d H:i:s'),
            ]);
        } else {
            $document->status = 'pending';
            $document->save();

            $document->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'reject',
                'details' => 'ไม่อนุมัติเอกสาร : ' . $request->reason,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'อนุมัติเอกสารเสร็จสิ้น!',
        ]);
    }

    public function completeAllDocument(Request $request)
    {

        switch ($request->type) {
            case 'pac':
                $documents = DocumentPac::where('status', 'done')->get();
                break;
            case 'lab':
                $documents = DocumentHc::where('status', 'done')->get();
                break;
            case 'heartstream':
                $documents = DocumentHeartstream::where('status', 'done')->get();
                break;
            case 'register':
                $documents = DocumentRegister::where('status', 'done')->get();
                break;
        }

        foreach ($documents as $document) {
            $document->status = 'complete';
            $document->save();

            $document->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'complete',
                'details' => 'อนุมัติเอกสารเสร็จสิ้น',
            ]);

            $document->tasks()->orderBy('step', 'desc')->first()->update([
                'status'        => 'approve',
                'task_name'     => 'อนุมัติเอกสารเสร็จสิ้น',
                'task_user'     => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date'          => date('Y-m-d H:i:s'),
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'อนุมัติเอกสารเสร็จสิ้น!',
        ]);
    }

    // Report
    public function adminReportDocuments(Request $request)
    {
        $start_date = $request->get('start_date', date('Y-m-01'));
        $end_date   = $request->get('end_date', date('Y-m-d'));
        $type       = $request->get('type'); // pac, lab, heartstream, register

        $queries = [
            'pac'         => DocumentPac::query(),
            'lab'         => DocumentHc::query(),
            'heartstream' => DocumentHeartstream::query(),
            'register'    => DocumentRegister::query(),
        ];

        // Filter by date and type
        foreach ($queries as $key => $query) {
            if ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            }
            if ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            }
        }

        // 1. Department Request Stats
        $deptStats = [];
        $allDocs   = [];

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

        // 2. Admin Action Stats
        $logsQuery = \App\Models\Log::whereIn('action', ['accept', 'process', 'transfer', 'work']);
        if ($start_date) {
            $logsQuery->whereDate('created_at', '>=', $start_date);
        }
        if ($end_date) {
            $logsQuery->whereDate('created_at', '<=', $end_date);
        }

        // Filter logs by morphable type if needed? 
        // Logs are morphMany, they have loggable_type.
        // If type is set, filter by that loggable_type.
        if ($type && $type !== 'ALL') {
            $modelMap = [
                'pac'         => DocumentPac::class,
                'lab'         => DocumentHc::class,
                'heartstream' => DocumentHeartstream::class,
                'register'    => DocumentRegister::class,
            ];
            if (isset($modelMap[$type])) {
                $logsQuery->where('loggable_type', $modelMap[$type]);
            }
        } else {
            $logsQuery->whereIn('loggable_type', [
                DocumentPac::class,
                DocumentHc::class,
                DocumentHeartstream::class,
                DocumentRegister::class,
            ]);
        }

        $logs = $logsQuery->with('user')->get();
        $adminStats = [];
        foreach ($logs as $log) {
            $admin = $log->user->name ?? $log->userid;
            if (!isset($adminStats[$admin])) {
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

        // 3. Document Stats by Status
        $keys = ['wait_approval', 'pending', 'process', 'done', 'complete', 'reject', 'total'];
        $statsByType = [];
        foreach (['pac', 'lab', 'heartstream', 'register'] as $k) {
            $statsByType[$k] = array_fill_keys($keys, 0);
        }

        foreach ($allDocs as $k => $docs) {
            foreach ($docs as $doc) {
                $statsByType[$k]['total']++;
                if ($doc->status == 'wait_approval') {
                    $statsByType[$k]['wait_approval']++;
                } elseif ($doc->status == 'pending') {
                    $statsByType[$k]['pending']++;
                } elseif ($doc->status == 'process') {
                    $statsByType[$k]['process']++;
                } elseif ($doc->status == 'done') {
                    $statsByType[$k]['done']++;
                } elseif ($doc->status == 'complete') {
                    $statsByType[$k]['complete']++;
                } elseif (in_array($doc->status, ['reject', 'cancel', 'not_approval'])) {
                    $statsByType[$k]['reject']++;
                }
            }
        }

        // Overall Stats (filtered by type if specified)
        $allStats = array_fill_keys($keys, 0);
        foreach ($statsByType as $k => $s) {
            if ($type && $type !== $k && $type !== 'ALL') continue;
            foreach ($keys as $key) {
                $allStats[$key] += $s[$key];
            }
        }

        return view('admin.user.report', compact('deptStats', 'adminStats', 'allStats', 'statsByType', 'start_date', 'end_date', 'type'));
    }
}
