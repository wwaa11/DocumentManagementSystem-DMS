<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CompleteUserDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required',
            'type' => 'required',
            'status' => 'required|in:approve,reject',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'กรุณาระบุรหัสเอกสาร (Document ID is required).',
            'type.required' => 'กรุณาระบุประเภทเอกสาร (Document type is required).',
            'status.required' => 'กรุณาระบุสถานะเอกสาร (Document status is required).',
            'status.in' => 'สถานะต้องเป็น approve หรือ reject เท่านั้น (Status must be approve or reject).',
        ];
    }
}
