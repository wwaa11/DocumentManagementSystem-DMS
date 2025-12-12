<?php
namespace App\Http\Controllers;

use App\Models\DocumentPac;
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
        }

        $documentNew = $documentAlls->where('status', 'pending')->filter(function ($item) use ($task_user) {
            return $item->tasks()->where('step', 2)->where('task_user', 'Xray')->first();
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
        $documents = DocumentPac::where('status', 'done')->get();

        $action = 'approve';

        return view('admin.user.list', compact('documents', 'action', 'type'));
    }

    public function adminNewDocuments($type)
    {
        $documentListAll = DocumentPac::where('status', 'pending')->get();

        $documents = $documentListAll->filter(function ($item) {
            return $item->tasks()->where('step', 2)->where('task_user', 'Xray')->first();
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
        }
        $action = 'my';

        return view('admin.user.list', compact('documents', 'action', 'type'));
    }

    public function adminAllDocuments($type)
    {
        $documents = DocumentPac::orderByDesc('id')->paginate(10);

        $action = 'all';

        return view('admin.user.list', compact('documents', 'action', 'type'));
    }

    public function viewDocument($type, $document_id, $action)
    {
        $document = DocumentPac::find($document_id);

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
            'id'     => 'required|exists:document_pacs,id',
            'reason' => 'required',
        ]);

        $document         = DocumentPac::find($request->id);
        $document->status = 'reject';
        $document->save();

        $document->tasks()->where('task_position', 'Xray Department')->update([
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
            'id' => 'required|exists:document_pacs,id',
        ]);

        $document = DocumentPac::find($request->id);
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
        $document = DocumentPac::find($request->id);

        dd($request);

        if ($request->detail !== null) {
            $uploadedFiles = $request->file('document_files');
            if ($uploadedFiles) {
                foreach ($uploadedFiles as $file) {
                    $originalFilename = 'PAC_' . $file->getClientOriginalName();
                    $mimeType         = $file->getMimeType();
                    $size             = $file->getSize();
                    $storedPath       = $file->store('uploads', 'public');

                    $document->files()->create([
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
        }

        if ($request->transfer_userid == null) {
            if ($request->detail === null) {
                return redirect()->route('admin.pac.mylist')->with('error', 'กรุณากรอกรายละเอียดการดำเนินการ!');
            }
            $document->status           = 'done';
            $document->assigned_user_id = null;
            $document->save();
            $document->tasks()->where('task_user', 'Xray Department')->update([
                'status'        => 'approve',
                'task_name'     => 'ดำเนินการเสร็จสิ้น',
                'task_user'     => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date'          => date('Y-m-d H:i:s'),
            ]);
        } else {
            $document->status           = 'process';
            $document->assigned_user_id = $request->transfer_userid;
            $document->save();
            $document->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'transfer',
                'details' => 'ส่งใบงานไปยัง ' . $request->transfer_userid,
            ]);
        }

        return redirect()->route('admin.pac.mylist')->with('success', 'ดำเนินการสำเร็จ!');
    }

}
