<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRegister extends Model
{
    protected $table = 'document_registers';

    protected $fillable = [
        'document_user_id',
        'document_number',
    ];

    protected $appends = [
        'document_tag',
        'document_type_name',
        'list_detail',
    ];

    public function getDocumentTagAttribute()
    {
        return [
            'document_tag' => 'Register',
            'colour'       => 'warning',
        ];
    }

    public function getDocumentTypeNameAttribute()
    {
        return 'ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม';
    }

    public function getListDetailAttribute()
    {
        return strlen($this->documentUser->detail) > 100 ? mb_substr($this->documentUser->detail, 0, 100) . '...' : $this->documentUser->detail;
    }

    public function documentUser()
    {
        return $this->belongsTo(DocumentUser::class, 'document_user_id', 'id');
    }

    public function creator()
    {
        return $this->documentUser->creator();
    }

    public function approvers()
    {
        return $this->documentUser->approvers();
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }
}
