<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CoursePlanItem extends Model
{
    public const OUT_OF_PLAN_OBJECTIVE = 'จัดฝึกอบรมนอกแผนหลักสูตรประจำปี';

    protected $fillable = [
        'course_plan_id',
        'number',
        'name',
        'origin',
        'objective',
        'training_type',
        'schedule_months',
        'estimated_cost',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schedule_months' => 'array',
            'estimated_cost' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function trainingTypeLabels(): array
    {
        return [
            'internal' => 'ภายใน',
            'external' => 'ภายนอก',
            'elearning' => 'E-learning',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function monthLabels(): array
    {
        return [
            1 => 'มกราคม',
            2 => 'กุมภาพันธ์',
            3 => 'มีนาคม',
            4 => 'เมษายน',
            5 => 'พฤษภาคม',
            6 => 'มิถุนายน',
            7 => 'กรกฎาคม',
            8 => 'สิงหาคม',
            9 => 'กันยายน',
            10 => 'ตุลาคม',
            11 => 'พฤศจิกายน',
            12 => 'ธันวาคม',
        ];
    }

    public function coursePlan(): BelongsTo
    {
        return $this->belongsTo(CoursePlan::class);
    }

    public function instructors(): HasMany
    {
        return $this->hasMany(CourseInstructor::class);
    }

    public function targetPositions(): HasMany
    {
        return $this->hasMany(CourseTargetPosition::class);
    }

    public function responsibles(): HasMany
    {
        return $this->hasMany(CourseResponsible::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(DocumentTraining::class)->latest();
    }

    public function training(): HasOne
    {
        return $this->hasOne(DocumentTraining::class)->latestOfMany();
    }

    public function hasTrainingDocument(): bool
    {
        if ($this->relationLoaded('trainings')) {
            return $this->trainings->isNotEmpty();
        }

        if ($this->relationLoaded('training')) {
            return $this->training !== null;
        }

        return $this->trainings()->exists();
    }

    public function isOutOfPlan(): bool
    {
        return $this->objective === self::OUT_OF_PLAN_OBJECTIVE
            || str_starts_with((string) $this->origin, 'จัดนอกแผน:');
    }

    public function trainingTypeLabel(): string
    {
        return self::trainingTypeLabels()[$this->training_type] ?? $this->training_type;
    }

    /**
     * @return list<string>
     */
    public function scheduleMonthLabels(): array
    {
        $labels = self::monthLabels();

        return collect($this->schedule_months ?? [])
            ->map(fn ($month): string => $labels[(int) $month] ?? (string) $month)
            ->values()
            ->all();
    }
}
