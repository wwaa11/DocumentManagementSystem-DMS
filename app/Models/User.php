<?php
namespace App\Models;

use App\Models\DocumentHc;
use App\Models\DocumentIT;
use App\Models\DocumentPac;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'userid',
        'name',
        'position',
        'department',
        'division',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $appends = [
        'getapprover',
    ];

    public function getGetApproverAttribute()
    {
        // Check if approver data is already in session
        if (session()->has('user_approver')) {
            return session('user_approver');
        }

        // If not in session, fetch from API
        $response = Http::withHeaders([
            'token' => env('API_AUTH_KEY'),
        ])->post('http://172.20.1.12/dbstaff/api/getapprover', [
            'userid' => auth()->user()->userid,
        ])->json();

        if (! isset($response['status']) || $response['status'] != 1) {
            $result = (object) ['status' => false];
        } else {
            $result = (object) ['status' => true, 'approver' => (object) $response['approver']];
        }
        // Store in session for future use
        session(['user_approver' => $result]);

        return $result;
    }

    public function itsDocuments(): HasMany
    {
        return $this->hasMany(DocumentIT::class, 'requester', 'userid');
    }

    public function hcsDocuments(): HasMany
    {
        return $this->hasMany(DocumentHc::class, 'requester', 'userid');
    }

    public function pacsDocuments(): HasMany
    {
        return $this->hasMany(DocumentPac::class, 'requester', 'userid');
    }

    public function getApproveDocument()
    {

        return Approver::where('userid', $this->userid)->where('status', 'wait')->orderByDesc('id')->get();
    }

    public function getAllDocumentsOrdered()
    {
        $userId = auth()->user()->userid;

        $its = DocumentIT::where('requester', $userId)
            ->select(
                'id',
                'requester',
                'document_number',
                'title',
                'detail',
                'document_its.status as status',
                'created_at',
                DB::raw("'document_its' as document_type"),
                DB::raw('NULL as is_approver')
            );

        $hcs = DocumentHc::where('requester', $userId)
            ->select(
                'id',
                'requester',
                'document_number',
                'title',
                'detail',
                'document_hcs.status as status',
                'created_at',
                DB::raw("'document_hcs' as document_type"),
                DB::raw('NULL as is_approver')
            );

        $pacs = DocumentPac::where('requester', $userId)
            ->select(
                'id',
                'requester',
                'document_number',
                'title',
                'detail',
                'document_pacs.status as status',
                'created_at',
                DB::raw("'document_pacs' as document_type"),
                DB::raw('NULL as is_approver')
            );

        $document = $its->unionAll($hcs)
            ->unionAll($pacs)
            ->orderByDesc('created_at')
            ->get();

        return $document;
    }
}
