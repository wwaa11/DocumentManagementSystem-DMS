<?php
namespace App\Http\Controllers;

use App\Http\Controllers\HelperController;
use App\Models\DocumentBorrow;
use App\Models\DocumentHc;
use App\Models\DocumentHeartstream;
use App\Models\DocumentIT;
use App\Models\DocumentItUser;
use App\Models\DocumentNumber;
use App\Models\DocumentPAC;
use App\Models\DocumentRegister;
use App\Models\DocumentUser;
use App\Models\File;
use App\Models\Hardware;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentITController extends Controller
{

    private $helper;

    public function __construct()
    {
        $this->helper = new HelperController();
    }

    // Create Document
    public function createDocument(Request $request)
    {
        // dd($request);
        // dump($request->all());
        // Dev bybass validation
        // $request->validate([
        //     'type'        => 'required|in:user,support',
        //     'requester'   => 'required|string',
        //     'title'       => 'required|string',
        //     'description' => 'required|string',
        // ]);

        switch ($request->main_document_type) {
            case 'user':
                $this->createDocumentUser($request);
                break;
            case 'support':
                $this->createDocumentIT($request);
                break;
            case 'borrow':
                $this->createDocumentBorrow($request);
                break;
            default:
                break;
        }
        // die();

        return redirect()->route('document.index')->with('success', 'สร้างเอกสารสำเร็จ!');
    }

    private function setUserFieldData($users, $title)
    {
        $userField = '';
        foreach ($users as $user) {
            $userField .= 'รหัสพนักงาน: ' . $user['userid'] . '<br>';
            $userField .= 'ชื่อ-นามสกุล: ' . $user['name'] . ' ' . $user['name_en'] . '<br>';
            $userField .= 'แผนก: ' . $user['department'] . '<br>';
            $userField .= 'ประเภท: ' . $title . '<br>';
            $userField .= 'รายการที่ขอ: ';
            foreach ($user['request'] as $service => $value) {
                if ($value == 'true') {
                    if ($service == 'other') {
                        $userField .= $user['request']['other'] . ' ';
                    } else {
                        $userField .= $service . ' ';
                    }
                }
                if ($service == 'detail') {
                    $userField .= '<br>รายละเอียด: ' . $value . '<br>';
                }

            }
        }
        return $userField;
    }

    private function createDocumentIT($request)
    {
        $dataField = $request->all();
        $taskData  = [
            'document_type' => ($request->isHardware == 'true') ? 'it-hardware' : 'it',
            'selfApprove'   => ($request['selfApprove'] == 'true') ? true : false,
            'approver'      => $request['approver'],
        ];

        $title = $request->title;
        if ($request->title == 'OTHER') {
            $title = $request->title_other_text;
        }
        // set Detail Title
        if (str_contains($request->request_type_detail, 'อื่นๆ')) {
            $title .= '|' . $request->request_type_detail . ' ' . $request->request_type_detail_other;
        } else {
            $isEmpty = empty($request->request_type_detail);
            if (! $isEmpty) {
                $title .= '|' . $request->request_type_detail;
            }
        }

        $document                   = new DocumentIT();
        $document->requester        = auth()->user()->userid;
        $document->document_phone   = $request->document_phone;
        $document->document_number  = DocumentNumber::getNextNumber($dataField['documentCode']);
        $document->type             = $request->document_type;
        $document->title            = $title;
        $document->detail           = $request->support_detail;
        $document->assigned_user_id = ($request->document_admin) ? $request->document_admin : null;
        $document->save();

        $this->helper->createApprover('it', $dataField, $document);
        $this->helper->createTask($taskData, $document);
        $this->helper->createFile($request, $document);

        // Log
        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'create',
            'details' => 'สร้างเอกสาร IT',
        ]);
        if ($request->document_admin) {
            $document->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'info',
                'details' => 'มอบหมายงานไปยัง ' . $request->document_admin,
            ]);
        }
    }

    private function createDocumentUser($request)
    {
        $dataField = $request->all();
        $title     = $request->title;
        $approver  = $request['approver'];

        $document                 = new DocumentUser();
        $document->requester      = auth()->user()->userid;
        $document->document_phone = $request->document_phone;
        $document->title          = $title;
        switch ($title) {
            case 'ขอแก้ไขสิทธิการใช้งาน':
                $detail = $this->setUserFieldData($request->users, $title);
                break;
            case 'เลขาแพทย์':
                $detail = $request->user_detail;
                break;
            case 'ฝ่ายบุคคล':
                $detail = $request->user_detail;
                break;
        }
        $document->detail = $detail;
        $document->save();

        $approverField = [
            'selfApprove' => $request['selfApprove'],
            'approver'    => $approver,
        ];
        $this->helper->createApprover('user', $approverField, $document);
        $this->helper->createFile($request, $document);

        if ($request->createIT == 'true') {
            $this->createSubUserDocument('it', $document, $approver);
        }
        if ($request->createHC == 'true') {
            $this->createSubUserDocument('hc', $document, $approver);
        }
        if ($request->createPAC == 'true') {
            $this->createSubUserDocument('pac', $document, $approver);
        }
        if ($request->createHeartStream == 'true') {
            $this->createSubUserDocument('heart-steam', $document, $approver);
        }
        if ($request->createRegister == 'true') {
            $this->createSubUserDocument('register', $document, $approver);
        }

    }

    private function createSubUserDocument($type, $documentUser, $approver)
    {
        switch ($type) {
            case 'it':
                $document   = new DocumentItUser();
                $NumberType = 'ITU';
                $logDetails = 'สร้างเอกสาร IT';
                break;
            case 'hc':
                $document   = new DocumentHc();
                $NumberType = 'HC';
                $logDetails = 'สร้างเอกสาร HC';
                break;
            case 'pac':
                $document   = new DocumentPac();
                $NumberType = 'PAC';
                $logDetails = 'สร้างเอกสาร PAC';
                break;
            case 'heart-steam':
                $document   = new DocumentHeartstream();
                $NumberType = 'HS';
                $logDetails = 'สร้างเอกสาร Heart Stream';
                break;
            case 'register':
                $document   = new DocumentRegister();
                $NumberType = 'REG';
                $logDetails = 'สร้างเอกสาร Register';
                break;
        }

        $document->document_user_id = $documentUser->id;
        $document->status           = $approver['userid'] == auth()->user()->userid ? 'pending' : 'wait_approval';
        $document->document_number  = DocumentNumber::getNextNumber($NumberType);
        $document->save();

        $taskData = [
            'document_type' => $type,
            'selfApprove'   => false,
            'approver'      => $approver,
        ];
        $this->helper->createTask($taskData, $document);

        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'create',
            'details' => $logDetails,
        ]);
    }

    private function createDocumentBorrow($request)
    {
        $dataField = $request->all();
        $taskData  = [
            'document_type' => ($request->isHardware == 'true') ? 'it-borrow-hardware' : 'it-borrow',
            'selfApprove'   => ($request['selfApprove'] == 'true') ? true : false,
            'approver'      => $request['approver'],
        ];

        $document                       = new DocumentBorrow;
        $document->requester            = auth()->user()->userid;
        $document->document_phone       = $request->document_phone;
        $document->document_number      = DocumentNumber::getNextNumber($dataField['documentCode']);
        $document->title                = $request->borrow_type == 'OTHER' ? $request->borrow_other_text : $request->borrow_type;
        $document->detail               = $request->borrow_detail;
        $document->estimate_return_date = $request->return_date;
        $document->save();

        $this->helper->createApprover('it', $dataField, $document);
        $this->helper->createTask($taskData, $document);
        $this->helper->createFile($request, $document);

        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'create',
            'details' => 'สร้างเอกสาร IT',
        ]);
    }

    // Count Document
    public function adminDocumentCount()
    {
        $documentListAll         = DocumentIT::whereIn('status', ['pending', 'process', 'done'])->get();
        $documentListNewHardware = $documentListAll->where('status', 'pending')->filter(function ($item) {
            return $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
        })->count();
        $documentListNew = $documentListAll->where('status', 'pending')->filter(function ($item) {
            return ! $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
        })->count();
        $documentListApprove = $documentListAll->where('status', 'done')->count();
        $documentListMy      = $documentListAll->where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->count();

        $documentITUserList    = DocumentItUser::whereIn('status', ['pending', 'process', 'done'])->get();
        $documentITUserListNew = $documentITUserList->where('status', 'pending')->filter(function ($item) {
            return ! $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
        })->count();
        $documentITUserListApprove = $documentITUserList->where('status', 'done')->count();
        $documentITUserListMy      = $documentITUserList->where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->count();

        $documentBorrowList         = DocumentBorrow::whereIn('status', ['pending', 'borrow_approve', 'return_approve', 'return'])->get();
        $documentListBorrowHardware = $documentBorrowList->where('status', 'pending')->filter(function ($item) {
            return $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
        })->count();
        $documentListBorrowNew     = $documentBorrowList->whereIn('status', ['pending', 'return_approve'])->count();
        $documentListBorrowApprove = $documentBorrowList->whereIn('status', ['borrow_approve', 'return'])->count();

        return response()->json([
            'it.hardware' => $documentListNewHardware + $documentListBorrowHardware,
            'it.approve'  => $documentListApprove + $documentITUserListApprove + $documentListBorrowApprove,
            'it.borrow'   => $documentListBorrowNew,
            'it.new'      => $documentListNew + $documentITUserListNew,
            'it.my'       => $documentListMy + $documentITUserListMy,
        ]);
    }
    // Admin Page List Document
    public function adminHardwareDocuments()
    {
        $documentListAll = DocumentIT::where('status', 'pending')->get();
        $documents       = $documentListAll->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
            return $task;
        });
        $documentBorrowList = DocumentBorrow::where('status', 'pending')->get();
        $documentsborrow    = $documentBorrowList->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
            return $task;
        });
        $documents = $documents->concat($documentsborrow)->sortBy('created_at');
        $action    = 'hardware';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function adminApproveDocuments()
    {
        $documents       = DocumentIT::where('status', 'done')->get();
        $documentsITUser = DocumentItUser::where('status', 'done')->get();
        $documentsBorrow = DocumentBorrow::whereIn('status', ['borrow_approve', 'return'])->get();
        $documents       = $documents->concat($documentsITUser)->concat($documentsBorrow)->sortBy('created_at');
        $action          = 'approve';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function adminNewDocuments()
    {
        $documentListAll = DocumentIT::where('status', 'pending')->get();
        $documents       = $documentListAll->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
            return ! $task;
        });
        $documentITUserListAll = DocumentItUser::where('status', 'pending')->get();
        $documentsITUser       = $documentITUserListAll->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
            return ! $task;
        });
        $documents = $documents->concat($documentsITUser)->sortBy('created_at');
        $action    = 'new';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function adminMyDocuments()
    {
        $documents       = DocumentIT::where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->get();
        $documentsITUser = DocumentItUser::where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->get();
        $documents       = $documents->concat($documentsITUser)->sortBy('created_at');
        $action          = 'my';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function adminAllDocuments(Request $request)
    {
        $documents       = DocumentIT::all();
        $documentsITUser = DocumentItUser::all();
        $documentsBorrow = DocumentBorrow::all();
        $documents       = $documents->concat($documentsITUser)->concat($documentsBorrow)->sortByDESC('created_at');

        $action    = 'all';
        $documents = $this->helper->paginateCollection($documents, 10, $request);

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function adminviewDocument($type, $document_id, $action)
    {
        if ($type == 'IT') {
            $document = DocumentIT::find($document_id);
        } elseif ($type == 'USER') {
            $document = DocumentItUser::find($document_id);
        } elseif ($type == 'BORROW') {
            $document = DocumentBorrow::find($document_id);
        }

        $userList = [];
        if ($action == 'my') {
            $userList = User::whereIn('role', ['admin', 'it', 'it-hardware', 'it-approver'])->get();
        }

        return view('admin.it.view', compact('document', 'action', 'userList', 'type'));
    }

    // Action Documents
    public function approveHardwareDocument(Request $request)
    {
        $request->validate([
            'id'     => 'required',
            'type'   => 'required',
            'status' => 'required|in:approve,reject',
            'reason' => 'required_if:status,reject',
        ]);
        $detail = ($request->status == 'approve') ? 'อนุมัติ' : 'ปฏิเสธ ' . $request->reason;

        switch ($request->type) {
            case 'IT':
                $document = DocumentIT::find($request->id);
                break;
            case 'BORROW':
                $document = DocumentBorrow::find($request->id);
                break;
        }

        $status           = ($request->status == 'approve') ? 'อนุมัติ' : 'ปฏิเสธ';
        $document->status = ($request->status == 'approve') ? 'pending' : 'reject';
        $document->save();

        $document->tasks()->where('step', 2)->update([
            'status'        => $request->status,
            'task_name'     => $status,
            'task_user'     => auth()->user()->userid,
            'task_position' => auth()->user()->position,
            'date'          => date('Y-m-d H:i:s'),
        ]);

        // Log
        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => $request->status,
            'details' => $detail,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => $detail,
        ]);
    }

    public function acceptDocument(Request $request)
    {
        $request->validate([
            'id'   => 'required',
            'type' => 'required|in:IT,USER',
        ]);

        if ($request->type == 'IT') {
            $document = DocumentIT::find($request->id);
        } else {
            $document = DocumentItUser::find($request->id);
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

        // Log
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

        if ($request->type == 'IT') {
            $document = DocumentIT::find($request->id);
        } else {
            $document = DocumentItUser::find($request->id);
        }

        $document->status = 'reject';
        $document->save();

        $document->tasks()->where('task_user', 'IT Department')->update([
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

        if ($request->type == 'IT') {
            $document = DocumentIT::find($request->id);
        } else {
            $document = DocumentItUser::find($request->id);
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
        if ($request->type == 'IT') {
            $document = DocumentIT::find($request->id);
        } else {
            $document = DocumentItUser::find($request->id);
        }

        if ($request->detail !== null) {
            $uploadedFiles = $request->file('document_files');
            if ($uploadedFiles) {
                foreach ($uploadedFiles as $file) {
                    $originalFilename = 'IT_' . $file->getClientOriginalName();
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
                return redirect()->route('admin.it.mylist')->with('error', 'กรุณากรอกรายละเอียดการดำเนินการ!');
            }
            $document->status           = 'done';
            $document->assigned_user_id = null;
            $document->save();
            $document->tasks()->where('task_user', 'IT Department')->update([
                'status'        => 'approve',
                'task_name'     => 'ดำเนินการเสร็จสิ้น',
                'task_user'     => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date'          => date('Y-m-d H:i:s'),
            ]);
        } elseif ($request->transfer_userid === 'new') {
            if ($request->detail === null) {
                return redirect()->route('admin.it.mylist')->with('error', 'กรุณากรอกรายละเอียดการดำเนินการ!');
            }

            $document->status           = 'pending';
            $document->assigned_user_id = null;
            $document->save();
            $document->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'work',
                'details' => 'ดำเนินการเสร็จสิ้น ส่งใบงานไปยังใบงานใหม่',
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

        return redirect()->route('admin.it.mylist')->with('success', 'ดำเนินการสำเร็จ!');
    }

    public function completeDocument(Request $request)
    {
        $request->validate([
            'id'     => 'required',
            'type'   => 'required',
            'status' => 'required|in:approve,reject',
        ]);

        if ($request->type == 'IT') {
            $document = DocumentIT::find($request->id);
        } elseif ($request->type == 'USER') {
            $document = DocumentItUser::find($request->id);
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => 'ไม่พบเอกสาร!',
            ]);
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
        $documents = DocumentIT::where('status', 'done')->get();
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
        $documentUser = DocumentItUser::where('status', 'done')->get();
        foreach ($documentUser as $document) {
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
        $documentsBorrow = DocumentBorrow::whereIn('status', ['borrow_approve', 'return'])->get();
        foreach ($documentsBorrow as $borrow) {
            switch ($borrow->status) {
                case 'borrow_approve':
                    $status = 'borrow';
                    $task   = 'รออนุมัติการยืม จากฝ่ายเทคโนโลยีสารสนเทศ';
                    $detail = 'อนุมัติการให้ยึมอุปกรณ์';
                    break;
                case 'return':
                    $status = 'complete';
                    $task   = 'คืนอุปกรณ์เรียบร้อย';
                    $detail = 'คืนอุปกรณ์เรียบร้อย';
                    break;
            }
            $borrow->status = $status;
            $borrow->save();

            $borrow->tasks()->where('task_name', $task)->update([
                'status'        => 'approve',
                'task_user'     => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date'          => date('Y-m-d H:i:s'),
            ]);

            $borrow->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'approve',
                'details' => $detail,
            ]);

        }

        return response()->json([
            'status'  => 'success',
            'message' => 'อนุมัติเอกสารเสร็จสิ้น!',
        ]);
    }

    // Borrow
    public function adminBorrowDocuments()
    {
        $documents = DocumentBorrow::whereIn('status', ['pending', 'borrow', 'return_approve'])->get();
        $documents = $documents->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
            return ! $task;
        });
        $action = 'borrow';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function adminBorrowAdd(Request $request)
    {
        $request->validate([
            'id'     => 'required',
            'date'   => 'required',
            'serial' => 'required',
        ]);

        $document = DocumentBorrow::find($request->id);
        $approver = $document->approvers()->first();
        $document->hardwares()->create([
            'serial_number' => $request->serial,
            'detail'        => $request->detail,
            'borrow_date'   => $request->date,
            'approver'      => $approver->userid,
        ]);

        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'add',
            'details' => 'ระบุอุปกรณ์ที่ให้ยืม : ' . $request->serial,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'เพิ่มอุปกรณ์สำเร็จ!',
        ]);
    }

    public function adminBorrowRemove(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $document = Hardware::find($request->id);
        $document->borrow_document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'remove',
            'details' => 'ลบอุปกรณ์ที่ให้ยืม : ' . $document->serial_number,
        ]);
        $document->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'ลบอุปกรณ์สำเร็จ!',
        ]);
    }

    public function adminBorrowSummary(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $document = DocumentBorrow::find($request->id);

        if (count($document->hardwares) == 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'ต้องมีอุปกรณ์ที่ยืมอย่างน้อย 1 อุปกรณ์!',
            ]);
        }

        $document->status = 'borrow_approve';
        $document->save();

        $document->tasks()->where('task_name', 'รอบันทึกรายละเอียดการยืม จากฝ่ายเทคโนโลยีสารสนเทศ')->update([
            'status'        => 'approve',
            'task_user'     => auth()->user()->userid,
            'task_position' => auth()->user()->position,
            'date'          => date('Y-m-d H:i:s'),
        ]);

        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'process',
            'details' => 'ขออนุมัติการให้ยึมอุปกรณ์',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'ขออนุมัติอุปกรณ์สำเร็จ!',
        ]);
    }

    public function adminBorrowApprove(Request $request)
    {
        $request->validate([
            'id'   => 'required',
            'type' => 'required|in:borrow,return',
        ]);

        switch ($request->type) {
            case 'borrow':
                $status = 'borrow';
                $task   = 'รออนุมัติการยืม จากฝ่ายเทคโนโลยีสารสนเทศ';
                $detail = 'อนุมัติการให้ยึมอุปกรณ์';
                break;
            case 'return':
                $status = 'complete';
                $task   = 'คืนอุปกรณ์เรียบร้อย';
                $detail = 'อนุมัติการคืนอุปกรณ์';
                break;
        }

        $document         = DocumentBorrow::find($request->id);
        $document->status = $status;
        $document->save();

        $document->tasks()->where('task_name', $task)->update([
            'status'        => 'approve',
            'task_user'     => auth()->user()->userid,
            'task_position' => auth()->user()->position,
            'date'          => date('Y-m-d H:i:s'),
        ]);

        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'approve',
            'details' => $detail,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'อนุมัติสำเร็จ!',
        ]);
    }

    public function borrowReturn(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $hardware = Hardware::find($request->id);
        $document = $hardware->borrow_document;

        $hardware->return_date = date('Y-m-d H:i');
        $hardware->save();

        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'retrun',
            'details' => 'ขอคืนอุปกรณ์ : ' . $hardware->serial_number,
        ]);

        if ($document->hardwares()->whereNull('return_date')->count() == 0) {
            $document->status = 'return_approve';
            $document->save();

            $document->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'retrun',
                'details' => 'ขอคืนอุปกรณ์ครบแล้ว',
            ]);

            $document->tasks()->where('task_name', 'รออนุมัติการคืน จากแผนกผู้ขอยืม')->update([
                'status'        => 'approve',
                'task_user'     => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date'          => date('Y-m-d H:i:s'),
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'รับอุปกรณ์สำเร็จ!',
        ]);
    }

    public function adminBorrowRetrieve(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $hardware = Hardware::find($request->id);
        $document = $hardware->borrow_document;

        $hardware->retrieve_date = date('Y-m-d H:i');
        $hardware->save();

        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'retrun',
            'details' => 'รับอุปกรณ์คืน : ' . $document->serial_number,
        ]);

        if ($document->hardwares()->whereNull('retrieve_date')->count() == 0) {
            $document->status = 'return';
            $document->save();

            $document->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'retreive',
                'details' => 'รับคืนอุปกรณ์ครบแล้ว',
            ]);

            $document->tasks()->where('task_name', 'รอบันทึกรายละเอียดการคืน จากฝ่ายเทคโนโลยีสารสนเทศ')->update([
                'status'        => 'approve',
                'task_user'     => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date'          => date('Y-m-d H:i:s'),
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'รับอุปกรณ์สำเร็จ!',
        ]);
    }

}
