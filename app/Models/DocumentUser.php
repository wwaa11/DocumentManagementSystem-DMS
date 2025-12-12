<?php
namespace App\Models;

use App\Models\DocumentHc;
use App\Models\DocumentHeartstream;
use App\Models\DocumentPac;
use Illuminate\Database\Eloquent\Model;

class DocumentUser extends Model
{
    protected $table = 'document_users';

    protected $fillable = [
        'requester',
        'document_phone',
        'title',
        'detail',
    ];

    protected $appends = [
        'document_type_name',
        'document_tag',
        'list_detail',
        'status',
    ];

    public function getDocumentTypeNameAttribute()
    {
        return 'ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม';
    }

    public function getDocumentTagAttribute()
    {
        return [
            'document_tag' => 'USER',
            'colour'       => 'warning',
        ];
    }

    public function getListDetailAttribute()
    {

        return strlen($this->detail) > 100 ? mb_substr($this->detail, 0, 100) . '...' : $this->detail;
    }

    public function getStatusAttribute()
    {
        $documentStatus = null;
        $documents      = $this->getAllDocuments();
        $statusArray    = [];
        foreach ($documents as $document) {
            switch ($document->status) {
                case ("wait_approval"):
                    $statusArray[] = 'wait_approval';
                    break;
                case ("cancel"):
                    $statusArray[] = 'cancel';
                    break;
                case ("not_approval"):
                    $statusArray[] = 'not_approval';
                    break;
                case ("pending"):
                    $statusArray[] = 'pending';
                    break;
                case ("reject"):
                    $statusArray[] = 'reject';
                    break;
                case ("process"):
                    $statusArray[] = 'process';
                    break;
                case ("done"):
                    $statusArray[] = 'done';
                    break;
                case ("complete"):
                    $statusArray[] = 'complete';
                    break;
            }
        }
        $isWaitApproval = collect($statusArray)->every(function ($value) {
            return $value === 'wait_approval';
        });
        $isCancel = collect($statusArray)->every(function ($value) {
            return $value === 'cancel';
        });
        $isNotApproval = collect($statusArray)->every(function ($value) {
            return $value === 'not_approval';
        });
        $isPending = collect($statusArray)->every(function ($value) {
            return $value === 'pending';
        });
        $isReject = collect($statusArray)->every(function ($value) {
            return $value === 'reject';
        });
        $isProcess = collect($statusArray)->every(function ($value) {
            return $value === 'process';
        });
        $isDone = collect($statusArray)->every(function ($value) {
            return $value === 'done';
        });
        $isComplete = collect($statusArray)->every(function ($value) {
            return $value === 'complete';
        });

        if ($isWaitApproval) {
            $documentStatus = 'wait_approval';
        } elseif ($isCancel) {
            $documentStatus = 'cancel';
        } elseif ($isNotApproval) {
            $documentStatus = 'not_approval';
        } elseif ($isPending) {
            $documentStatus = 'pending';
        } elseif ($isReject) {
            $documentStatus = 'reject';
        } elseif ($isProcess) {
            $documentStatus = 'process';
        } elseif ($isDone) {
            $documentStatus = 'done';
        } elseif ($isComplete) {
            $documentStatus = 'complete';
        } elseif (in_array('pending', $statusArray)) {
            $documentStatus = 'pending';
        } elseif (in_array('process', $statusArray)) {
            $documentStatus = 'process';
        }

        return $documentStatus;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'requester', 'userid');
    }

    public function approvers()
    {
        return $this->morphMany(Approver::class, 'approvable');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function getAllDocuments()
    {
        $document_user_id = $this->id;
        $document         = [];

        $it = DocumentitUser::where('document_user_id', $document_user_id)->first();
        if ($it) {
            $document[] = $it;
        }
        $pac = DocumentPac::where('document_user_id', $document_user_id)->first();
        if ($pac) {
            $document[] = $pac;
        }
        $hc = DocumentHc::where('document_user_id', $document_user_id)->first();
        if ($hc) {
            $document[] = $hc;
        }
        $heartstream = DocumentHeartstream::where('document_user_id', $document_user_id)->first();
        if ($heartstream) {
            $document[] = $heartstream;
        }
        $registration = DocumentRegister::where('document_user_id', $document_user_id)->first();
        if ($registration) {
            $document[] = $registration;
        }

        return $document;
    }

}
