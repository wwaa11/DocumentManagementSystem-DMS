<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class RemoveTrainingDateRequest extends FormRequest
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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_id.required' => 'ไม่พบวันอบรมที่ต้องการลบ',
        ];
    }
}
