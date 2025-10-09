<?php
namespace App\Http\Controllers;

use App\Http\Controllers\HelperController;
use App\Models\DocumentHC;
use App\Models\DocumentIT;
use App\Models\DocumentNumber;
use App\Models\DocumentPAC;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentITController extends Controller
{

    private $helper;

    public function __construct()
    {
        $this->helper = new HelperController();
    }

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

        if ($request->createIT == 'true') {
            $this->createDocumentIT($request);
        }
        if ($request->createPAC == 'true') {
            $this->createDocumentPAC($request);
        }
        if ($request->createHC == 'true') {
            $this->createDocumentHC($request);
        }

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

    private function createDocumentIT(Request $request)
    {
        $dataField = $request->all();
        $taskData  = [
            'document_type' => ($request->isHardware == 'true') ? 'it-hardware' : 'it',
            'selfApprove'   => ($request['selfApprove'] == 'true') ? true : false,
            'approver'      => $request['approver'],
        ];

        $title = $request->title;
        if ($request->document_type == 'support') {
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
        }

        $detail = '';
        if ($request->document_type == 'support') {
            $detail = $request->support_detail;
        } else if ($request->document_type == 'user') {
            if ($request->title == 'ฝ่ายบุคคล' || $request->title == 'เลขาแพทย์') {
                $detail = $request->user_detail;
            } else {
                $detail = $this->setUserFieldData($request->users, $title);
            }
        }

        $document                   = new DocumentIT();
        $document->requester        = auth()->user()->userid;
        $document->document_phone   = $request->document_phone;
        $document->document_number  = DocumentNumber::getNextNumber($dataField['documentCode']);
        $document->type             = $request->document_type;
        $document->title            = $title;
        $document->detail           = $detail;
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

    private function createDocumentPAC($request)
    {
        $dataField = $request->all();
        $taskData  = [
            'document_type' => 'pac',
            'selfApprove'   => false,
            'approver'      => $request['approver'],
        ];
        $title = $request->title;
        if ($request->title == 'ฝ่ายบุคคล' || $request->title == 'เลขาแพทย์') {
            $detail = $request->user_detail;
        } else {
            $detail = $this->setUserFieldData($request->users, $title);
        }

        $document                  = new DocumentPac();
        $document->requester       = auth()->user()->userid;
        $document->document_phone  = $request->document_phone;
        $document->document_number = DocumentNumber::getNextNumber('PAC');
        $document->title           = $request->title;
        $document->detail          = $detail;
        $document->save();

        $this->helper->createApprover('pac', $dataField, $document);
        $this->helper->createTask($taskData, $document);
        $this->helper->createFile($request, $document);

        // Log
        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'create',
            'details' => 'สร้างเอกสาร PACS',
        ]);
    }

    private function createDocumentHC($request)
    {
        $dataField = $request->all();
        $taskData  = [
            'document_type' => 'hc',
            'selfApprove'   => false,
            'approver'      => $request['approver'],
        ];
        $title = $request->title;
        if ($request->title == 'ฝ่ายบุคคล' || $request->title == 'เลขาแพทย์') {
            $detail = $request->user_detail;
        } else {
            $detail = $this->setUserFieldData($request->users, $title);
        }

        $document                  = new DocumentHc();
        $document->requester       = auth()->user()->userid;
        $document->document_phone  = $request->document_phone;
        $document->document_number = DocumentNumber::getNextNumber('HC');
        $document->title           = $title;
        $document->detail          = $detail;
        $document->save();

        $this->helper->createApprover('hc', $dataField, $document);
        $this->helper->createTask($taskData, $document);
        $this->helper->createFile($request, $document);

        // Log
        $document->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'create',
            'details' => 'สร้างเอกสาร HCLAB',
        ]);
    }

    public function listDocumentCount()
    {
        $documentListAll         = DocumentIT::whereIn('status', ['pending', 'process', 'done'])->get();
        $documentListNewHardware = $documentListAll->where('status', 'pending')->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
            return $task;
        })->count();
        $documentListNew = $documentListAll->where('status', 'pending')->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
            return ! $task;
        })->count();
        $documentListApprove = $documentListAll->where('status', 'done')->count();
        $documentListMy      = $documentListAll->where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->count();

        return response()->json([
            'admin.it.hardwarelist' => $documentListNewHardware,
            'admin.it.approvelist'  => $documentListApprove,
            'admin.it.newlist'      => $documentListNew,
            'admin.it.mylist'       => $documentListMy,
        ]);
    }

    public function listHardwareDocuments()
    {
        $documentListAll = DocumentIT::where('status', 'pending')->get();
        $documents       = $documentListAll->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
            return $task;
        });
        $action = 'hardware';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function approveHardwareDocument(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:document_its,id',
            'status' => 'required|in:approve,reject',
            'reason' => 'required_if:status,reject',
        ]);
        $detail = ($request->status == 'approve') ? 'อนุมัติ' : 'ปฏิเสธ ' . $request->reason;

        $document         = DocumentIT::find($request->id);
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

    public function listNewDocuments()
    {
        $documentListAll = DocumentIT::where('status', 'pending')->get();
        $documents       = $documentListAll->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
            return ! $task;
        });
        $action = 'new';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function listMyDocuments()
    {
        $documents = DocumentIT::where('assigned_user_id', auth()->user()->userid)->where('status', 'process')->get();
        $action    = 'my';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function acceptDocument(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:document_its,id',
        ]);

        $document = DocumentIT::find($request->id);
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
            'id'     => 'required|exists:document_its,id',
            'reason' => 'required',
        ]);

        $document         = DocumentIT::find($request->id);
        $document->status = 'reject';
        $document->save();

        $document->tasks()->where('task_position', 'IT Department')->update([
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
            'id' => 'required|exists:document_its,id',
        ]);

        $document = DocumentIT::find($request->id);
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
        $document = DocumentIT::find($request->id);
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

    public function listApproveDocuments()
    {
        $documents = DocumentIT::where('status', 'done')->get();
        $action    = 'approve';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function listAllDocuments()
    {
        $documents = DocumentIT::orderByDesc('id')->get();
        $action    = 'all';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function viewDocument($document_id, $action)
    {
        $document = DocumentIT::find($document_id);
        $userList = [];
        if ($action == 'my') {
            $userList = User::whereIn('role', ['admin', 'it', 'it-hardware', 'it-approver'])->get();
        }

        return view('admin.it.view', compact('document', 'action', 'userList'));
    }

}
