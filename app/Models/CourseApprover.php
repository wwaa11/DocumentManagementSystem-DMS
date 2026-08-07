<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseApprover extends Model
{
    protected $fillable = [
        'course_plan_id',
        'level',
        'userid',
        'name',
        'position',
        'email',
        'status',
        'reason',
        'approved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function coursePlan(): BelongsTo
    {
        return $this->belongsTo(CoursePlan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }
}
