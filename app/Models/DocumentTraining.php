<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTraining extends Model
{
    protected $table = 'document_trainings';

    protected $appends = [
        'document_tag',
    ];

    public function getDocumentTagAttribute()
    {
        return [
            'document_tag' => 'Training',
            'colour'       => 'secondary',
        ];
    }

    public function approvers()
    {
        return $this->morphMany(Approver::class, 'approvable');
    }

    public function mentors()
    {
        return $this->hasMany(DocumentTrainingMentor::class, 'training_id');
    }

    public function participants()
    {
        return $this->hasMany(DocumentTrainingParticipant::class, 'training_id');
    }

    public function files()
    {
        // 'fileable' must match the prefix used in the files table migration
        return $this->morphMany(File::class, 'fileable');
    }
}
