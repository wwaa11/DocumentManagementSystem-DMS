<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentBorrow extends Model
{
    protected $table = 'document_borrows';

    private $documentStatuses = [
        'wait_approval',  // Wait for Approval
        'not_approval',   // Not-Approval Document
        'cancel',         // Requester cancel the request
        'reject',         // Hardware reject by admin
        'pending',        // pending for admin to process
        'borrow_approve', // waiting for admin to approve hardware
        'borrow',         // Hardware is borrowed
        'return_approve', // Hardware is borrowed
        'return',         // Hardware is returned
        'complete',       // Document is completed
    ];

    protected $appends = [
        'document_type_name',
        'document_tag',
        'list_detail',
    ];

    protected $casts = [
        'estimate_return_date' => 'datetime',
    ];

    public function getDocumentTagAttribute()
    {
        return [
            'document_tag' => 'BORROW',
            'colour'       => 'secondary',
        ];
    }

    public function getDocumentTypeNameAttribute()
    {
        switch ($this->status) {
            case 'borrow':
                $text = 'อุปกรณ์ที่ยิม';
                break;
            default:
                $text = 'ขอยืมอุปกรณ์';
                break;
        }

        return $text;
    }

    public function getListDetailAttribute()
    {

        return strlen($this->detail) > 100 ? mb_substr($this->detail, 0, 100) . '...' : $this->detail;
    }

    public function allHardwareRetrieve()
    {
        $total    = $this->hardwares()->count();
        $retrieve = $this->hardwares()->whereNotNull('return_date')->count();

        return ($total == $retrieve) ? true : false;
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

    // Relationship to Hardware
    public function hardwares()
    {
        return $this->hasMany(Hardware::class, 'borrow_id');
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
