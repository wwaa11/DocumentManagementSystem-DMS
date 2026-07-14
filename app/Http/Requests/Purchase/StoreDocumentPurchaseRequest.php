<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentPurchaseRequest extends FormRequest
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
            'document_type' => ['required', 'string', Rule::in(['code', 'quotation', 'boq', 'po_edit', 'other'])],
            'documentCode' => ['required', 'string', Rule::in(['PURC', 'PURQ', 'PURB', 'PUR', 'PURE'])],
            'selfApprove' => ['required', 'string', Rule::in(['true', 'false'])],
            'approver' => ['required', 'array'],
            'approver.userid' => ['required', 'string'],
            'approver.position' => ['nullable', 'string'],
            'approver.email' => ['nullable', 'string'],
            'document_phone' => ['required', 'string', 'max:50'],
            'detail' => ['nullable', 'string'],
            'title_other_text' => ['required_if:document_type,other', 'nullable', 'string', 'max:255'],
            'po_number' => ['required_if:document_type,po_edit', 'nullable', 'string', 'max:100'],
            'po_reason' => [
                'required_if:document_type,po_edit',
                'nullable',
                'string',
                Rule::in([
                    'ชนิดสินค้าไม่ถูกต้อง',
                    'ราคาของสินค้าไม่ถูกต้อง',
                    'จำนวนของสินค้าไม่ถูกต้อง',
                    'อื่นๆ',
                ]),
            ],
            'po_reason_other' => ['required_if:po_reason,อื่นๆ', 'nullable', 'string', 'max:255'],
            'document_files' => ['nullable', 'array', 'max:20'],
            'document_files.*' => ['file', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_type.required' => 'กรุณาเลือกประเภทของเอกสาร',
            'document_phone.required' => 'กรุณาระบุเบอร์โทรศัพท์ภายในติดต่อกลับ',
            'title_other_text.required_if' => 'กรุณาระบุประเภทเอกสารอื่นๆ',
            'po_number.required_if' => 'กรุณาระบุเลขที่ใบสั่งซื้อ',
            'po_reason.required_if' => 'กรุณาเลือกรายละเอียดการขอแก้ไข/ยกเลิกใบสั่งซื้อ',
            'po_reason_other.required_if' => 'กรุณาระบุรายละเอียดอื่นๆ',
            'approver.required' => 'กรุณาระบุผู้อนุมัติ',
            'approver.userid.required' => 'กรุณาระบุผู้อนุมัติ',
        ];
    }
}
