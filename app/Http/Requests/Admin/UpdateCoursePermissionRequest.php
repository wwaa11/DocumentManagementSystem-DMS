<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoursePermissionRequest extends FormRequest
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
            'can_create_course' => ['required', 'boolean'],
            'course_departments' => ['nullable', 'array'],
            'course_departments.*' => ['string'],
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
            'can_create_course.required' => 'กรุณาระบุสิทธิ์การสร้างหลักสูตร',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'can_create_course' => filter_var($this->input('can_create_course', false), FILTER_VALIDATE_BOOLEAN),
            'course_departments' => array_values(array_filter((array) $this->input('course_departments', []))),
        ]);
    }
}
