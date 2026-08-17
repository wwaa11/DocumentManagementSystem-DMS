<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTrainingMentor extends Model
{
    protected $table = 'document_training_mentors';

    protected $fillable = [
        'document_training_id',
        'mentor',
        'mentor_name',
        'mentor_position',
    ];

    public function training()
    {
        return $this->belongsTo(DocumentTraining::class, 'id', 'training_id');
    }
}
