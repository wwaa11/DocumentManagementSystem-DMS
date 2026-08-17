<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingLecturerRequest extends FormRequest
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
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_id.required' => 'ไม่พบวันอบรมที่ต้องการเพิ่มวิทยากร',
            'users.required' => 'กรุณาเลือกวิทยากรอย่างน้อย 1 คน',
            'users.min' => 'กรุณาเลือกวิทยากรอย่างน้อย 1 คน',
        ];
    }
}
