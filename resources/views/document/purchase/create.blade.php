@extends('layouts.app')
@section('content')
    <div class="mx-auto max-w-5xl">
        <x-document.page-header
            title="แจ้งงานจัดซื้อ"
            description="กรอกข้อมูลด้านล่างเพื่อสร้างเอกสารจัดซื้อ"
            icon="fas fa-shopping-cart"
        />
        <form id="create-form" action="{{ route('document.purchase.create') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-document.approver-form />
            <x-ui.validation-errors />

            <input id="selfApprove" type="hidden" name="selfApprove" value="true">
            <input id="documentCode" type="hidden" name="documentCode" value="">

            <div class="card bg-base-100 mb-8 p-6 shadow-xl">
                <x-ui.section-title icon="fas fa-file-alt">ประเภทของเอกสาร</x-ui.section-title>
                <div class="flex flex-col gap-3">
                    <x-ui.radio-option
                        id="type-code"
                        name="document_type"
                        value="code"
                        onchange="selectPurchaseType('code')"
                    >
                        ขอเพิ่มแก้ไข Code สินค้า
                    </x-ui.radio-option>

                    <x-ui.radio-option
                        id="type-quotation"
                        name="document_type"
                        value="quotation"
                        onchange="selectPurchaseType('quotation')"
                    >
                        ขอใบเสนอราคา
                    </x-ui.radio-option>

                    <x-ui.radio-option
                        id="type-boq"
                        name="document_type"
                        value="boq"
                        onchange="selectPurchaseType('boq')"
                    >
                        BOQ
                    </x-ui.radio-option>

                    <x-ui.radio-option
                        id="type-po-edit"
                        name="document_type"
                        value="po_edit"
                        onchange="selectPurchaseType('po_edit')"
                        hint="*ต้องการขออนุมัติจากแผนก"
                    >
                        ขออนุมัติแก้ไข/ยกเลิกใบสั่งซื้อ
                    </x-ui.radio-option>

                    <x-ui.radio-option
                        id="type-other"
                        name="document_type"
                        value="other"
                        onchange="selectPurchaseType('other')"
                    >
                        อื่นๆ ระบุ
                    </x-ui.radio-option>
                </div>

                <div class="mt-4 hidden" id="other-type-section">
                    <input
                        class="input input-bordered w-full"
                        id="title_other_text"
                        name="title_other_text"
                        type="text"
                        placeholder="ระบุประเภทเอกสารอื่นๆ"
                        disabled
                    />
                </div>

                <section class="mt-6 hidden" id="po-edit-section">
                    <x-ui.section-title icon="fas fa-file-invoice" class="mb-2">
                        เลขที่ใบสั่งซื้อ
                    </x-ui.section-title>
                    <input
                        class="input input-bordered mb-6 w-full"
                        id="po_number"
                        name="po_number"
                        type="text"
                        placeholder="เลขที่ใบสั่งซื้อ"
                        disabled
                    />

                    <x-ui.section-title icon="fas fa-list" class="mb-2">
                        Detail
                    </x-ui.section-title>
                    <div class="flex flex-col gap-3">
                        <x-ui.radio-option id="po-reason-1" name="po_reason" value="ชนิดสินค้าไม่ถูกต้อง" onchange="togglePoReasonOther(false)">
                            ชนิดสินค้าไม่ถูกต้อง
                        </x-ui.radio-option>
                        <x-ui.radio-option id="po-reason-2" name="po_reason" value="ราคาของสินค้าไม่ถูกต้อง" onchange="togglePoReasonOther(false)">
                            ราคาของสินค้าไม่ถูกต้อง
                        </x-ui.radio-option>
                        <x-ui.radio-option id="po-reason-3" name="po_reason" value="จำนวนของสินค้าไม่ถูกต้อง" onchange="togglePoReasonOther(false)">
                            จำนวนของสินค้าไม่ถูกต้อง
                        </x-ui.radio-option>
                        <x-ui.radio-option id="po-reason-4" name="po_reason" value="อื่นๆ" onchange="togglePoReasonOther(true)">
                            อื่นๆ ระบุ
                        </x-ui.radio-option>
                    </div>
                    <input
                        class="input input-bordered mt-3 w-full"
                        id="po_reason_other"
                        name="po_reason_other"
                        type="text"
                        placeholder="ระบุรายละเอียดอื่นๆ"
                        disabled
                    />
                </section>

                <div class="hidden" id="document-additional-info">
                    <div class="divider"></div>

                    <x-ui.section-title icon="fas fa-align-left" class="mb-2">
                        รายละเอียดเพิ่มเติม
                    </x-ui.section-title>
                    <textarea
                        class="textarea textarea-bordered w-full"
                        id="detail"
                        name="detail"
                        rows="4"
                        placeholder="รายละเอียดเพิ่มเติม"
                    ></textarea>

                    <x-ui.section-title icon="fas fa-paperclip" class="mb-2 mt-6">
                        เอกสารแนบ (ถ้ามี)
                    </x-ui.section-title>
                    <x-form.file-dropzone hint="* ใส่เอกสารแนบได้ไม่เกิน 20 ไฟล์" />

                    <x-ui.section-title icon="fas fa-phone-alt" class="mb-2 mt-6">
                        เบอร์โทรศัพท์ภายในติดต่อกลับ
                    </x-ui.section-title>
                    <input
                        class="input input-bordered w-full"
                        id="document_phone"
                        name="document_phone"
                        type="text"
                        placeholder="เบอร์โทรศัพท์ภายในติดต่อกลับ"
                        value="{{ old('document_phone') }}"
                    />

                    <div class="mt-6 flex justify-center">
                        <button class="btn btn-accent gap-2 transition-all duration-200 hover:scale-105" type="submit" onclick="submitForm()">
                            <i class="fas fa-paper-plane"></i> สร้างเอกสาร
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        function selectPurchaseType(type) {
            const codeMap = {
                code: 'PURC',
                quotation: 'PURQ',
                boq: 'PURB',
                po_edit: 'PUR',
                other: 'PURE',
            };

            const needsApproval = type === 'po_edit';
            $('#selfApprove').val(needsApproval ? 'false' : 'true');
            $('#documentCode').val(codeMap[type] || '');
            $('#document-additional-info').removeClass('hidden');

            if (type === 'other') {
                $('#other-type-section').removeClass('hidden');
                $('#title_other_text').prop('disabled', false);
            } else {
                $('#other-type-section').addClass('hidden');
                $('#title_other_text').prop('disabled', true).val('');
            }

            if (type === 'po_edit') {
                $('#po-edit-section').removeClass('hidden');
                $('#po_number').prop('disabled', false);
                $('input[name="po_reason"]').prop('disabled', false);
            } else {
                $('#po-edit-section').addClass('hidden');
                $('#po_number').prop('disabled', true).val('');
                $('input[name="po_reason"]').prop('checked', false).prop('disabled', true);
                togglePoReasonOther(false);
            }
        }

        function togglePoReasonOther(enable) {
            $('#po_reason_other').prop('disabled', !enable);
            if (!enable) {
                $('#po_reason_other').val('');
            }
        }

        function submitForm() {
            event.preventDefault();

            const form = '#create-form';
            const type = $('input[name="document_type"]:checked').val();
            if (!type) {
                highlightInvalidField('input[name="document_type"]', form);
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกประเภทของเอกสาร',
                    confirmButtonText: 'ตกลง',
                    buttonsStyling: false,
                    customClass: { confirmButton: 'btn btn-primary' },
                });
                return;
            }

            let isValid = true;
            let errorMessage = '';
            let errorField = null;

            if (type === 'other' && !$('#title_other_text').val()) {
                isValid = false;
                errorMessage = 'กรุณาระบุประเภทเอกสารอื่นๆ';
                errorField = '#title_other_text';
            } else if (type === 'po_edit') {
                if (!$('#po_number').val()) {
                    isValid = false;
                    errorMessage = 'กรุณาระบุเลขที่ใบสั่งซื้อ';
                    errorField = '#po_number';
                } else if (!$('input[name="po_reason"]:checked').val()) {
                    isValid = false;
                    errorMessage = 'กรุณาเลือกรายละเอียด';
                    errorField = 'input[name="po_reason"]';
                } else if ($('input[name="po_reason"]:checked').val() === 'อื่นๆ' && !$('#po_reason_other').val()) {
                    isValid = false;
                    errorMessage = 'กรุณาระบุรายละเอียดอื่นๆ';
                    errorField = '#po_reason_other';
                }
            }

            if (isValid && !$('#document_phone').val()) {
                isValid = false;
                errorMessage = 'กรุณาระบุเบอร์โทรศัพท์ภายใน';
                errorField = '#document_phone';
            }

            if (!isValid) {
                showValidationError(errorMessage, errorField, form);
                return;
            }

            clearFormFieldErrors(form);

            Swal.fire({
                title: 'ต้องการสร้างเอกสารหรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary mx-3',
                    cancelButton: 'btn btn-ghost mx-3'
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังสร้างเอกสาร...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    document.getElementById('create-form').submit();
                }
            });
        }
    </script>
@endpush
