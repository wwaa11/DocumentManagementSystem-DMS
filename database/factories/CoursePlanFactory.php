<?php

namespace Database\Factories;

use App\Models\CoursePlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoursePlan>
 */
class CoursePlanFactory extends Factory
{
    protected $model = CoursePlan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => (int) now()->year,
            'department' => 'แผนกเทคโนโลยีสารสนเทศ',
            'created_by' => User::query()->value('userid') ?? '650000',
            'status' => 'wait_approval',
        ];
    }
}
