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
        'complete',      // Document is completed
    ];

    protected $fillable = [
        'document_user_id',
        'document_number',
    ];

    protected $appends = [
        'document_tag',
        'list_detail',
    ];

    public function getDocumentTagAttribute()
    {
        return [
            'document_tag' => 'PAC',
            'colour'       => 'warning',
        ];
    }

    public function documentUser()
    {
        return $this->belongsTo(DocumentUser::class, 'document_user_id', 'id');
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
