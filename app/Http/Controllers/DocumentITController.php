<?php
namespace App\Http\Controllers;

use App\Http\Controllers\HelperController;
use App\Models\DocumentHC;
use App\Models\DocumentIT;
use App\Models\DocumentNumber;
use App\Models\DocumentPAC;
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

        return redirect()->route('document.index')->with('success', 'Document created successfully!');
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
        $dataField                  = $request->all();
        $dataField['document_type'] = ($request->isHardware == 'true') ? 'it-hardware' : 'it';
        //Set Title
        $title = $request->title;
        if ($request->title == 'OTHER') {
            $title = $request->title_other_text;
        }
        if (str_contains($request->request_type_detail, 'อื่นๆ')) {
            $title .= ' ' . $request->request_type_detail . ' ' . $request->request_type_detail_other;
        }
        // Set Detail
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

        // Assuming $document is the fileable model
        $this->helper->createApprover($dataField, $document);
        $this->helper->createFile($request, $document);
        $log = [
            'action'  => 'create',
            'details' => 'สร้างเอกสาร IT',
        ];
        $this->helper->createLog($log, $document);
        if ($request->document_admin) {
            $log = [
                'action' => 'info',
                'detail' => 'มอบหมายงานไปยัง ' . $request->document_admin,
            ];
            $this->helper->createLog($log, $document);
        }
    }

    private function createDocumentPAC($request)
    {
        $dataField                  = $request->all();
        $dataField['document_type'] = 'pac';
        // Set Detail
        $title  = $request->title;
        $detail = $this->setUserFieldData($request->users, $title);

        $document                  = new DocumentPac();
        $document->requester       = auth()->user()->userid;
        $document->document_phone  = $request->document_phone;
        $document->document_number = DocumentNumber::getNextNumber('PAC');
        $document->title           = $request->title;
        $document->detail          = $detail;
        $document->save();

        // Assuming $document is the fileable model
        $this->helper->createApprover($dataField, $document);
        $this->helper->createFile($request, $document);
        $log = [
            'action'  => 'create',
            'details' => 'สร้างเอกสาร PACS',
        ];
        $this->helper->createLog($log, $document);
    }

    private function createDocumentHC($request)
    {
        $dataField                  = $request->all();
        $dataField['document_type'] = 'hc';

        $title  = $request->title;
        $detail = $this->setUserFieldData($request->users, $title);

        $document                  = new DocumentHc();
        $document->requester       = auth()->user()->userid;
        $document->document_phone  = $request->document_phone;
        $document->document_number = DocumentNumber::getNextNumber('HC');
        $document->title           = $title;
        $document->detail          = $detail;
        $document->save();

        $this->helper->createApprover($dataField, $document);
        $this->helper->createFile($request, $document);
        $log = [
            'action'  => 'create',
            'details' => 'สร้างเอกสาร HCLAB',
        ];
        $this->helper->createLog($log, $document);
    }
}
