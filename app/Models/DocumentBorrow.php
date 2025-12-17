<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentBorrow extends Model
{
    protected $table = 'document_borrows';

    private $documentStatuses = [
        'wait_approval', // Wait for Approval
        'not_approval',  // Not-Approval Document
        'cancel',        // Requester cancel the request
        'waiting',       // Waiting for admin to process
        'reject',        // Hardware reject by admin
        'borrow',        // Hardware is borrowed
        'return',        // Hardware is returned
        'complete',      // Document is completed
    ];

    protected $appends = [
        'document_tag',
        'list_detail',
    ];

    public function getDocumentTagAttribute()
    {
        return [
            'document_tag' => 'IT',
            'colour'       => 'secondary',
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
