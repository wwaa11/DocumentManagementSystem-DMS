<?php
namespace App\Http\Controllers;

use App\Http\Controllers\HelperController;
use App\Models\DocumentIT;
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
        dump($request);
        // Dev bybass validation
        // $request->validate([
        //     'type'        => 'required|in:user,support',
        //     'requester'   => 'required|string',
        //     'title'       => 'required|string',
        //     'description' => 'required|string',
        // ]);

        $createIT  = false;
        $createPAC = false;
        $createHC  = false;
        if ($request->document_type == 'user') {
            $checkTitle = ['ขอเพิ่ม', 'ขอลด', 'ขอแก้ไข'];
            if (in_array($request->title, $checkTitle)) {
                foreach ($request->users as $user) {
                    foreach ($user['request'] as $service => $value) {
                        if ($value == 'true') {
                            switch ($service) {
                                case 'hclab':
                                    $createHC = true;
                                    break;
                                case 'pacs':
                                    $createPAC = true;
                                    break;
                                default:
                                    $createIT = true;
                                    break;
                            }
                        }
                    }
                }
            } else {
                $createIT = true;
            }
        } else {
            $createIT = true;
        }

        if ($createIT) {
            $this->createDocumentIT($request);
        }
        if ($createPAC) {
            $this->createDocumentPAC($request);
        }
        if ($createHC) {
            $this->createDocumentHC($request);
        }
    }

    private function createDocumentIT(Request $request)
    {
        $document            = new DocumentIT();
        $document->requester = auth()->user()->userid;
        $document->title     = $request->title;
        $document->type      = $request->document_type;

        $document->title  = $request->title;
        $document->detail = $request->support_detail;

        $document->document_phone = $request->document_phone;

        dump($document);
        // $document->save();

        $dataField                  = $request->all();
        $dataField['document_type'] = 'it';
        $dataField['approverType']  = 'it';
        // Assuming $document is the fileable model
        $this->helper->createApprover($dataField, $document);
        $this->helper->createFile($request, $document);
        $log = [
            'action' => 'create',
            'detail' => 'สร้างเอกสาร IT',
        ];
        $this->helper->createLog($log, $document);
    }
    private function createDocumentPAC($request)
    {
        dump('createDocumentPAC');
        $dataField                  = $request->all();
        $dataField['document_type'] = 'pac';
        $dataField['approverType']  = 'pac';

        $approverList = $this->helper->createApprover($dataField, $document);
        dump($approverList);
    }
    private function createDocumentHC($request)
    {
        dump('createDocumentHC');
        $dataField                  = $request->all();
        $dataField['document_type'] = 'hc';
        $dataField['approverType']  = 'hc';
        $approverList               = $this->helper->createApprover($dataField, $document);
        dump($approverList);
    }
}
