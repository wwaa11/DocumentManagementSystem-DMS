<?php

namespace App\Http\Requests\IT;

use App\Models\HisLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'problem_detail' => ['nullable', 'string'],
            'fixer' => ['nullable', 'string', 'max:255'],
            'root_cause' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(HisLog::statusOptions())],
            'time' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $fixer = $this->input('fixer');

            if (! filled($fixer)) {
                return;
            }

            $allowedFixers = HisLog::fixerOptions();
            $hisLog = $this->route('hisLog');

            if ($hisLog instanceof HisLog && filled($hisLog->fixer)) {
                $allowedFixers[] = $hisLog->fixer;
            }

            if (! in_array($fixer, $allowedFixers, true)) {
                $validator->errors()->add('fixer', 'ผู้แก้ไขที่เลือกไม่ถูกต้อง');
            }
        });
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
            'status.required' => 'กรุณาเลือกสถานะ',
            'time.required' => 'กรุณาระบุเวลา',
            'time.date_format' => 'รูปแบบเวลาไม่ถูกต้อง',
        ];
    }
}
