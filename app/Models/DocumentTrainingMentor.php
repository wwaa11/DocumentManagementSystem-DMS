<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTrainingMentor extends Model
{
    protected $table = 'document_training_mentors';

    public function training()
    {
        return $this->belongsTo(DocumentTraining::class, 'id', 'training_id');
    }
}
