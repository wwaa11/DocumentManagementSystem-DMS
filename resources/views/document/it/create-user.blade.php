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
                $('input[name^="users["][name$="[request][windows]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][email]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][other_check]"]').val() === 'true';
            const hasHCRequest = $('input[name^="users["][name$="[request][hclab]"]').val() === 'true';
            const hasPACRequest = $('input[name^="users["][name$="[request][pacs]"]').val() === 'true';
            const hasHeartStreamRequest = $('input[name^="users["][name$="[request][heartstream]"]').val() === 'true';
            const hasRegisterRequest = $('input[name^="users["][name$="[request][register]"]').val() === 'true';

            const doctorHrIT = $('#doctor_hr_it').is(':checked');
            const doctorHrHCLab = $('#doctor_hr_hclab').is(':checked');
            const doctorHrPACS = $('#doctor_hr_pacs').is(':checked');
            const doctorHrHeartStream = $('#doctor_hr_heartstream').is(':checked');
            const doctorHrRegister = $('#doctor_hr_register').is(':checked');

            $('input[name="createIT"]').val(doctorHrIT || hasITRequest ? 'true' : 'false');
            $('input[name="createHC"]').val(doctorHrHCLab || hasHCRequest ? 'true' : 'false');
            $('input[name="createPAC"]').val(doctorHrPACS || hasPACRequest ? 'true' : 'false');
            $('input[name="createHeartStream"]').val(doctorHrHeartStream || hasHeartStreamRequest ? 'true' : 'false');
            $('input[name="createRegister"]').val(doctorHrRegister || hasRegisterRequest ? 'true' : 'false');
        }

        function selectRequestType(type) {
            $("input[name='title']").prop('checked', false);
            $('#user-title-' + type).prop('checked', true);

            if (type === 'edit') {
                $('#user_search').removeClass('hidden');
                $('#doctor_hr').addClass('hidden');
                $('#user_detail').prop('disabled', true);
            } else if (type === 'hr' || type === 'doctor') {
                $('#user_search').addClass('hidden');
                $('#doctor_hr').removeClass('hidden');
                $('#user_detail').prop('disabled', false);
            }

            $('#doctor_hr_it').prop('checked', false);
            $('#doctor_hr_hclab').prop('checked', false);
            $('#doctor_hr_pacs').prop('checked', false);
            $('#doctor_hr_heartstream').prop('checked', false);
            $('#doctor_hr_register').prop('checked', false);

            $('input[name=createIT]').val('false');
            $('input[name=createHC]').val('false');
            $('input[name=createPAC]').val('false');
            $('input[name=createHeartStream]').val('false');
            $('input[name=createRegister]').val('false');
            $('#user_result_append').html('');
            $('#document-addtional-info').removeClass('hidden');
        }
    </script>
@endpush
