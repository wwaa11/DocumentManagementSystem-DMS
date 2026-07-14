@extends('layouts.app')
@section('content')
    <div class="mx-auto max-w-5xl">
        <x-document.page-header
            title="แจ้งงาน/สนับสนุนการทำงาน IT"
            description="กรอกข้อมูลด้านล่างเพื่อสร้างเอกสารใหม่"
            icon="fas fa-file-alt"
        />
        <form id="create-form" action="{{ route('document.it.create') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-document.approver-form />
            <x-ui.validation-errors />

            <input type="hidden" name="main_document_type" value="false" />
            <input type="hidden" name="createIT" value="false" />
            <input type="hidden" name="createHC" value="false" />
            <input type="hidden" name="createPAC" value="false" />
            <input type="hidden" name="createHeartStream" value="false" />
            <input type="hidden" name="createRegister" value="false" />
            <input type="hidden" name="createBorrow" value="false" />

            <input id="selfApprove" type="hidden" name="selfApprove" value="true">
            <input id="isHardware" type="hidden" name="isHardware" value="false">
            <input id="documentCode" type="hidden" name="documentCode" value="">

            <div class="card bg-base-100 mb-8 p-6 shadow-xl">
                <x-ui.section-title icon="fas fa-file-alt">ประเภทเอกสาร</x-ui.section-title>
                <div class="flex flex-col gap-3">
                    <x-ui.radio-option
                        id="type-user"
                        name="document_type"
                        value="user"
                        onchange="selectDocType('user')"
                        hint="*ต้องการขออนุมัติจากแผนก"
                    >
                        ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม
                    </x-ui.radio-option>

                    <x-ui.radio-option
                        id="type-support"
                        name="document_type"
                        value="support"
                        onchange="selectDocType('support')"
                    >
                        ขอแจ้งงาน/สนับสนุนการทำงาน
                    </x-ui.radio-option>

                    <x-ui.radio-option
                        id="type-borrow"
                        name="document_type"
                        value="borrow"
                        onchange="selectDocType('borrow')"
                    >
                        ขอยืมอุปกรณ์
                    </x-ui.radio-option>
                </div>

                @include('document.it.create-user')
                @include('document.it.create-support')
                @include('document.it.create-borrow')

                <div class="hidden" id="document-addtional-info">
                    <div class="divider"></div>

                    <x-ui.section-title icon="fas fa-paperclip" class="mb-2">
                        เอกสารแนบ (ถ้ามี)
                    </x-ui.section-title>
                    <x-form.file-dropzone hint="* ใส่เอกสารแนบได้ไม่เกิน 20 ไฟล์" />

                    <div id="send_to_it_admin">
                        <x-ui.section-title icon="fas fa-user-shield" class="mb-2 mt-3">
                            ส่งถึงแผนก IT
                        </x-ui.section-title>
                        <select class="select select-bordered w-full" name="document_admin">
                            <option selected disabled>โปรดระบุ</option>
                            @foreach ($it_admins as $it_admin)
                                <option value="{{ $it_admin->userid }}">{{ $it_admin->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-ui.section-title icon="fas fa-phone-alt" class="mb-2 mt-6">
                        เบอร์โทรศัพท์ภายในติดต่อกลับ
                    </x-ui.section-title>
                    <input class="input input-bordered w-full" id="document_phone" name="document_phone" type="text" placeholder="เบอร์โทรศัพท์ภายในติดต่อกลับ" />

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
        function selectDocType(document_type) {
            if (document_type === 'user') {
                $('#send_to_it_admin').addClass('hidden');
                $('#type-user').prop('checked', true);
                $('#user-section').removeClass('hidden');
                $('#support-section').addClass('hidden');
                $('#borrow-section').addClass('hidden');
                $('#support_detail').prop('disabled', true);
                setDataApprove('user', false, 'ITU');

                $('input[name="createIT"]').val('false');
                $('input[name="createHC"]').val('false');
                $('input[name="createPAC"]').val('false');
                $('input[name="createHeartStream"]').val('false');
                $('input[name="createRegister"]').val('false');
                $('input[name="createBorrow"]').val('false');
            } else if (document_type === 'support') {
                $('#send_to_it_admin').removeClass('hidden');
                $('#type-support').prop('checked', true);
                $('#user-section').addClass('hidden');
                $('#support-section').removeClass('hidden');
                $('#borrow-section').addClass('hidden');
                $('#support_detail').prop('disabled', false);

                $('input[name="createIT"]').val('true');
                $('input[name="createHC"]').val('false');
                $('input[name="createPAC"]').val('false');
                $('input[name="createHeartStream"]').val('false');
                $('input[name="createRegister"]').val('false');
                $('input[name="createBorrow"]').val('false');
            } else if (document_type === 'borrow') {
                $('#send_to_it_admin').removeClass('hidden');
                $('#type-borrow').prop('checked', true);
                $('#user-section').addClass('hidden');
                $('#support-section').addClass('hidden');
                $('#support_detail').prop('disabled', true);
                $('#borrow-section').removeClass('hidden');
                setDataApprove('borrow', false, 'ITB');

                $('input[name="createIT"]').val('false');
                $('input[name="createHC"]').val('false');
                $('input[name="createPAC"]').val('false');
                $('input[name="createHeartStream"]').val('false');
                $('input[name="createRegister"]').val('false');
                $('input[name="createBorrow"]').val('true');
            }
        }

        function setDataApprove(type, isSelfApprove, code, isHardware = false) {
            $('input[name="main_document_type"]').val(type);
            $('#selfApprove').val(isSelfApprove);
            $('#documentCode').val(code);
            $('input[name="isHardware"]').val(isHardware);
        }

        function submitForm() {
            event.preventDefault();

            const type = $('input[name="document_type"]:checked').val();
            if (!type) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกประเภทเอกสาร',
                    confirmButtonText: 'ตกลง',
                    buttonsStyling: false,
                    customClass: { confirmButton: 'btn btn-primary' },
                });
                return;
            }

            let isValid = true;
            let errorMessage = '';

            if (!$('#document_phone').val()) {
                isValid = false;
                errorMessage = 'กรุณาระบุเบอร์โทรศัพท์ภายใน';
            }

            if (isValid) {
                if (type === 'user') {
                    const title = $('input[name="title"]:checked').val();
                    if (!title) {
                        isValid = false;
                        errorMessage = 'กรุณาเลือกหัวข้อขอรหัสผู้ใช้งาน';
                    } else if (title === 'เลขาแพทย์' || title === 'ฝ่ายบุคคล') {
                        const hasSystem = $('#doctor_hr_it').is(':checked') ||
                            $('#doctor_hr_hclab').is(':checked') ||
                            $('#doctor_hr_pacs').is(':checked') ||
                            $('#doctor_hr_heartstream').is(':checked') ||
                            $('#doctor_hr_register').is(':checked');
                        if (!hasSystem) {
                            isValid = false;
                            errorMessage = 'กรุณาเลือกอย่างน้อย 1 ระบบ';
                        } else if (!$('#user_detail').val()) {
                            isValid = false;
                            errorMessage = 'กรุณากรอกรายละเอียด';
                        }
                    }
                } else if (type === 'support') {
                    const title = $('input[name="title"]:checked').val();
                    if (!title) {
                        isValid = false;
                        errorMessage = 'กรุณาเลือกประเภทงานที่ต้องการแจ้ง';
                    } else if (title === 'OTHER' && !$('#title_other_text').val()) {
                        isValid = false;
                        errorMessage = 'กรุณาระบุประเภทงานอื่นๆ';
                    } else if (['HARDWARE', 'SOFTWARE', 'SSB', 'HIS', 'ERP'].includes(title)) {
                        const requestDetail = $('input[name="request_type_detail"]:checked').val();
                        if (!requestDetail) {
                            isValid = false;
                            errorMessage = 'กรุณาเลือกรายละเอียดการขอ';
                        } else if (requestDetail === 'อื่นๆ') {
                            if (title === 'HARDWARE' && !$('#request_other_hardware').val()) {
                                isValid = false;
                                errorMessage = 'กรุณาระบุรายละเอียดอื่นๆ (Hardware)';
                            } else if (title === 'SOFTWARE' && !$('#request_other_software').val()) {
                                isValid = false;
                                errorMessage = 'กรุณาระบุรายละเอียดอื่นๆ (Software)';
                            } else if (title === 'SSB' && !$('#request_other_ssb').val()) {
                                isValid = false;
                                errorMessage = 'กรุณาระบุรายละเอียดอื่นๆ (SSB)';
                            }
                        }
                    }

                    if (isValid && !$('#support_detail').val()) {
                        isValid = false;
                        errorMessage = 'กรุณากรอกรายละเอียดเพิ่มเติม';
                    }
                } else if (type === 'borrow') {
                    const borrowType = $('input[name="borrow_type"]:checked').val();
                    if (!borrowType) {
                        isValid = false;
                        errorMessage = 'กรุณาเลือกประเภทอุปกรณ์ที่ต้องการยืม';
                    } else if (borrowType === 'OTHER' && !$('#borrow_other_text').val()) {
                        isValid = false;
                        errorMessage = 'กรุณาระบุประเภทอุปกรณ์อื่นๆ';
                    }

                    if (isValid && !$('input[name="return_date"]').val()) {
                        isValid = false;
                        errorMessage = 'กรุณาระบุวันที่คาดว่าจะคืนอุปกรณ์';
                    }

                    if (isValid && !$('#borrow_detail').val()) {
                        isValid = false;
                        errorMessage = 'กรุณากรอกรายละเอียดเพิ่มเติม';
                    }
                }
            }

            if (!isValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    text: errorMessage,
                    confirmButtonText: 'ตกลง',
                    buttonsStyling: false,
                    customClass: { confirmButton: 'btn btn-primary' },
                });
                return;
            }

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
