<?php

namespace App\Http\Requests\IT;

use App\Models\HisLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHisLogRequest extends FormRequest
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
            'reported_at' => ['required', 'date'],
            'reporter' => ['required', 'string', 'max:255'],
            'module' => ['required', 'string', Rule::in(HisLog::moduleOptions())],
            'issues' => ['required', 'array', 'min:1'],
            'issues.*' => ['string', Rule::in(HisLog::issueOptions())],
            'problem_detail' => ['nullable', 'string'],
            'fixer' => ['nullable', 'string', 'max:255'],
            'root_cause' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(HisLog::statusOptions())],
            'time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reported_at.required' => 'กรุณาระบุวันที่แจ้ง',
            'reporter.required' => 'กรุณาระบุผู้แจ้ง/แผนก',
            'module.required' => 'กรุณาเลือก Module',
            'module.in' => 'Module ที่เลือกไม่ถูกต้อง',
            'issues.required' => 'กรุณาเลือก Issue อย่างน้อย 1 รายการ',
            'issues.min' => 'กรุณาเลือก Issue อย่างน้อย 1 รายการ',
            'status.required' => 'กรุณาเลือกสถานะ',
            'time.required' => 'กรุณาระบุเวลา',
            'time.date_format' => 'รูปแบบเวลาไม่ถูกต้อง',
        ];
    }
}
