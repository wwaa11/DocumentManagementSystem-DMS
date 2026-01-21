<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentItUser extends Model
{
    protected $table = 'document_itusers';

    private $documentStatuses = [
        'wait_approval', // Wait for Approval
        'not_approval',  // Not-Approval Document
        'cancel',        // Requester cancel the request
        'pending',       // Pending for admin to process
        'reject',        // Document reject by admin
        'process',       // Document is processing
        'done',          // Document is done wait for head of admin to approve
        'complete',      // Document is completed
    ];

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
            'document_tag' => 'USER',
            'colour'       => 'warning',
        ];
    }

    public function getDocumentTypeNameAttribute()
    {
        return 'ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม';
    }

    public function getDetailAttribute()
    {
        return $this->documentUser->detail;
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

    public function assigned_user()
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'userid');
    }

    public function approvers()
    {
        return $this->documentUser->approvers();
    }

    public function files()
    {
        return $this->documentUser->files();
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
