<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Http;

class User extends Authenticatable
{
    use Notifiable;

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
        $response = Http::withHeaders([
            'token' => env('API_AUTH_KEY'),
        ])->post('http://172.20.1.12/dbstaff/api/getapprover', [
            'userid' => auth()->user()->userid,
        ])->json();

        if (! isset($response['status']) || $response['status'] != 1) {

            return (object) ['status' => false];
        }

        return (object) ['status' => true, 'approver' => $response['approver']];
    }

    public function itsDocuments(): HasMany
    {
        return $this->hasMany(DocumentIts::class, 'requester', 'userid');
    }

    public function hcsDocuments(): HasMany
    {
        return $this->hasMany(DocumentHcs::class, 'requester', 'userid');
    }

    public function labsDocuments(): HasMany
    {
        return $this->hasMany(DocumentLabs::class, 'requester', 'userid');
    }

    public static function getAllDocumentsOrdered(string $userId)
    {
        // Ensure all select lists are identical (with the addition of a 'document_type' alias)
        $selectColumns = ['id', 'requester', 'document_number', 'title', 'status', 'created_at'];

        $its = DocumentIts::where('requester', $userId)
            ->select(array_merge($selectColumns, [DB::raw("'document_its' as document_type")]));

        $hcs = DocumentHcs::where('requester', $userId)
            ->select(array_merge($selectColumns, [DB::raw("'document_hcs' as document_type")]));

        $labs = DocumentLabs::where('requester', $userId)
            ->select(array_merge($selectColumns, [DB::raw("'document_labs' as document_type")]));

        return $its->unionAll($hcs)
            ->unionAll($labs)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
}
