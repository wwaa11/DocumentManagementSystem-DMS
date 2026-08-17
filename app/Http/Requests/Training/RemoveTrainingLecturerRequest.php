<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class RemoveTrainingLecturerRequest extends FormRequest
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
            'lecture_id' => ['nullable', 'required_without_all:date_id,userid', 'integer'],
            'date_id' => ['nullable', 'required_with:userid', 'integer'],
            'userid' => ['nullable', 'required_with:date_id', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lecture_id.required_without_all' => 'ต้องระบุ lecture_id หรือ date_id พร้อม userid',
            'date_id.required_with' => 'ต้องระบุวันอบรมคู่กับรหัสพนักงาน',
            'userid.required_with' => 'ต้องระบุรหัสพนักงานคู่กับวันอบรม',
        ];
    }
}
