<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentViewPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = (string) ($this->user()?->role ?? '');

        return in_array($role, ['admin', 'dev'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'userid' => ['required', 'string', 'exists:users,userid'],
            'can_view_department_documents' => ['required', 'boolean'],
            'view_departments' => ['nullable', 'array'],
            'view_departments.*' => ['string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'userid.required' => 'กรุณาระบุรหัสพนักงาน',
            'userid.exists' => 'ไม่พบผู้ใช้งานในระบบ',
            'can_view_department_documents.required' => 'กรุณาระบุสิทธิ์การดูเอกสารแผนก',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'can_view_department_documents' => filter_var($this->input('can_view_department_documents', false), FILTER_VALIDATE_BOOLEAN),
            'view_departments' => array_values(array_filter((array) $this->input('view_departments', []))),
        ]);
    }
}
