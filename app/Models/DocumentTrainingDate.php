<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    function getDateStringAttribute()
    {
        return $this->date->format('Y-m-d');
    }

    public function training()
    {
        return $this->belongsTo(DocumentTraining::class, 'document_training_id');
    }
}
