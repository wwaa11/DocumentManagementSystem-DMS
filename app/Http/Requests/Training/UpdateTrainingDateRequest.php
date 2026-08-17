<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainingDateRequest extends FormRequest
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
            'date_datetime' => ['nullable', 'date_format:Y-m-d'],
            'date_detail' => ['nullable', 'string', 'max:255'],
            'date_location' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_id.required' => 'ไม่พบวันอบรมที่ต้องการแก้ไข',
            'date_datetime.date_format' => 'รูปแบบวันที่ไม่ถูกต้อง (Y-m-d)',
        ];
    }
}
