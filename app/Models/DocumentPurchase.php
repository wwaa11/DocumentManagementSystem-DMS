<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DocumentPurchase extends Model
{
    protected $table = 'document_purchases';

    protected $fillable = [
        'requester',
        'document_phone',
        'document_number',
        'type',
        'title',
        'detail',
        'po_number',
        'po_reason',
        'status',
        'assigned_user_id',
    ];

    protected $appends = [
        'document_type_name',
        'document_tag',
        'list_detail',
    ];

    /**
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        return [
            'code' => 'ขอเพิ่มแก้ไข Code สินค้า',
            'quotation' => 'ขอใบเสนอราคา',
            'boq' => 'BOQ',
            'po_edit' => 'ขออนุมัติแก้ไข/ยกเลิกใบสั่งซื้อ',
            'other' => 'อื่นๆ',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function typeCodes(): array
    {
        return [
            'code' => 'PURC',
            'quotation' => 'PURQ',
            'boq' => 'PURB',
            'po_edit' => 'PUR',
            'other' => 'PURE',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester', 'userid');
    }

    public function assigned_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'userid');
    }

    public function getDocumentTypeNameAttribute(): string
    {
        $type = $this->attributes['type'] ?? null;

        return self::typeLabels()[$type] ?? ($type ?: '');
    }

    /**
     * @return array{document_tag: string, colour: string}
     */
    public function getDocumentTagAttribute(): array
    {
        return [
            'document_tag' => 'PURCHASE',
            'colour' => 'accent',
        ];
    }

    public function getTitleAttribute(mixed $value): mixed
    {
        return is_string($value) && str_contains($value, '|') ? explode('|', $value) : $value;
    }

    public function getListDetailAttribute(): string
    {
        $detail = $this->attributes['detail'] ?? '';

        return strlen($detail) > 100 ? mb_substr($detail, 0, 100).'...' : $detail;
    }

    public function approvers(): MorphMany
    {
        return $this->morphMany(Approver::class, 'approvable');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, 'loggable');
    }
}
