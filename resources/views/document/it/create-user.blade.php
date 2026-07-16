<section class="mt-6 hidden" id="user-section">
    <x-ui.section-title icon="fas fa-user-cog" class="mb-3">
        ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม
    </x-ui.section-title>
    <div class="mb-6 flex flex-row gap-3">
        <x-ui.radio-option id="user-title-edit" name="title" value="ขอแก้ไขสิทธิการใช้งาน" onchange="selectRequestType('edit')">
            ขอแก้ไขสิทธิการใช้งาน
        </x-ui.radio-option>
        <x-ui.radio-option id="user-title-doctor" name="title" value="เลขาแพทย์" onchange="selectRequestType('doctor')">
            เลขาแพทย์
        </x-ui.radio-option>
        <x-ui.radio-option id="user-title-hr" name="title" value="ฝ่ายบุคคล" onchange="selectRequestType('hr')">
            ฝ่ายบุคคล
        </x-ui.radio-option>
    </div>

    @include('document.it.create-user-sub.editRight')
    @include('document.it.create-user-sub.doctor_hr')
</section>

@push('scripts')
    <script>
        function updateCreateFlags() {
            const hasITRequest = $('input[name^="users["][name$="[request][ssb]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][mkyt]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][windows]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][email]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][other_check]"]').val() === 'true';
            const hasHCRequest = $('input[name^="users["][name$="[request][hclab]"]').val() === 'true';
            const hasPACRequest = $('input[name^="users["][name$="[request][pacs]"]').val() === 'true';
            const hasHeartStreamRequest = $('input[name^="users["][name$="[request][heartstream]"]').val() === 'true';
            const hasRegisterRequest = $('input[name^="users["][name$="[request][register]"]').val() === 'true';

            const doctorHasIT = $('input[name^="doctors["][name$="[request][mkyt]"]').filter(function () {
                return $(this).val() === 'true';
            }).length > 0 ||
                $('input[name^="doctors["][name$="[request][windows]"]').filter(function () {
                    return $(this).val() === 'true';
                }).length > 0 ||
                $('input[name^="doctors["][name$="[request][email]"]').filter(function () {
                    return $(this).val() === 'true';
                }).length > 0;
            const doctorHasHC = $('input[name^="doctors["][name$="[request][hclab]"]').filter(function () {
                return $(this).val() === 'true';
            }).length > 0;
            const doctorHasPAC = $('input[name^="doctors["][name$="[request][pacs]"]').filter(function () {
                return $(this).val() === 'true';
            }).length > 0;
            const doctorHasHeartStream = $('input[name^="doctors["][name$="[request][heartstream]"]').filter(function () {
                return $(this).val() === 'true';
            }).length > 0;
            const doctorHasRegister = $('input[name^="doctors["][name$="[request][register]"]').filter(function () {
                return $(this).val() === 'true';
            }).length > 0;

            const isHr = $('input[name="title"]:checked').val() === 'ฝ่ายบุคคล';

            $('input[name="createIT"]').val(isHr || doctorHasIT || hasITRequest ? 'true' : 'false');
            $('input[name="createHC"]').val(!isHr && (doctorHasHC || hasHCRequest) ? 'true' : 'false');
            $('input[name="createPAC"]').val(!isHr && (doctorHasPAC || hasPACRequest) ? 'true' : 'false');
            $('input[name="createHeartStream"]').val(!isHr && (doctorHasHeartStream || hasHeartStreamRequest) ? 'true' : 'false');
            $('input[name="createRegister"]').val(!isHr && (doctorHasRegister || hasRegisterRequest) ? 'true' : 'false');
        }

        function selectRequestType(type) {
            $("input[name='title']").prop('checked', false);
            $('#user-title-' + type).prop('checked', true);

            $('#user_search').addClass('hidden');
            $('#doctor_section').addClass('hidden');
            $('#hr_section').addClass('hidden');
            $('#user_detail').prop('disabled', true);
            $('#send_to_it_admin').addClass('hidden');

            if (type === 'edit') {
                $('#user_search').removeClass('hidden');
            } else if (type === 'doctor') {
                $('#doctor_section').removeClass('hidden');
            } else if (type === 'hr') {
                $('#hr_section').removeClass('hidden');
                $('#user_detail').prop('disabled', false);
                $('#send_to_it_admin').removeClass('hidden');
            }

            $('input[name=createIT]').val(type === 'hr' ? 'true' : 'false');
            $('input[name=createHC]').val('false');
            $('input[name=createPAC]').val('false');
            $('input[name=createHeartStream]').val('false');
            $('input[name=createRegister]').val('false');
            $('#user_result_append').html('');
            $('#doctor_result_append').html('');
            if (typeof resetDoctorForm === 'function') {
                resetDoctorForm();
            }
            $('#document-addtional-info').removeClass('hidden');
            updateCreateFlags();
        }
    </script>
@endpush
