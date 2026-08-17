<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingDateRequest extends FormRequest
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
            'date_datetime' => ['required', 'date_format:Y-m-d'],
            'date_detail' => ['nullable', 'string', 'max:255'],
            'date_location' => ['nullable', 'string', 'max:255'],
            'times' => ['required', 'array', 'min:1'],
            'times.*.time_start' => ['required', 'date_format:H:i'],
            'times.*.time_end' => ['required', 'date_format:H:i', 'after:times.*.time_start'],
            'times.*.time_detail' => ['nullable', 'string', 'max:255'],
            'times.*.time_limit' => ['nullable', 'boolean'],
            'times.*.time_max' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_datetime.required' => 'กรุณาระบุวันที่อบรม',
            'date_datetime.date_format' => 'รูปแบบวันที่ไม่ถูกต้อง (Y-m-d)',
            'times.required' => 'กรุณาระบุช่วงเวลาอย่างน้อย 1 ช่วง',
            'times.*.time_start.required' => 'กรุณาระบุเวลาเริ่ม',
            'times.*.time_end.required' => 'กรุณาระบุเวลาสิ้นสุด',
            'times.*.time_end.after' => 'เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม',
        ];
    }
}
