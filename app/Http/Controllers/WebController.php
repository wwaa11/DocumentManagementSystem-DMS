<?php
namespace App\Http\Controllers;

use App\Http\Controllers\HelperController;
use App\Models\Document;
use App\Models\DocumentHc;
use App\Models\DocumentIT;
use App\Models\DocumentPac;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WebController extends Controller
{
    private $helper;

    public function __construct()
    {
        mb_internal_encoding('UTF-8');
        $this->helper = new HelperController();
    }

    public function myDocument()
    {
        $my_documents     = auth()->user()->getAllDocumentsOrdered();
        $approveDocuments = auth()->user()->getApproveDocument();
        $documents        = [];
        foreach ($approveDocuments as $item) {
            $documentData            = $item->document;
            $document_id             = $documentData->document_tag["document_tag"] . $documentData->id;
            $detail                  = strlen($documentData->detail) > 100 ? mb_substr($documentData->detail, 0, 100) . '...' : $documentData->detail;
            $documents[$document_id] = [
                'flag'               => 'approve',
                'id'                 => $documentData->id,
                'document_tag'       => $documentData->document_tag,
                'document_number'    => $documentData->document_number,
                'document_type_name' => $documentData->document_type_name,
                'title'              => $documentData->title,
                'detail'             => $detail,
                'status'             => $documentData->status,
                'created_at'         => $documentData->created_at,
            ];
        }
        foreach ($my_documents as $item) {
            $document_id = $item->document_tag["document_tag"] . $item->id;
            if (! isset($documents[$document_id])) {
                $detail                  = strlen($item->detail) > 100 ? mb_substr($item->detail, 0, 100) . '...' : $item->detail;
                $documents[$document_id] = [
                    'flag'               => 'my',
                    'id'                 => $item->id,
                    'document_tag'       => $item->document_tag,
                    'document_number'    => $item->document_number,
                    'document_type_name' => $item->document_type_name,
                    'title'              => $item->title,
                    'detail'             => $detail,
                    'status'             => $item->status,
                    'created_at'         => $item->created_at,
                ];
            }
        }

        $perPage            = 15;
        $currentPage        = LengthAwarePaginator::resolveCurrentPage();
        $currentItems       = array_slice($documents, ($currentPage - 1) * $perPage, $perPage);
        $paginatedDocuments = new LengthAwarePaginator($currentItems, count($documents), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        return view('documnet_index', ['documents' => $paginatedDocuments]);
    }

    public function createDocument()
    {
        $document = Document::active()->get();

        return view('document_create', compact('document'));
    }

    public function createDocumentByType($document_type)
    {
        $data = [];
        switch ($document_type) {
            case 'it':
                $view      = 'document.it.create';
                $it_admins = User::whereIN('role', ['it', 'admin'])->get();
                $data      = compact('it_admins');
                break;
            case 'media':
                $view = 'document.media.create';
                break;
            case 'purchase':
                $view = 'document.purchase.create';
                break;
            default:
                return redirect()->route('document.create');
                break;
        }

        return view($view, $data);
    }

    public function userSearch(Request $request)
    {
        $userid   = $request->input('userid');
        $response = Http::withHeaders([
            'token' => env('API_AUTH_KEY'),
        ])->post('http://172.20.1.12/dbstaff/api/getuser', [
            'userid' => $userid,
        ])->json();
        if (! isset($response['status']) || $response['status'] != 1) {

            return ['status' => false];
        }

        return response()->json(['status' => true, 'user' => $response['user']]);
    }

    public function viewDocument($document_type, $document_id)
    {
        switch ($document_type) {
            case 'IT':
                $document = DocumentIT::find($document_id);
                $view     = 'document.it.view';
                break;
            case 'PAC':
                $document = DocumentPac::find($document_id);
                $view     = 'document.it.view';
                break;
            case 'HCLAB':
                $document = DocumentHc::find($document_id);
                $view     = 'document.it.view';
                break;
            default:
                return redirect()->route('document.index');
                break;
        }

        return view($view, compact('document'));
    }

    public function fileShow(File $file)
    {
        $path = $file->stored_path;

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path, $file->original_filename);
    }

    public function fileDownload(File $file)
    {
        $path = $file->stored_path;

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path, $file->original_filename);
    }

}
