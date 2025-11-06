<section class="mt-6 hidden" id="user-section">

    <h3 class="card-title text-primary mb-3 flex items-center text-xl">
        <i class="fas fa-user-cog mr-2"></i> ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม
    </h3>
    <div class="mb-6 flex flex-row gap-3">
        <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="user-title-edit">
            <div class="flex items-center">
                <input class="radio radio-primary mr-3" id="user-title-edit" value="ขอแก้ไขสิทธิการใช้งาน" type="radio" name="title" onchange="selectRequestType('edit')" />
                <div>
                    <h4 class="font-medium">ขอแก้ไขสิทธิการใช้งาน</h4>
                </div>
            </div>
        </label>
        <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="user-title-doctor">
            <div class="flex items-center">
                <input class="radio radio-primary mr-3" id="user-title-doctor" value="เลขาแพทย์" type="radio" name="title" onchange="selectRequestType('doctor')" />
                <div>
                    <h4 class="font-medium">เลขาแพทย์</h4>
                </div>
            </div>
        </label>
        <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="user-title-hr">
            <div class="flex items-center">
                <input class="radio radio-primary mr-3" id="user-title-hr" value="ฝ่ายบุคคล" type="radio" name="title" onchange="selectRequestType('hr')" />
                <div>
                    <h4 class="font-medium">ฝ่ายบุคคล</h4>
                </div>
            </div>
        </label>
    </div>

    @include("document.it.create-user-sub.editRight")

    @include("document.it.create-user-sub.doctor_hr")
</section>

@push("scripts")
    <script>
        function updateCreateFlags() {
            // Check Edit request checkboxes
            const hasITRequest = $('input[name^="users["][name$="[request][ssb]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][windows]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][email]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][other_check]"]').val() === 'true';
            const hasHCRequest = $('input[name^="users["][name$="[request][hclab]"]').val() === 'true';
            const hasPACRequest = $('input[name^="users["][name$="[request][pacs]"]').val() === 'true';
            const hasHeartStreamRequest = $('input[name^="users["][name$="[request][heartstream]"]').val() === 'true';
            const hasRegisterRequest = $('input[name^="users["][name$="[request][register]"]').val() === 'true';

            // Check doctor_hr checkboxes
            const doctorHrIT = $('#doctor_hr_it').is(':checked');
            const doctorHrHCLab = $('#doctor_hr_hclab').is(':checked');
            const doctorHrPACS = $('#doctor_hr_pacs').is(':checked');
            const doctorHrHeartStream = $('#doctor_hr_heartstream').is(':checked');
            const doctorHrRegister = $('#doctor_hr_register').is(':checked');

            // Update hidden inputs
            $('input[name="createIT"]').val(doctorHrIT || hasITRequest ? 'true' : 'false');
            $('input[name="createHC"]').val(doctorHrHCLab || hasHCRequest ? 'true' : 'false');
            $('input[name="createPAC"]').val(doctorHrPACS || hasPACRequest ? 'true' : 'false');
            $('input[name="createHeartStream"]').val(doctorHrHeartStream || hasHeartStreamRequest ? 'true' : 'false');
            $('input[name="createRegister"]').val(doctorHrRegister || hasRegisterRequest ? 'true' : 'false');
        }

        function selectRequestType(type) {
            // Remove Radio check
            $("input[name='title']").prop("checked", false);
            // Check Radio with type
            $('#user-title-' + type).prop("checked", true);

            // Show hide form
            if (type === "edit") {
                // Show Edit Form
                $("#user_search").removeClass("hidden");
                // Hide Hr and Doctor Form
                $("#doctor_hr").addClass("hidden");
                $("#user_detail").prop("disabled", true);
            } else if (type === "hr" || type === "doctor") {
                // Hide Edit Form
                $("#user_search").addClass("hidden");
                // Show Hr and Doctor Form
                $("#doctor_hr").removeClass("hidden");
                $("#user_detail").prop("disabled", false);
            }

            // Remove Radio check for HR and Doctor
            $('#doctor_hr_it').prop('checked', false);
            $('#doctor_hr_hclab').prop('checked', false);
            $('#doctor_hr_pacs').prop('checked', false);
            $('#doctor_hr_heartstream').prop('checked', false);
            $('#doctor_hr_register').prop('checked', false);

            // Clear create flags
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
