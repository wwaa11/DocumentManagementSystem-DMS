<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTrainingParticipant extends Model
{
    protected $table = 'document_training_participants';

    protected $fillable = [
        'document_training_id',
        'participant',
        'participant_name',
        'participant_position',
        'participant_department',
        'assetment_date',
        'assetment_type',
        'score',
    ];

    protected $casts = [
        'assetment_date' => 'datetime',
    ];

    public function training()
    {
        return $this->belongsTo(DocumentTraining::class, 'id', 'training_id');
    }
}
