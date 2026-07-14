<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentMediaSignItem extends Model
{
    protected $fillable = [
        'document_media_id',
        'sign_type',
        'detail',
        'image_path',
        'original_filename',
    ];

    public function documentMedia(): BelongsTo
    {
        return $this->belongsTo(DocumentMedia::class, 'document_media_id');
    }

    public function getSignTypeLabelAttribute(): string
    {
        return DocumentMedia::signTypeLabels()[$this->sign_type] ?? $this->sign_type;
    }

    public function getReferenceImageUrlAttribute(): ?string
    {
        return DocumentMedia::signTypeReferenceImage($this->sign_type);
    }
}
