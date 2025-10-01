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

    public static function getAllDocumentsOrdered()
    {
        // Ensure all select lists are identical (with the addition of a 'document_type' alias)
        $selectColumns = ['id', 'requester', 'document_number', 'title', 'status', 'created_at'];

        $its = DocumentIT::where('requester', auth()->user()->userid)
            ->select(array_merge($selectColumns, [DB::raw("'document_its' as document_type")]));

        $hcs = DocumentHc::where('requester', auth()->user()->userid)
            ->select(array_merge($selectColumns, [DB::raw("'document_hcs' as document_type")]));

        $pacs = DocumentPac::where('requester', auth()->user()->userid)
            ->select(array_merge($selectColumns, [DB::raw("'document_pacs' as document_type")]));

        return $its->unionAll($hcs)
            ->unionAll($pacs)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
}
