<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ApproveTrainingAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('id') && ! $this->filled('transaction_id')) {
            $this->merge(['transaction_id' => $this->input('id')]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:document_trainings,id'],
            'transaction_id' => ['nullable', 'integer'],
            'transaction_ids' => ['nullable', 'array', 'min:1'],
            'transaction_ids.*' => ['integer'],
            'approve_all' => ['nullable', 'boolean'],
            'userid' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasSingle = filled($this->input('transaction_id'));
            $hasMany = filled($this->input('transaction_ids'));
            $approveAll = $this->boolean('approve_all');

            if (! $hasSingle && ! $hasMany && ! $approveAll) {
                $validator->errors()->add(
                    'transaction_ids',
                    'กรุณาเลือกรายการที่ต้องการอนุมัติ หรือเลือกอนุมัติทั้งหมด',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'ไม่พบเอกสารการฝึกอบรม',
            'transaction_ids.min' => 'กรุณาเลือกรายการที่ต้องการอนุมัติอย่างน้อย 1 รายการ',
        ];
    }
}
