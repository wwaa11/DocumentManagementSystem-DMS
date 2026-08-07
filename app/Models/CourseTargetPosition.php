<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseTargetPosition extends Model
{
    protected $fillable = [
        'course_plan_item_id',
        'position',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(CoursePlanItem::class, 'course_plan_item_id');
    }
}
