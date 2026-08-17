<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainingTimeRequest extends FormRequest
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
            'project_id' => ['required', 'integer', 'exists:document_trainings,id'],
            'time_id' => ['required', 'integer'],
            'time_start' => ['nullable', 'date_format:H:i'],
            'time_end' => ['nullable', 'date_format:H:i', 'after:time_start'],
            'time_detail' => ['nullable', 'string', 'max:255'],
            'time_limit' => ['nullable', 'boolean'],
            'time_max' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'time_id.required' => 'ไม่พบช่วงเวลาที่ต้องการแก้ไข',
            'time_end.after' => 'เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม',
        ];
    }
}
