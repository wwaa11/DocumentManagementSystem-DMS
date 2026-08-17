<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class RemoveTrainingParticipantRequest extends FormRequest
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
            'attend_id' => ['nullable', 'required_without_all:time_id,userid', 'integer'],
            'time_id' => ['nullable', 'required_with:userid', 'integer'],
            'userid' => ['nullable', 'required_with:time_id', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attend_id.required_without_all' => 'ต้องระบุ attend_id หรือ time_id พร้อม userid',
            'time_id.required_with' => 'ต้องระบุช่วงเวลาคู่กับรหัสพนักงาน',
            'userid.required_with' => 'ต้องระบุรหัสพนักงานคู่กับช่วงเวลา',
        ];
    }
}
