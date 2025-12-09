<?php
namespace App\Models;

use App\Models\DocumentHc;
use App\Models\DocumentHeartstream;
use App\Models\DocumentIT;
use App\Models\DocumentPac;
use Illuminate\Database\Eloquent\Model;

class DocumentUser extends Model
{
    protected $table = 'document_users';

    protected $appends = [
        'document_type_name',
        'document_tag',
        'list_detail',
    ];

    protected $fillable = [
        'requester',
        'document_phone',
        'title',
        'detail',
    ];

    public function getDocumentTypeNameAttribute()
    {
        return 'ขอสิทธิใช้งานโปรแกรม';
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
        $documnet_user_id = $this->id;
        $documnet         = [];

        $it = DocumentIT::where('document_user_id', $documnet_user_id)->first();
        if ($it) {
            $documnet[] = $it;
        }
        $pac = DocumentPac::where('document_user_id', $documnet_user_id)->first();
        if ($pac) {
            $documnet[] = $pac;
        }
        $hc = DocumentHc::where('document_user_id', $documnet_user_id)->first();
        if ($hc) {
            $documnet[] = $hc;
        }
        $heartstream = DocumentHeartstream::where('document_user_id', $documnet_user_id)->first();
        if ($heartstream) {
            $documnet[] = $heartstream;
        }
        $registration = DocumentRegister::where('document_user_id', $documnet_user_id)->first();
        if ($registration) {
            $documnet[] = $registration;
        }

        return $document;
    }
}
