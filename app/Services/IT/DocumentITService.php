<?php

namespace App\Services\IT;

use App\Models\DocumentBorrow;
use App\Models\DocumentHc;
use App\Models\DocumentHeartstream;
use App\Models\DocumentIT;
use App\Models\DocumentItUser;
use App\Models\DocumentNumber;
use App\Models\DocumentPac;
use App\Models\DocumentRegister;
use App\Models\DocumentUser;
use App\Services\DocumentWorkflowService;
use Illuminate\Http\Request;

class DocumentITService
{
    public function __construct(private DocumentWorkflowService $workflow) {}

    public function createDocument(Request $request): void
    {
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
    }

    private function setUserFieldData($users, string $title): string
    {
        $userField = '';

        foreach ($users as $user) {
            $userField .= 'รหัสพนักงาน: '.$user['userid'].'<br>';
            $userField .= 'ชื่อ-นามสกุล: '.$user['name'].' '.$user['name_en'].'<br>';
            $userField .= 'แผนก: '.$user['department'].'<br>';
            $userField .= 'ประเภท: '.$title.'<br>';
            $userField .= 'รายการที่ขอ: ';

            foreach ($user['request'] as $service => $value) {
                if ($value == 'true') {
                    if ($service == 'other') {
                        $userField .= $user['request']['other'].' ';
                    } else {
                        $userField .= $service.' ';
                    }
                }

                if ($service == 'detail') {
                    $userField .= '<br>รายละเอียด: '.$value.'<br>';
                }
            }
        }

        return $userField;
    }

    private function createDocumentIT(Request $request): void
    {
        $dataField = $request->all();
        $taskData = [
            'document_type' => ($request->isHardware == 'true') ? 'it-hardware' : 'it',
            'selfApprove' => ($request['selfApprove'] == 'true') ? true : false,
            'approver' => $request['approver'],
        ];

        $title = $request->title;
        if ($request->title == 'OTHER') {
            $title = $request->title_other_text;
        }

        if (str_contains($request->request_type_detail, 'อื่นๆ')) {
            $title .= '|'.$request->request_type_detail.' '.$request->request_type_detail_other;
        } else {
            $isEmpty = empty($request->request_type_detail);
            if (! $isEmpty) {
                $title .= '|'.$request->request_type_detail;
            }
        }

        $document = new DocumentIT;
        $document->requester = auth()->user()->userid;
        $document->document_phone = $request->document_phone;
        $document->document_number = DocumentNumber::getNextNumber($dataField['documentCode']);
        $document->type = $request->document_type;
        $document->title = $title;
        $document->detail = $request->support_detail;
        $document->assigned_user_id = ($request->document_admin) ? $request->document_admin : null;
        $document->save();

        $this->workflow->createApprover('it', $dataField, $document);
        $this->workflow->createTask($taskData, $document);
        $this->workflow->createFile($request, $document);

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'create',
            'details' => 'สร้างเอกสาร IT',
        ]);

        if ($request->document_admin) {
            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'info',
                'details' => 'มอบหมายงานไปยัง '.$request->document_admin,
            ]);
        }
    }

    private function createDocumentUser(Request $request): void
    {
        $title = $request->title;
        $approver = $request['approver'];

        $document = new DocumentUser;
        $document->requester = auth()->user()->userid;
        $document->document_phone = $request->document_phone;
        $document->title = $title;

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
            'approver' => $approver,
        ];

        $this->workflow->createApprover('user', $approverField, $document);
        $this->workflow->createFile($request, $document);

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

    private function createSubUserDocument(string $type, DocumentUser $documentUser, $approver): void
    {
        switch ($type) {
            case 'it':
                $document = new DocumentItUser;
                $NumberType = 'ITU';
                $logDetails = 'สร้างเอกสาร IT';
                break;
            case 'hc':
                $document = new DocumentHc;
                $NumberType = 'HC';
                $logDetails = 'สร้างเอกสาร HC';
                break;
            case 'pac':
                $document = new DocumentPac;
                $NumberType = 'PAC';
                $logDetails = 'สร้างเอกสาร PAC';
                break;
            case 'heart-steam':
                $document = new DocumentHeartstream;
                $NumberType = 'HS';
                $logDetails = 'สร้างเอกสาร Heart Stream';
                break;
            case 'register':
                $document = new DocumentRegister;
                $NumberType = 'REG';
                $logDetails = 'สร้างเอกสาร Register';
                break;
        }

        $document->document_user_id = $documentUser->id;
        $document->status = $approver['userid'] == auth()->user()->userid ? 'pending' : 'wait_approval';
        $document->document_number = DocumentNumber::getNextNumber($NumberType);
        $document->save();

        $taskData = [
            'document_type' => $type,
            'selfApprove' => false,
            'approver' => $approver,
        ];

        $this->workflow->createTask($taskData, $document);

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'create',
            'details' => $logDetails,
        ]);
    }

    private function createDocumentBorrow(Request $request): void
    {
        $dataField = $request->all();
        $taskData = [
            'document_type' => ($request->isHardware == 'true') ? 'it-borrow-hardware' : 'it-borrow',
            'selfApprove' => ($request['selfApprove'] == 'true') ? true : false,
            'approver' => $request['approver'],
        ];

        $document = new DocumentBorrow;
        $document->requester = auth()->user()->userid;
        $document->document_phone = $request->document_phone;
        $document->document_number = DocumentNumber::getNextNumber($dataField['documentCode']);
        $document->title = $request->borrow_type == 'OTHER' ? $request->borrow_other_text : $request->borrow_type;
        $document->detail = $request->borrow_detail;
        $document->estimate_return_date = $request->return_date;
        $document->save();

        $this->workflow->createApprover('it', $dataField, $document);
        $this->workflow->createTask($taskData, $document);
        $this->workflow->createFile($request, $document);

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'create',
            'details' => 'สร้างเอกสาร IT',
        ]);
    }
}
