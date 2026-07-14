<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename',
        'stored_path',
        'mime_type',
        'size',
    ];

    public function document(): MorphTo
    {
        return $this->morphTo('document', 'fileable_type', 'fileable_id');
    }

    public function isImage(): bool
    {
        $mime = strtolower((string) $this->mime_type);

        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        return in_array($this->extension(), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
    }

    public function isPdf(): bool
    {
        $mime = strtolower((string) $this->mime_type);

        if ($mime === 'application/pdf') {
            return true;
        }

        return $this->extension() === 'pdf';
    }

    public function isViewable(): bool
    {
        return $this->isImage() || $this->isPdf();
    }

    public function extension(): string
    {
        return strtolower(pathinfo((string) $this->original_filename, PATHINFO_EXTENSION));
    }

    public function resolvedMimeType(): string
    {
        if (filled($this->mime_type)) {
            return (string) $this->mime_type;
        }

        return match ($this->extension()) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}
