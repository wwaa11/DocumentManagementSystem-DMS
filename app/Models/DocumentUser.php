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
        'document_status',
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

    public function getDocumentStatusAttribute()
    {
        return 'test';
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

        $it = DocumentIT::where('document_user_id', $document_user_id)->first();
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
