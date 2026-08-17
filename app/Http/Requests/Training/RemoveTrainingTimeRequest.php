<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class RemoveTrainingTimeRequest extends FormRequest
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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'time_id.required' => 'ไม่พบช่วงเวลาที่ต้องการลบ',
        ];
    }
}
