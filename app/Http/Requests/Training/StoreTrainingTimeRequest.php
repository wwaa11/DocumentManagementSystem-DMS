<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingTimeRequest extends FormRequest
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
            'date_id' => ['required', 'integer'],
            'time_start' => ['required', 'date_format:H:i'],
            'time_end' => ['required', 'date_format:H:i', 'after:time_start'],
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
            'date_id.required' => 'ไม่พบวันอบรมของช่วงเวลานี้',
            'time_start.required' => 'กรุณาระบุเวลาเริ่ม',
            'time_end.required' => 'กรุณาระบุเวลาสิ้นสุด',
            'time_end.after' => 'เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม',
        ];
    }
}
