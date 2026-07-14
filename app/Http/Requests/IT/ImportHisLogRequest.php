<?php

namespace App\Http\Requests\IT;

use Illuminate\Foundation\Http\FormRequest;

class ImportHisLogRequest extends FormRequest
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
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'excel_file.required' => 'กรุณาเลือกไฟล์ Excel',
            'excel_file.mimes' => 'รองรับเฉพาะไฟล์ .xlsx หรือ .xls',
            'excel_file.max' => 'ขนาดไฟล์ต้องไม่เกิน 10MB',
        ];
    }
}
