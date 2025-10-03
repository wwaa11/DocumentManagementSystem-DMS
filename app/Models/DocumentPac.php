<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentPac extends Model
{
    protected $table = 'document_pacs';

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

    // Relationship to Approver
    public function approvers()
    {
        // 'approvable' must match the prefix used in the approvers table migration
        return $this->morphMany(Approver::class, 'approvable');
    }

    // Relationship to Files
    public function files()
    {
        // 'fileable' must match the prefix used in the files table migration
        return $this->morphMany(File::class, 'fileable');
    }

    // Relationship to Activity Logs
    public function activityLogs()
    {
        // 'loggable' must match the prefix used in the activity_logs table migration
        return $this->morphMany(ActivityLog::class, 'loggable');
    }
}
