<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'approver' => 'required|array',
            'mentors_userid' => 'nullable|array',
            'participants_userid' => 'required|array',
            'training_name' => 'required|string',
            'date_mode' => 'required|string|in:range,specific',
            'start_date' => 'required_if:date_mode,range|nullable|date',
            'end_date' => 'required_if:date_mode,range|nullable|date',
            'start_time' => 'required_if:date_mode,range|nullable',
            'end_time' => 'required_if:date_mode,range|nullable',
            'specific_date' => 'required_if:date_mode,specific|array',
            'specific_start_time' => 'required_if:date_mode,specific|array',
            'specific_end_time' => 'required_if:date_mode,specific|array',
            'duration_hours' => 'required|integer',
            'duration_minutes' => 'nullable|integer',
            'source_type' => 'required|string|in:in_plan,substitute,out_of_plan',
            'course_plan_item_id' => ['nullable', 'integer', 'exists:course_plan_items,id'],
            'substitute_course_plan_item_id' => ['nullable', 'required_if:source_type,substitute', 'integer', 'exists:course_plan_items,id'],
            'substitute_topic' => ['nullable', 'string', 'max:255'],
            'substitute_reason' => ['nullable', 'required_if:source_type,substitute', 'string', 'max:255'],
            'plan_no' => ['nullable', 'string', 'max:50'],
            'out_of_plan_reason' => ['nullable', 'required_if:source_type,out_of_plan', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'substitute_course_plan_item_id.required_if' => 'กรุณาเลือกหลักสูตรในแผนที่ถูกแทน',
            'substitute_reason.required_if' => 'กรุณาระบุเหตุผลที่จัดแทน',
            'out_of_plan_reason.required_if' => 'กรุณาระบุเหตุผลที่จัดนอกแผน',
        ];
    }
}
