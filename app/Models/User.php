<?php

namespace App\Models;

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
        'email',
        'role',
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
            'it' => [
                'type' => 'it',
                'route' => 'admin.it.count',
            ],
            'pac' => [
                'type' => 'pac',
                'route' => 'admin.user.count',
            ],
            'lab' => [
                'type' => 'lab',
                'route' => 'admin.user.count',
            ],
            'heartstream' => [
                'type' => 'heartstream',
                'route' => 'admin.user.count',
            ],
            'register' => [
                'type' => 'register',
                'route' => 'admin.user.count',
            ],
            'purchase' => [
                'type' => 'purchase',
                'route' => 'admin.purchase.count',
            ],
            'media' => [
                'type' => 'media',
                'route' => 'admin.media.count',
            ],
        ];

        foreach ($listArray as $value) {
            if (array_key_exists($value, $dataList)) {
                $count[] = $dataList[$value];
            }
        }

        return $count;
    }

    private function menuList($listArray)
    {
        $menu = [];
        $menulist = [
            'approvers' => [
                [
                    'title' => 'Approvers',
                    'type' => 'approver',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'Department Approvers',
                    'type' => 'approver',
                    'id' => 'approver',
                    'link' => 'approvers.list',
                    'count' => false,
                ],
            ],
            'roles' => [
                [
                    'title' => 'Roles',
                    'type' => 'role',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'User Roles',
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
                ],
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
                ],
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
                    'type' => 'ALL',
                    'id' => 'all',
                    'link' => 'admin.it.alllist',
                    'count' => false,
                ],
                [
                    'title' => 'Report',
                    'type' => 'it-report',
                    'id' => 'report',
                    'link' => 'admin.it.reportlist',
                    'count' => false,
                ],
                [
                    'title' => 'HIS Logs',
                    'type' => 'it',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'สร้าง HIS Log',
                    'type' => 'it-hislog',
                    'id' => 'hislog-create',
                    'link' => 'admin.it.hislogs.create',
                    'count' => false,
                ],
                [
                    'title' => 'All Logs',
                    'type' => 'it-hislog',
                    'id' => 'hislog-index',
                    'link' => 'admin.it.hislogs.index',
                    'count' => false,
                ],
                [
                    'title' => 'HIS Log Dashboard',
                    'type' => 'it-hislog',
                    'id' => 'hislog-dashboard',
                    'link' => 'admin.it.hislogs.dashboard',
                    'count' => false,
                ],
            ],
            'purchase-approve' => [
                [
                    'title' => 'Approve Purchase',
                    'type' => 'purchase',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'Approve Jobs',
                    'type' => 'purchase',
                    'id' => 'approve',
                    'link' => 'admin.purchase.approvelist',
                    'count' => true,
                ],
            ],
            'purchase-head' => [
                [
                    'title' => 'Purchase Head',
                    'type' => 'purchase',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'Head Approve Jobs',
                    'type' => 'purchase',
                    'id' => 'head',
                    'link' => 'admin.purchase.headlist',
                    'count' => true,
                ],
            ],
            'purchase' => [
                [
                    'title' => 'Purchase',
                    'type' => 'purchase',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'New Jobs',
                    'type' => 'purchase',
                    'id' => 'new',
                    'link' => 'admin.purchase.newlist',
                    'count' => true,
                ],
                [
                    'title' => 'My Jobs',
                    'type' => 'purchase',
                    'id' => 'my',
                    'link' => 'admin.purchase.mylist',
                    'count' => true,
                ],
                [
                    'title' => 'All Jobs',
                    'type' => 'purchase',
                    'id' => 'all',
                    'link' => 'admin.purchase.alllist',
                    'count' => false,
                ],
                [
                    'title' => 'Report',
                    'type' => 'purchase',
                    'id' => 'report',
                    'link' => 'admin.purchase.reportlist',
                    'count' => false,
                ],
            ],
            'media-head' => [
                [
                    'title' => 'Approve Media',
                    'type' => 'media',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'Approve Jobs',
                    'type' => 'media',
                    'id' => 'approve',
                    'link' => 'admin.media.approvelist',
                    'count' => true,
                ],
            ],
            'media' => [
                [
                    'title' => 'Media',
                    'type' => 'media',
                    'id' => 'title',
                    'link' => null,
                    'count' => false,
                ],
                [
                    'title' => 'Queue',
                    'type' => 'media',
                    'id' => 'queue',
                    'link' => 'admin.media.queuelist',
                    'count' => true,
                ],
                [
                    'title' => 'All Jobs',
                    'type' => 'media',
                    'id' => 'all',
                    'link' => 'admin.media.alllist',
                    'count' => false,
                ],
                [
                    'title' => 'Report',
                    'type' => 'media',
                    'id' => 'report',
                    'link' => 'admin.media.reportlist',
                    'count' => false,
                ],
            ],
        ];

        $userMenu = ['pac', 'lab', 'heartstream', 'register'];
        foreach ($userMenu as $type) {
            $menulist[$type.'-approve'] = [
                [
                    'title' => 'Approve '.strtoupper($type),
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
                [
                    'title' => 'Report',
                    'type' => $type,
                    'id' => 'report',
                    'link' => 'admin.user.reportlist',
                    'count' => false,
                ],
            ];
        }

        foreach ($listArray as $value) {
            if (array_key_exists($value, $menulist)) {
                $menu = array_merge($menu, $menulist[$value]);
            }
        }

        return $menu;
    }

    /**
     * @return array<int, array{key: string, label: string, menus: array<int, array<string, mixed>>, counts: array<int, array<string, string>>}>
     */
    private function menuGroups(array $groups): array
    {
        $result = [];

        foreach ($groups as $group) {
            $result[] = [
                'key' => $group['key'],
                'label' => $group['label'],
                'menus' => $this->menuList($group['menus']),
                'counts' => $this->countList($group['counts'] ?? []),
            ];
        }

        return $result;
    }

    public function getMenuAttribute()
    {
        if (in_array($this->role, ['admin', 'dev'], true)) {
            return [
                'count' => [],
                'lists' => [],
                'groups' => $this->menuGroups([
                    ['key' => 'admin', 'label' => 'Admin', 'menus' => ['approvers', 'roles'], 'counts' => []],
                    ['key' => 'it', 'label' => 'IT', 'menus' => ['it-approve', 'it-hardware', 'it'], 'counts' => ['it']],
                    ['key' => 'purchase', 'label' => 'Purchase', 'menus' => ['purchase-approve', 'purchase-head', 'purchase'], 'counts' => ['purchase']],
                    ['key' => 'media', 'label' => 'Media', 'menus' => ['media-head', 'media'], 'counts' => ['media']],
                    ['key' => 'pac', 'label' => 'PAC', 'menus' => ['pac-approve', 'pac'], 'counts' => ['pac']],
                    ['key' => 'lab', 'label' => 'LAB', 'menus' => ['lab-approve', 'lab'], 'counts' => ['lab']],
                    ['key' => 'heartstream', 'label' => 'Heartstream', 'menus' => ['heartstream-approve', 'heartstream'], 'counts' => ['heartstream']],
                    ['key' => 'register', 'label' => 'Register', 'menus' => ['register-approve', 'register'], 'counts' => ['register']],
                ]),
            ];
        }

        $count = [];
        $menu = [];

        if ($this->role == 'it') {
            $count = $this->countList(['it']);
            $menu = $this->menuList(['it', 'approvers']);
        } elseif ($this->role == 'it-approve') {
            $count = $this->countList(['it']);
            $menu = $this->menuList(['it-approve', 'it', 'roles']);
        } elseif ($this->role == 'it-hardware') {
            $count = $this->countList(['it']);
            $menu = $this->menuList(['it-hardware', 'it']);
        } elseif ($this->role == 'it-hardware-approve') {
            $count = $this->countList(['it']);
            $menu = $this->menuList(['it-approve', 'it-hardware', 'it', 'approvers', 'roles']);
        } elseif ($this->role == 'lab') {
            $count = $this->countList(['lab']);
            $menu = $this->menuList(['lab']);
        } elseif ($this->role == 'lab-approve') {
            $count = $this->countList(['lab']);
            $menu = $this->menuList(['lab-approve', 'lab', 'roles']);
        } elseif ($this->role == 'pac') {
            $count = $this->countList(['pac']);
            $menu = $this->menuList(['pac']);
        } elseif ($this->role == 'pac-approve') {
            $count = $this->countList(['pac']);
            $menu = $this->menuList(['pac-approve', 'pac', 'roles']);
        } elseif ($this->role == 'heartstream') {
            $count = $this->countList(['heartstream']);
            $menu = $this->menuList(['heartstream']);
        } elseif ($this->role == 'heartstream-approve') {
            $count = $this->countList(['heartstream']);
            $menu = $this->menuList(['heartstream-approve', 'heartstream', 'roles']);
        } elseif ($this->role == 'register') {
            $count = $this->countList(['register']);
            $menu = $this->menuList(['register']);
        } elseif ($this->role == 'register-approve') {
            $count = $this->countList(['register']);
            $menu = $this->menuList(['register-approve', 'register', 'roles']);
        } elseif ($this->role == 'purchase') {
            $count = $this->countList(['purchase']);
            $menu = $this->menuList(['purchase']);
        } elseif ($this->role == 'purchase-approve') {
            $count = $this->countList(['purchase']);
            $menu = $this->menuList(['purchase-approve', 'purchase', 'roles']);
        } elseif ($this->role == 'purchase-head') {
            $count = $this->countList(['purchase']);
            $menu = $this->menuList(['purchase-head', 'purchase', 'roles']);
        } elseif ($this->role == 'media') {
            $count = $this->countList(['media']);
            $menu = $this->menuList(['media']);
        } elseif ($this->role == 'media-head') {
            $count = $this->countList(['media']);
            $menu = $this->menuList(['media-head', 'media', 'roles']);
        }

        return [
            'count' => $count,
            'lists' => $menu,
            'groups' => [],
        ];
    }

    public function getGetApproverAttribute()
    {
        // Check if approver data is already in session
        if (session()->has('user_approver')) {
            return session('user_approver');
        }

        $response = Http::withHeaders([
            'token' => config('services.staff.token'),
        ])->timeout((int) config('services.staff.timeout', 30))
            ->post(rtrim((string) config('services.staff.base_url'), '/').'/getapprover', [
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

        $purchases = DocumentPurchase::where('requester', $userId)
            ->select(
                'id',
                'requester',
                'document_number',
                'type',
                'title',
                'detail',
                'status',
                'created_at',
            );

        $medias = DocumentMedia::where('requester', $userId)
            ->select(
                'id',
                'requester',
                'document_number',
                'type',
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
        foreach ($purchases->get() as $item) {
            $document[] = $item;
        }
        foreach ($medias->get() as $item) {
            $document[] = $item;
        }

        // sort by created_at
        usort($document, function ($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        return $document;
    }
}
