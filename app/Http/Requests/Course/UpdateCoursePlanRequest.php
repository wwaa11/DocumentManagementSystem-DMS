<?php

namespace App\Http\Requests\Course;

use App\Models\CoursePlan;

class UpdateCoursePlanRequest extends StoreCoursePlanRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var CoursePlan|null $plan */
        $plan = $this->route('course');

        if (! $user || ! $plan instanceof CoursePlan) {
            return false;
        }

        return $user->canCreateCourseForDepartment($plan->department)
            || ($this->input('department')
                ? $user->canCreateCourseForDepartment((string) $this->input('department'))
                : false);
    }
}
