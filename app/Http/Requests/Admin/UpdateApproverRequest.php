<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class UpdateApproverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(\App\Services\Admin\ApproverAdminService::class)->canManageApprovers($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'department' => 'required|string',
            'userid' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'email' => 'required|string|max:255',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $department = $this->input('department');

            if (! filled($department) || $validator->errors()->has('department')) {
                return;
            }

            $exists = DB::connection('staff')
                ->table('departments')
                ->where('department', $department)
                ->exists();

            if (! $exists) {
                $validator->errors()->add('department', 'ไม่พบแผนกนี้ในระบบ');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'department.required' => 'กรุณาเลือกแผนก',
            'userid.required' => 'กรุณาระบุ User ID',
            'name.required' => 'กรุณาระบุชื่อ-นามสกุล',
            'position.required' => 'กรุณาระบุตำแหน่ง',
            'email.required' => 'กรุณาระบุอีเมล',
        ];
    }
}
