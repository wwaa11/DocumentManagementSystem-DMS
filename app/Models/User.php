<?php
namespace App\Models;

use App\Models\DocumentIT;
use App\Models\DocumentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'menu',
    ];

    public function getMenuAttribute()
    {
        switch ($this->role) {
            case 'admin':
                return [
                    'count' => [
                        [
                            'type'  => 'it',
                            'route' => 'admin.it.count',
                        ],
                        [
                            'type'  => 'pac',
                            'route' => 'admin.user.count',
                        ],
                        [
                            'type'  => 'lab',
                            'route' => 'admin.user.count',
                        ],
                        [
                            'type'  => 'heartstream',
                            'route' => 'admin.user.count',
                        ],
                        [
                            'type'  => 'register',
                            'route' => 'admin.user.count',
                        ],
                    ],
                    'lists' => [
                        // IT
                        [
                            'title' => 'IT',
                            'type'  => 'it',
                            'id'    => 'title',
                            'link'  => null,
                            'count' => false,
                        ],
                        [
                            'title' => 'Hardware Jobs',
                            'type'  => 'it',
                            'id'    => 'hardware',
                            'link'  => 'admin.it.hardwarelist',
                            'count' => true,
                        ],
                        [
                            'title' => 'Approve Jobs',
                            'type'  => 'it',
                            'id'    => 'approve',
                            'link'  => 'admin.it.approvelist',
                            'count' => true,
                        ],
                        [
                            'title' => 'New Jobs',
                            'type'  => 'it',
                            'id'    => 'new',
                            'link'  => 'admin.it.newlist',
                            'count' => true,
                        ],
                        [
                            'title' => 'My Jobs',
                            'type'  => 'it',
                            'id'    => 'my',
                            'link'  => 'admin.it.mylist',
                            'count' => true,
                        ],
                        [
                            'title' => 'All Jobs',
                            'type'  => 'it',
                            'id'    => 'all',
                            'link'  => 'admin.it.alllist',
                            'count' => false,
                        ],
                        // PAC
                        [
                            'title' => 'PAC',
                            'type'  => 'pac',
                            'id'    => 'title',
                            'link'  => null,
                            'count' => false,
                        ],
                        [
                            'title' => 'Approve Jobs',
                            'type'  => 'pac',
                            'id'    => 'approve',
                            'link'  => 'admin.user.approvelist',
                            'count' => true,
                        ],
                        [
                            'title' => 'New Jobs',
                            'type'  => 'pac',
                            'id'    => 'new',
                            'link'  => 'admin.user.newlist',
                            'count' => true,
                        ],
                        [
                            'title' => 'My Jobs',
                            'type'  => 'pac',
                            'id'    => 'my',
                            'link'  => 'admin.user.mylist',
                            'count' => true,
                        ],
                        [
                            'title' => 'All Jobs',
                            'type'  => 'pac',
                            'id'    => 'all',
                            'link'  => 'admin.user.alllist',
                            'count' => false,
                        ],
                        // LAB
                        [
                            'title' => 'LAB',
                            'type'  => 'lab',
                            'id'    => 'title',
                            'link'  => null,
                            'count' => false,
                        ],
                        [
                            'title' => 'Approve Jobs',
                            'type'  => 'lab',
                            'id'    => 'approve',
                            'link'  => 'admin.user.approvelist',
                            'count' => true,
                        ],
                        [
                            'title' => 'New Jobs',
                            'type'  => 'lab',
                            'id'    => 'new',
                            'link'  => 'admin.user.newlist',
                            'count' => true,
                        ],
                        [
                            'title' => 'My Jobs',
                            'type'  => 'lab',
                            'id'    => 'my',
                            'link'  => 'admin.user.mylist',
                            'count' => true,
                        ],
                        [
                            'title' => 'All Jobs',
                            'type'  => 'lab',
                            'id'    => 'all',
                            'link'  => 'admin.user.alllist',
                            'count' => false,
                        ],
                        // HEARTSTREAM
                        [
                            'title' => 'HEARTSTREAM',
                            'type'  => 'heartstream',
                            'id'    => 'title',
                            'link'  => null,
                            'count' => false,
                        ],
                        [
                            'title' => 'Approve Jobs',
                            'type'  => 'heartstream',
                            'id'    => 'approve',
                            'link'  => 'admin.user.approvelist',
                            'count' => true,
                        ],
                        [
                            'title' => 'New Jobs',
                            'type'  => 'heartstream',
                            'id'    => 'new',
                            'link'  => 'admin.user.newlist',
                            'count' => true,
                        ],
                        [
                            'title' => 'My Jobs',
                            'type'  => 'heartstream',
                            'id'    => 'my',
                            'link'  => 'admin.user.mylist',
                            'count' => true,
                        ],
                        [
                            'title' => 'All Jobs',
                            'type'  => 'heartstream',
                            'id'    => 'all',
                            'link'  => 'admin.user.alllist',
                            'count' => false,
                        ],
                        // REGISTRATION
                        [
                            'title' => 'REGISTRATION',
                            'type'  => 'register',
                            'id'    => 'title',
                            'link'  => null,
                            'count' => false,
                        ],
                        [
                            'title' => 'Approve Jobs',
                            'type'  => 'register',
                            'id'    => 'approve',
                            'link'  => 'admin.user.approvelist',
                            'count' => true,
                        ],
                        [
                            'title' => 'New Jobs',
                            'type'  => 'register',
                            'id'    => 'new',
                            'link'  => 'admin.user.newlist',
                            'count' => true,
                        ],
                        [
                            'title' => 'My Jobs',
                            'type'  => 'register',
                            'id'    => 'my',
                            'link'  => 'admin.user.mylist',
                            'count' => true,
                        ],
                        [
                            'title' => 'All Jobs',
                            'type'  => 'register',
                            'id'    => 'all',
                            'link'  => 'admin.user.alllist',
                            'count' => false,
                        ],
                    ],
                ];
                break;
            default:
                return false;
                break;
        }
    }

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
        $documentList         = Approver::where('userid', $this->userid)->whereIn('status', ['wait', 'approve'])->orderByDesc('id')->get();
        $filteredDocumentList = $documentList->filter(function ($item) {
            if ($item->step == 1) {
                return true;
            } else {
                $checkBeforeStep = Approver::where('approvable_type', $item->approvable_type)->where('approvable_id', $item->approvable_id)->where('step', $item->step - 1)->first();
                return $checkBeforeStep && $checkBeforeStep->status == 'approve';
            }
        });

        return (object) $filteredDocumentList->values();
    }

    public function getMyDocuments()
    {
        $userId = auth()->user()->userid;

        $users = DocumentUser::where('requester', $userId)
            ->select(
                'id',
                'requester',
                'title',
                'detail',
                'created_at',
            );

        $its = DocumentIT::where('requester', $userId)
            ->where('type', 'support')
            ->select(
                'id',
                'requester',
                'document_number',
                'title',
                'detail',
                'document_its.status as status',
                'created_at',
            );

        $document = [];
        foreach ($its->get() as $item) {
            $document[] = $item;
        }
        foreach ($users->get() as $item) {
            $document[] = $item;
        }

        // sort by created_at
        usort($document, function ($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        return $document;
    }

}
