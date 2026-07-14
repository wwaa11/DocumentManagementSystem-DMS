<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentMediaRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', Rule::in(['sign', 'brochure', 'photo_video', 'poster', 'tent_card', 'standee', 'other'])],
            'documentCode' => ['required', 'string', Rule::in(['MED'])],
            'selfApprove' => ['required', 'string', Rule::in(['true', 'false'])],
            'approver' => ['required', 'array'],
            'approver.userid' => ['required', 'string'],
            'approver.position' => ['nullable', 'string'],
            'approver.email' => ['nullable', 'string'],
            'document_phone' => ['required', 'string', 'max:50'],
            'required_date' => ['required', 'date'],
            'detail' => ['nullable', 'string'],
            'other_text' => ['required_if:document_type,other', 'nullable', 'string', 'max:255'],
            'sign_types' => ['required_if:document_type,sign', 'nullable', 'array', 'min:1'],
            'sign_types.*' => ['string', Rule::in(array_keys(\App\Models\DocumentMedia::signTypeLabels()))],
            'sign_details' => ['nullable', 'array'],
            'sign_details.*' => ['nullable', 'string'],
            'sign_location' => ['required_if:document_type,sign', 'nullable', 'string', 'max:255'],
            'brochure_sizes' => ['required_if:document_type,brochure', 'nullable', 'array', 'min:1'],
            'brochure_sizes.*' => ['string'],
            'brochure_print_type' => ['required_if:document_type,brochure', 'nullable', 'string', Rule::in(['พิมพ์สี', 'พิมพ์ขาวดำ'])],
            'photo_work_types' => ['required_if:document_type,photo_video', 'nullable', 'array', 'min:1'],
            'photo_work_types.*' => ['string'],
            'photo_date' => ['required_if:document_type,photo_video', 'nullable', 'date'],
            'photo_time' => ['required_if:document_type,photo_video', 'nullable', 'string', 'max:50'],
            'photo_location' => ['required_if:document_type,photo_video', 'nullable', 'string', 'max:255'],
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
            'title.required' => 'กรุณาระบุชื่องาน',
            'document_type.required' => 'กรุณาเลือกประเภทสื่อ',
            'document_phone.required' => 'กรุณาระบุเบอร์โทรศัพท์ภายในติดต่อกลับ',
            'required_date.required' => 'กรุณาระบุวันที่ต้องการ',
            'other_text.required_if' => 'กรุณาระบุประเภทสื่ออื่นๆ',
            'sign_types.required_if' => 'กรุณาเลือกประเภทป้ายอย่างน้อย 1 รายการ',
            'sign_location.required_if' => 'กรุณาระบุสถานที่ติดตั้งป้าย',
            'brochure_sizes.required_if' => 'กรุณาเลือกขนาดโบรชัวร์ / แผ่นพับ',
            'brochure_print_type.required_if' => 'กรุณาเลือกประเภทการพิมพ์',
            'photo_work_types.required_if' => 'กรุณาเลือกลักษณะงาน',
            'photo_date.required_if' => 'กรุณาระบุวันที่ถ่ายทำ',
            'photo_time.required_if' => 'กรุณาระบุเวลาถ่ายทำ',
            'photo_location.required_if' => 'กรุณาระบุสถานที่ถ่ายทำ',
            'approver.required' => 'กรุณาระบุผู้อนุมัติ',
            'approver.userid.required' => 'กรุณาระบุผู้อนุมัติ',
        ];
    }
}
