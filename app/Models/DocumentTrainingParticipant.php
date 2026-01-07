<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTrainingParticipant extends Model
{
    protected $table = 'document_training_participants';

    public function training()
    {
        return $this->belongsTo(DocumentTraining::class, 'id', 'training_id');
    }
}
