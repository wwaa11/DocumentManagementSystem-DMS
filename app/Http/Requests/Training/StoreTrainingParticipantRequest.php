<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingParticipantRequest extends FormRequest
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
            'time_id.required' => 'ไม่พบช่วงเวลาที่ต้องการเพิ่มผู้เข้าร่วม',
            'users.required' => 'กรุณาเลือกผู้เข้าร่วมอย่างน้อย 1 คน',
            'users.min' => 'กรุณาเลือกผู้เข้าร่วมอย่างน้อย 1 คน',
        ];
    }
}
