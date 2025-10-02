<?php
namespace App\Http\Controllers;

use App\Http\Controllers\HelperController;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebController extends Controller
{
    private $helper;

    public function __construct()
    {
        $this->helper = new HelperController();
    }

    public function myDocument()
    {
        $my_documents = auth()->user()->getAllDocumentsOrdered();

        return view('document.lists', compact('my_documents'));
    }

    public function createDocument()
    {
        $document = Document::active()->get();

        return view('document.create', compact('document'));
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

}
