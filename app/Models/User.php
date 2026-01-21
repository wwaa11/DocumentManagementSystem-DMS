<?php
namespace App\Models;

use App\Models\DocumentBorrow;
use App\Models\DocumentIT;
use App\Models\DocumentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    private function countList($listArray)
    {
        $count = [];
        $dataList = [
            "it" => [
                'type' => 'it',
                'route' => 'admin.it.count',
            ],
            "pac" => [
                'type' => 'pac',
                'route' => 'admin.user.count',
            ],
            "lab" => [
                'type' => 'lab',
                'route' => 'admin.user.count',
            ],
            "heartstream" => [
                'type' => 'heartstream',
                'route' => 'admin.user.count',
            ],
            "register" => [
                'type' => 'register',
                'route' => 'admin.user.count',
            ],
        ];

        foreach ($listArray as $value) {
            $count[] = $dataList[$value];
        }

        return $count;
    }
    private function menuList($listArray)
    {
        $menu = [];
        $menulist = [
            'admin' => [
                [
                    'title' => 'Admin',
                    'type' => 'admin',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'Approvers',
                    'type' => 'approver',
                    'id' => 'approver',
                    'link' => 'approvers.list',
                    'count' => false,
                ],
                [
                    'title' => 'Roles',
                    'type' => 'role',
                    'id' => 'role',
                    'link' => 'roles.list',
                    'count' => false,
                ],
            ],
            'it-approve' => [
                [
                    'title' => 'Approve',
                    'type' => 'it',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'Approve Jobs',
                    'type' => 'it',
                    'id' => 'approve',
                    'link' => 'admin.it.approvelist',
                    'count' => true,
                ]
            ],
            'it-hardware' => [
                [
                    'title' => 'Hardware',
                    'type' => 'it',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'Hardware Jobs',
                    'type' => 'it',
                    'id' => 'hardware',
                    'link' => 'admin.it.hardwarelist',
                    'count' => true,
                ]
            ],
            'it' => [
                [
                    'title' => 'IT',
                    'type' => 'it',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'Borrow',
                    'type' => 'it',
                    'id' => 'borrow',
                    'link' => 'admin.it.borrowlist',
                    'count' => true,
                ],
                [
                    'title' => 'New Jobs',
                    'type' => 'it',
                    'id' => 'new',
                    'link' => 'admin.it.newlist',
                    'count' => true,
                ],
                [
                    'title' => 'My Jobs',
                    'type' => 'it',
                    'id' => 'my',
                    'link' => 'admin.it.mylist',
                    'count' => true,
                ],
                [
                    'title' => 'All Jobs',
                    'type' => 'it',
                    'id' => 'all',
                    'link' => 'admin.it.alllist',
                    'count' => false,
                ],
            ]
        ];

        $userMenu = ['pac', 'lab', 'heartstream', 'register'];
        foreach ($userMenu as $type) {
            $menulist[$type . '-approve'] = [
                [
                    'title' => 'Approve ' . strtoupper($type),
                    'type' => $type,
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'Approve Jobs',
                    'type' => $type,
                    'id' => 'approve',
                    'link' => 'admin.user.approvelist',
                    'count' => true,
                ],
            ];
            $menulist[$type] = [
                [
                    'title' => strtoupper($type),
                    'type' => $type,
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'New Jobs',
                    'type' => $type,
                    'id' => 'new',
                    'link' => 'admin.user.newlist',
                    'count' => true,
                ],
                [
                    'title' => 'My Jobs',
                    'type' => $type,
                    'id' => 'my',
                    'link' => 'admin.user.mylist',
                    'count' => true,
                ],
                [
                    'title' => 'All Jobs',
                    'type' => $type,
                    'id' => 'all',
                    'link' => 'admin.user.alllist',
                    'count' => false,
                ],
            ];
        }
        ;

        foreach ($listArray as $value) {
            if (array_key_exists($value, $menulist)) {
                $menu = array_merge($menu, $menulist[$value]);
            }
        }

        return $menu;
    }

    public function getMenuAttribute()
    {
        if ($this->role == 'dev') {
            $count = $this->countList(['it', 'pac', 'lab', 'heartstream', 'register']);
            $menu = $this->menuList(['admin', 'it-approve', 'it-hardware', 'it', 'pac-approve', 'pac', 'lab-approve', 'lab', 'heartstream-approve', 'heartstream', 'register-approve', 'register']);
        } elseif ($this->role == 'admin') {
            $count = $this->countList(['it']);
            $menu = $this->menuList(['admin', 'it']);
        } elseif ($this->role == 'it') {
            $count = $this->countList(['it']);
            $menu = $this->menuList(['it']);
        } elseif ($this->role == 'it-approve') {
            $count = $this->countList(['it']);
            $menu = $this->menuList(['it-approve', 'it']);
        } elseif ($this->role == 'it-hardware') {
            $count = $this->countList(['it']);
            $menu = $this->menuList(['it-hardware', 'it']);
        } elseif ($this->role == 'lab') {
            $count = $this->countList(['lab']);
            $menu = $this->menuList(['lab']);
        } elseif ($this->role == 'lab-approve') {
            $count = $this->countList(['lab']);
            $menu = $this->menuList(['lab-approve', 'lab']);
        } elseif ($this->role == 'pac') {
            $count = $this->countList(['pac']);
            $menu = $this->menuList(['pac']);
        } elseif ($this->role == 'pac-approve') {
            $count = $this->countList(['pac']);
            $menu = $this->menuList(['pac-approve', 'pac']);
        } elseif ($this->role == 'heartstream') {
            $count = $this->countList(['heartstream']);
            $menu = $this->menuList(['heartstream']);
        } elseif ($this->role == 'heartstream-approve') {
            $count = $this->countList(['heartstream']);
            $menu = $this->menuList(['heartstream-approve', 'heartstream']);
        } elseif ($this->role == 'register') {
            $count = $this->countList(['register']);
            $menu = $this->menuList(['register']);
        } elseif ($this->role == 'register-approve') {
            $count = $this->countList(['register']);
            $menu = $this->menuList(['register-approve', 'register']);
        }

        return [
            'count' => $count,
            'lists' => $menu,
        ];
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

        if (!isset($response['status']) || $response['status'] != 1) {
            $result = (object) ['status' => false];
        } else {
            $result = (object) ['status' => true, 'approver' => (object) $response['approver']];
        }
        // Store in session for future use
        session(['user_approver' => $result]);

        return $result;
    }

    public function getApproveDocument()
    {
        $documentList = Approver::where('userid', $this->userid)->whereIn('status', ['wait', 'approve'])->orderByDesc('id')->get();
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
                'status',
                'created_at',
            );

        $borrows = DocumentBorrow::where('requester', $userId)
            ->select(
                'id',
                'requester',
                'document_number',
                'title',
                'detail',
                'status',
                'created_at',
            );
        $trainings = DocumentTraining::where('requester', $userId)
            ->select(
                'id',
                'requester',
                'title',
                'detail',
                'status',
                'created_at',
            );

        $document = [];
        foreach ($its->get() as $item) {
            $document[] = $item;
        }
        foreach ($users->get() as $item) {
            $document[] = $item;
        }
        foreach ($borrows->get() as $item) {
            $document[] = $item;
        }
        foreach ($trainings->get() as $item) {
            $document[] = $item;
        }

        // sort by created_at
        usort($document, function ($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        return $document;
    }

}
