@extends('layouts.app')
@section('content')
    <div class="mx-auto max-w-5xl">
        <x-document.page-header
            title="เอกสารขออนุมัติผลิตสื่อ"
            description="กรอกข้อมูลด้านล่างเพื่อสร้างเอกสารผลิตสื่อ"
            icon="fas fa-photo-video"
        />
        <form id="create-form" action="{{ route('document.media.create') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-document.approver-form />
            <x-ui.validation-errors />

            <input id="selfApprove" type="hidden" name="selfApprove" value="false">
            <input id="documentCode" type="hidden" name="documentCode" value="MED">

            <div class="card bg-base-100 mb-8 p-6 shadow-xl">
                <x-ui.section-title icon="fas fa-heading" class="mb-2">ชื่องาน</x-ui.section-title>
                <input class="input input-bordered mb-6 w-full" id="title" name="title" type="text" placeholder="ชื่องาน" value="{{ old('title') }}" />

                <x-ui.section-title icon="fas fa-photo-video">ประเภทสื่อ</x-ui.section-title>
                <div class="flex flex-col gap-3">
                    <x-ui.radio-option id="type-sign" name="document_type" value="sign" onchange="selectMediaType('sign')" >
                        ป้าย
                    </x-ui.radio-option>
                    <x-ui.radio-option id="type-brochure" name="document_type" value="brochure" onchange="selectMediaType('brochure')" >
                        โบรชัวร์ / แผ่นพับ
                    </x-ui.radio-option>
                    <x-ui.radio-option id="type-photo-video" name="document_type" value="photo_video" onchange="selectMediaType('photo_video')" >
                        ถ่ายภาพ / วิดีโอ
                    </x-ui.radio-option>
                    <x-ui.radio-option id="type-poster" name="document_type" value="poster" onchange="selectMediaType('poster')" >
                        โปสเตอร์
                    </x-ui.radio-option>
                    <x-ui.radio-option id="type-tent-card" name="document_type" value="tent_card" onchange="selectMediaType('tent_card')" >
                        Tent Card
                    </x-ui.radio-option>
                    <x-ui.radio-option id="type-standee" name="document_type" value="standee" onchange="selectMediaType('standee')" >
                        Standee
                    </x-ui.radio-option>
                    <x-ui.radio-option id="type-other" name="document_type" value="other" onchange="selectMediaType('other')" >
                        อื่นๆ ระบุ
                    </x-ui.radio-option>
                </div>

                <div class="mt-4 hidden" id="other-type-section">
                    <input class="input input-bordered w-full" id="other_text" name="other_text" type="text" placeholder="ระบุประเภทสื่ออื่นๆ" disabled />
                </div>

                <section class="mt-6 hidden" id="sign-section">
                    <x-ui.section-title icon="fas fa-map-signs" class="mb-2">ประเภทป้าย</x-ui.section-title>
                    <div class="flex flex-col gap-4">
                        @foreach (\App\Models\DocumentMedia::signTypeLabels() as $key => $label)
                            <div class="border-base-300 rounded-lg border p-4">
                                <label class="flex cursor-pointer items-start gap-3" for="sign-type-{{ $key }}">
                                    <input class="checkbox checkbox-primary mt-1" id="sign-type-{{ $key }}" type="checkbox" name="sign_types[]" value="{{ $key }}" onchange="toggleSignItem('{{ $key }}')" disabled />
                                    <div class="flex-1">
                                        <div class="flex flex-wrap items-start gap-4">
                                            @if ($imageUrl = \App\Models\DocumentMedia::signTypeReferenceImage($key))
                                                <img
                                                    class="border-base-300 max-h-64 w-48 rounded-md border object-contain"
                                                    src="{{ $imageUrl }}"
                                                    alt="{{ $label }}"
                                                />
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <div class="font-medium">{{ $label }}</div>
                                                <div class="sign-item-fields mt-3 hidden" id="sign-fields-{{ $key }}">
                                                    <textarea class="textarea textarea-bordered w-full" name="sign_details[{{ $key }}]" rows="2" placeholder="รายละเอียดป้ายนี้" disabled></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <x-ui.section-title icon="fas fa-map-marker-alt" class="mb-2 mt-6">สถานที่ติดตั้งป้าย</x-ui.section-title>
                    <input class="input input-bordered w-full" id="sign_location" name="sign_location" type="text" placeholder="สถานที่ติดตั้งป้าย" disabled />
                </section>

                <section class="mt-6 hidden" id="brochure-section">
                    <x-ui.section-title icon="fas fa-book-open" class="mb-2">ขนาด</x-ui.section-title>
                    <div class="flex flex-col gap-3">
                        @foreach (['แผ่นพับ A4 พับ 3', 'โบรชัว A5', 'โบรชัว A4'] as $size)
                            <label class="flex cursor-pointer items-center gap-3">
                                <input class="checkbox checkbox-primary" type="checkbox" name="brochure_sizes[]" value="{{ $size }}" disabled />
                                <span>{{ $size }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-ui.section-title icon="fas fa-print" class="mb-2 mt-6">ประเภท</x-ui.section-title>
                    <div class="flex flex-col gap-3">
                        <x-ui.radio-option id="print-color" name="brochure_print_type" value="พิมพ์สี">พิมพ์สี</x-ui.radio-option>
                        <x-ui.radio-option id="print-bw" name="brochure_print_type" value="พิมพ์ขาวดำ">พิมพ์ขาวดำ</x-ui.radio-option>
                    </div>
                </section>

                <section class="mt-6 hidden" id="photo-section">
                    <x-ui.section-title icon="fas fa-camera" class="mb-2">ลักษณะงาน</x-ui.section-title>
                    <div class="flex flex-col gap-3">
                        <label class="flex cursor-pointer items-center gap-3">
                            <input class="checkbox checkbox-primary" type="checkbox" name="photo_work_types[]" value="ถ่ายภาพนิ่ง" disabled />
                            <span>ถ่ายภาพนิ่ง</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3">
                            <input class="checkbox checkbox-primary" type="checkbox" name="photo_work_types[]" value="ถ่ายวีดีโอ" disabled />
                            <span>ถ่ายวีดีโอ</span>
                        </label>
                    </div>
                    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <x-ui.section-title icon="fas fa-calendar" class="mb-2">วันที่ถ่ายทำ</x-ui.section-title>
                            <input class="input input-bordered w-full" id="photo_date" name="photo_date" type="date" disabled />
                        </div>
                        <div>
                            <x-ui.section-title icon="fas fa-clock" class="mb-2">เวลาถ่ายทำ</x-ui.section-title>
                            <input class="input input-bordered w-full" id="photo_time" name="photo_time" type="text" placeholder="เช่น 09:00 - 12:00" disabled />
                        </div>
                    </div>
                    <x-ui.section-title icon="fas fa-map-marker-alt" class="mb-2 mt-6">สถานที่ถ่ายทำ</x-ui.section-title>
                    <input class="input input-bordered w-full" id="photo_location" name="photo_location" type="text" placeholder="สถานที่ถ่ายทำ" disabled />
                </section>

                <div class="hidden" id="document-additional-info">
                    <div class="divider"></div>

                    <x-ui.section-title icon="fas fa-align-left" class="mb-2">รายละเอียด</x-ui.section-title>
                    <textarea class="textarea textarea-bordered w-full" id="detail" name="detail" rows="4" placeholder="รายละเอียด"></textarea>

                    <x-ui.section-title icon="fas fa-paperclip" class="mb-2 mt-6">ไฟล์เอกสารแนบ / Reference</x-ui.section-title>
                    <x-form.file-dropzone hint="* ใส่เอกสารแนบได้ไม่เกิน 20 ไฟล์" />

                    <x-ui.section-title icon="fas fa-calendar-check" class="mb-2 mt-6">วันที่ต้องการ</x-ui.section-title>
                    <input class="input input-bordered w-full" id="required_date" name="required_date" type="date" value="{{ old('required_date') }}" />

                    <x-ui.section-title icon="fas fa-phone-alt" class="mb-2 mt-6">เบอร์โทรศัพท์ภายในติดต่อกลับ</x-ui.section-title>
                    <input class="input input-bordered w-full" id="document_phone" name="document_phone" type="text" placeholder="เบอร์โทรศัพท์ภายในติดต่อกลับ" value="{{ old('document_phone') }}" />

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
        function selectMediaType(type) {
            $('#document-additional-info').removeClass('hidden');
            $('#selfApprove').val('false');
            $('#documentCode').val('MED');

            $('#other-type-section').toggleClass('hidden', type !== 'other');
            $('#other_text').prop('disabled', type !== 'other');
            if (type !== 'other') {
                $('#other_text').val('');
            }

            const isSign = type === 'sign';
            $('#sign-section').toggleClass('hidden', !isSign);
            if (isSign) {
                $('#sign-section input[type="checkbox"], #sign_location').prop('disabled', false);
            } else {
                $('#sign-section input, #sign-section textarea').prop('disabled', true);
                $('#sign-section input[type="checkbox"]').prop('checked', false);
                $('.sign-item-fields').addClass('hidden');
                $('.sign-item-fields input, .sign-item-fields textarea').val('');
            }

            const isBrochure = type === 'brochure';
            $('#brochure-section').toggleClass('hidden', !isBrochure);
            $('#brochure-section input').prop('disabled', !isBrochure);
            if (!isBrochure) {
                $('#brochure-section input[type="checkbox"]').prop('checked', false);
                $('#brochure-section input[type="radio"]').prop('checked', false);
            }

            const isPhoto = type === 'photo_video';
            $('#photo-section').toggleClass('hidden', !isPhoto);
            $('#photo-section input').prop('disabled', !isPhoto);
            if (!isPhoto) {
                $('#photo-section input[type="checkbox"]').prop('checked', false);
                $('#photo_date, #photo_time, #photo_location').val('');
            }
        }

        function toggleSignItem(key) {
            const checked = $(`#sign-type-${key}`).is(':checked');
            const fields = $(`#sign-fields-${key}`);
            fields.toggleClass('hidden', !checked);
            fields.find('input, textarea').prop('disabled', !checked);
            if (!checked) {
                fields.find('input, textarea').val('');
            }
        }

        function submitForm() {
            event.preventDefault();

            const form = '#create-form';
            const type = $('input[name="document_type"]:checked').val();
            let isValid = true;
            let errorMessage = '';
            let errorField = null;

            if (!$('#title').val()) {
                isValid = false;
                errorMessage = 'กรุณาระบุชื่องาน';
                errorField = '#title';
            } else if (!type) {
                isValid = false;
                errorMessage = 'กรุณาเลือกประเภทสื่อ';
                errorField = 'input[name="document_type"]';
            } else if (type === 'other' && !$('#other_text').val()) {
                isValid = false;
                errorMessage = 'กรุณาระบุประเภทสื่ออื่นๆ';
                errorField = '#other_text';
            } else if (type === 'sign') {
                if ($('input[name="sign_types[]"]:checked').length === 0) {
                    isValid = false;
                    errorMessage = 'กรุณาเลือกประเภทป้ายอย่างน้อย 1 รายการ';
                    errorField = 'input[name="sign_types[]"]';
                } else if (!$('#sign_location').val()) {
                    isValid = false;
                    errorMessage = 'กรุณาระบุสถานที่ติดตั้งป้าย';
                    errorField = '#sign_location';
                }
            } else if (type === 'brochure') {
                if ($('input[name="brochure_sizes[]"]:checked').length === 0) {
                    isValid = false;
                    errorMessage = 'กรุณาเลือกขนาดโบรชัวร์ / แผ่นพับ';
                    errorField = 'input[name="brochure_sizes[]"]';
                } else if (!$('input[name="brochure_print_type"]:checked').val()) {
                    isValid = false;
                    errorMessage = 'กรุณาเลือกประเภทการพิมพ์';
                    errorField = 'input[name="brochure_print_type"]';
                }
            } else if (type === 'photo_video') {
                if ($('input[name="photo_work_types[]"]:checked').length === 0) {
                    isValid = false;
                    errorMessage = 'กรุณาเลือกลักษณะงาน';
                    errorField = 'input[name="photo_work_types[]"]';
                } else if (!$('#photo_date').val()) {
                    isValid = false;
                    errorMessage = 'กรุณาระบุวันที่ถ่ายทำ';
                    errorField = '#photo_date';
                } else if (!$('#photo_time').val()) {
                    isValid = false;
                    errorMessage = 'กรุณาระบุเวลาถ่ายทำ';
                    errorField = '#photo_time';
                } else if (!$('#photo_location').val()) {
                    isValid = false;
                    errorMessage = 'กรุณาระบุสถานที่ถ่ายทำ';
                    errorField = '#photo_location';
                }
            }

            if (isValid && !$('#required_date').val()) {
                isValid = false;
                errorMessage = 'กรุณาระบุวันที่ต้องการ';
                errorField = '#required_date';
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
