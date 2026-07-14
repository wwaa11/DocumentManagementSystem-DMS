<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DocumentMedia extends Model
{
    protected $table = 'document_medias';

    protected $fillable = [
        'requester',
        'document_phone',
        'document_number',
        'type',
        'title',
        'detail',
        'required_date',
        'sign_location',
        'brochure_sizes',
        'brochure_print_type',
        'photo_work_types',
        'photo_date',
        'photo_time',
        'photo_location',
        'other_text',
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
            'sign' => 'ป้าย',
            'brochure' => 'โบรชัวร์ / แผ่นพับ',
            'photo_video' => 'ถ่ายภาพ / วิดีโอ',
            'poster' => 'โปสเตอร์',
            'tent_card' => 'Tent Card',
            'standee' => 'Standee',
            'other' => 'อื่นๆ',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function signTypeLabels(): array
    {
        return [
            'standee' => 'สแตนดี้',
            'tent_card' => 'Tent Card (กรุณาระบุขนาด A4 / A5)',
            'poster' => 'Poster',
            'card' => 'การ์ด',
            'sign' => 'ป้าย',
            'sticker' => 'สติ๊กเกอร์ / สติ๊กเกอร์แร็ป',
            'health_report' => 'เล่มรายงานผลตรวจสุขภาพ',
            'gift_voucher' => 'Gift Voucher',
            'other_sign' => 'ป้ายอื่นๆ',
        ];
    }

    /**
     * Reference/example image per sign type (path under public/).
     * Omit a type to hide the example image.
     *
     * @return array<string, string>
     */
    public static function signTypeReferenceImages(): array
    {
        return [
            'standee' => 'images/standee.jpg',
            'tent_card' => 'images/tent_card.jpg',
            'poster' => 'images/poster.jpg',
            'card' => 'images/card.jpg',
            'sign' => 'images/sign.jpg',
            'sticker' => 'images/sticker.jpg',
            'health_report' => 'images/health_report.jpg',
            'gift_voucher' => 'images/gift_voucher.jpg',
        ];
    }

    public static function signTypeReferenceImage(string $signType): ?string
    {
        $images = self::signTypeReferenceImages();

        if (! isset($images[$signType])) {
            return null;
        }

        return asset($images[$signType]);
    }

    protected function casts(): array
    {
        return [
            'required_date' => 'date',
            'photo_date' => 'date',
            'brochure_sizes' => 'array',
            'photo_work_types' => 'array',
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

    public function signItems(): HasMany
    {
        return $this->hasMany(DocumentMediaSignItem::class, 'document_media_id');
    }

    public function getDocumentTypeNameAttribute(): string
    {
        $type = $this->attributes['type'] ?? null;

        if ($type === 'other' && ! empty($this->attributes['other_text'] ?? null)) {
            return (string) $this->attributes['other_text'];
        }

        return self::typeLabels()[$type] ?? ($type ?: '');
    }

    /**
     * @return array{document_tag: string, colour: string}
     */
    public function getDocumentTagAttribute(): array
    {
        return [
            'document_tag' => 'MEDIA',
            'colour' => 'secondary',
        ];
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
