<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTraining extends Model
{
    protected $table = 'document_trainings';

    protected $appends = [
        'document_tag',
    ];

    protected $fillable = [
        'status',
        'hrapprove',
        'course_plan_item_id',
    ];

    protected function casts(): array
    {
        return [
            'hrapprove' => 'datetime',
        ];
    }

    public function dates()
    {
        return $this->hasMany(DocumentTrainingDate::class, 'document_training_id');
    }

    public function coursePlanItem()
    {
        return $this->belongsTo(CoursePlanItem::class);
    }

    public function getDocumentTagAttribute()
    {
        return [
            'document_tag' => 'Training',
            'colour' => 'secondary',
        ];
    }

    public function getDocumentTypeNameAttribute(): string
    {
        return $this->course_plan_item_id
            ? 'ฝึกอบรมตามแผนหลักสูตร'
            : 'ฝึกอบรมนอกแผน';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'requester', 'userid');
    }

    public function approvers()
    {
        return $this->morphMany(Approver::class, 'approvable');
    }

    public function mentors()
    {
        return $this->hasMany(DocumentTrainingMentor::class, 'document_training_id');
    }

    public function participants()
    {
        return $this->hasMany(DocumentTrainingParticipant::class, 'document_training_id');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }
}
