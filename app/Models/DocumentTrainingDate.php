<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTrainingDate extends Model
{
    protected $fillable = [
        'document_training_id',
        'date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function getDateStringAttribute(): string
    {
        return $this->date->format('Y-m-d');
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(DocumentTraining::class, 'document_training_id');
    }

    public function canEdit(): bool
    {
        $trainingDate = Carbon::parse($this->date_string.' '.$this->start_time);

        return $trainingDate->diffInMinutes(now(), false) < -60;
    }
}
