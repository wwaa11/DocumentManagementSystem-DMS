<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentHc extends Model
{
    protected $table = 'document_hcs';

    private $documentStatuses = [
        'wait_approval', // Wait for Approval
        'not_approval',  // Not-Approval Document
        'cancel',        // Requester cancel the request
        'pending',       // Pending for admin to process
        'reject',        // Document reject by admin
        'process',       // Document is processing
        'done',          // Document is done wait for head of admin to approve
        'finish',        // Document is finished
    ];

    protected $appends = [
        'document_type_name',
        'document_tag',
    ];

    public function getDocumentTypeNameAttribute()
    {
        return 'ขอสิทธิใช้งานโปรแกรม';
    }

    public function getDocumentTagAttribute()
    {
        return [
            'document_tag' => 'HCLAB',
            'colour'       => 'success',
        ];
    }

    // Relationship to Approver
    public function approvers()
    {
        // 'approvable' must match the prefix used in the approvers table migration
        return $this->morphMany(Approver::class, 'approvable');
    }

    // Relationship to Tasks
    public function tasks()
    {
        // 'taskable' must match the prefix used in the tasks table migration
        return $this->morphMany(Task::class, 'taskable');
    }

    // Relationship to Files
    public function files()
    {
        // 'fileable' must match the prefix used in the files table migration
        return $this->morphMany(File::class, 'fileable');
    }

    // Relationship to Logs
    public function logs()
    {
        // 'loggable' must match the prefix used in the logs table migration
        return $this->morphMany(Log::class, 'loggable');
    }
}
