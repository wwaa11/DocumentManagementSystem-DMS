<?php

namespace App\Http\Requests\IT;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentMessageRequest extends FormRequest
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
            'body' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attachments.max' => 'แนบไฟล์ได้ไม่เกิน 5 ไฟล์',
            'attachments.*.max' => 'ขนาดไฟล์ต้องไม่เกิน 10 MB',
        ];
    }
}
