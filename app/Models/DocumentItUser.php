<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentChat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DocumentItUser extends Model
{
    use HasDocumentChat;

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
            'colour' => 'warning',
        ];
    }

    public function getDocumentTypeNameAttribute()
    {
        return 'ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม';
    }

    public function getDetailAttribute(): ?string
    {
        return $this->documentUser?->detail;
    }

    public function getListDetailAttribute(): ?string
    {
        $detail = $this->documentUser?->detail;

        if ($detail === null) {
            return null;
        }

        return strlen($detail) > 100 ? mb_substr($detail, 0, 100).'...' : $detail;
    }

    public function documentUser(): BelongsTo
    {
        return $this->belongsTo(DocumentUser::class, 'document_user_id', 'id');
    }

    /**
     * The requester is stored on the parent document, so it is resolved through it
     * instead of proxying to `documentUser`, which cannot be eager loaded.
     */
    public function creator(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            DocumentUser::class,
            'id',
            'userid',
            'document_user_id',
            'requester',
        );
    }

    public function assigned_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'userid');
    }

    /**
     * Approvers are attached to the parent document. Keying the relation on
     * `document_user_id` keeps the parent's morph type while remaining eager loadable.
     */
    public function approvers(): HasMany
    {
        return $this->hasMany(Approver::class, 'approvable_id', 'document_user_id')
            ->withAttributes(['approvable_type' => DocumentUser::class]);
    }

    /**
     * Files are attached to the parent document, see `approvers()`.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'fileable_id', 'document_user_id')
            ->withAttributes(['fileable_type' => DocumentUser::class]);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    public function messages(): MorphMany
    {
        return $this->morphMany(DocumentMessage::class, 'messagable');
    }
}
