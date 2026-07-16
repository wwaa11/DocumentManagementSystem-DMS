{{-- เลขาแพทย์: doctor name + systems + add --}}
<div class="border-accent hidden rounded border-2 border-dashed p-6" id="doctor_section">
    <h4 class="card-title text-primary mb-4 flex items-center text-xl">
        <i class="fas fa-user-md mr-2"></i> ข้อมูลแพทย์
    </h4>

    <div class="form-control mb-6">
        <label class="label">
            <span class="label-text">ชื่อแพทย์</span>
        </label>
        <input class="input input-bordered w-full" id="doctor_name" type="text" placeholder="ชื่อแพทย์" />
    </div>

    <h4 class="card-title text-primary mb-3 flex items-center text-xl">
        <i class="fas fa-key mr-2"></i> สิทธิการใช้งานที่ต้องการ
    </h4>

    <div class="mb-3 grid grid-cols-1 gap-4 md:grid-cols-3">
        <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
            <input class="checkbox checkbox-primary" id="doctor_request_mkyt" type="checkbox" onchange="toggleDoctorMkytOptions()" />
            <span class="label-text">MKyT</span>
        </label>
        <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
            <input class="checkbox checkbox-primary" id="doctor_request_windows" type="checkbox" />
            <span class="label-text">Windows</span>
        </label>
        <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
            <input class="checkbox checkbox-primary" id="doctor_request_email" type="checkbox" />
            <span class="label-text">Email</span>
        </label>
        <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
            <input class="checkbox checkbox-primary" id="doctor_request_hclab" type="checkbox" />
            <span class="label-text">HCLAB</span>
        </label>
        <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
            <input class="checkbox checkbox-primary" id="doctor_request_pacs" type="checkbox" />
            <span class="label-text">PACS</span>
        </label>
        <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
            <input class="checkbox checkbox-primary" id="doctor_request_heartstream" type="checkbox" />
            <span class="label-text">HeartStream</span>
        </label>
        <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
            <input class="checkbox checkbox-primary" id="doctor_request_register" type="checkbox" />
            <span class="label-text">Register</span>
        </label>
    </div>

    <div class="border-primary/30 bg-base-200/50 mb-6 hidden rounded-lg border p-4" id="doctor_mkyt_options">
        <h5 class="text-primary mb-3 font-medium">MKyT Role</h5>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-base-300 bg-base-100 p-3">
                <input class="checkbox checkbox-primary" id="doctor_mkyt_surgeon" type="checkbox" />
                <span class="label-text font-medium">Surgeon (Surgeon/Assistant Surgeon)</span>
            </label>

            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-base-300 bg-base-100 p-3">
                <input class="checkbox checkbox-primary" id="doctor_mkyt_radiologist" type="checkbox" />
                <span class="label-text font-medium">Radiologist</span>
            </label>

            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-base-300 bg-base-100 p-3">
                <input class="checkbox checkbox-primary" id="doctor_mkyt_anaesthetist" type="checkbox" />
                <span class="label-text font-medium">Anaesthetist (Anaesthetist/Assistant Anesthetist)</span>
            </label>

            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-base-300 bg-base-100 p-3">
                <input class="checkbox checkbox-primary" id="doctor_mkyt_checkup" type="checkbox" />
                <span class="label-text font-medium">Check up</span>
            </label>

            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-base-300 bg-base-100 p-3">
                <input class="checkbox checkbox-primary" id="doctor_mkyt_careprovider" type="checkbox" />
                <span class="label-text font-medium">Careprovider</span>
            </label>
        </div>
    </div>

    <div class="form-control mb-6">
        <label class="label">
            <span class="fas fa-info-circle"></span>
            <span class="label-text">รายละเอียดเพิ่มเติม</span>
        </label>
        <textarea class="textarea textarea-bordered h-24 w-full" id="doctor_request_detail" placeholder="รายละเอียดเพิ่มเติม"></textarea>
    </div>

    <div class="mt-6 flex justify-end">
        <button class="btn btn-accent w-full gap-2" onclick="appendDoctorData()" type="button">
            <i class="fas fa-plus"></i> เพิ่มรายการ
        </button>
    </div>

    <div class="mt-6" id="doctor_result_append"></div>
</div>

{{-- ฝ่ายบุคคล: detail only --}}
<div class="border-accent hidden rounded border-2 border-dashed p-6" id="hr_section">
    <div class="form-control">
        <label class="label mb-3">
            <span class="fas fa-info-circle"></span>
            <span class="label-text">รายละเอียด</span>
        </label>
        <textarea class="textarea textarea-bordered focus:outline-primary w-full focus:border-0" id="user_detail" rows="20" name="user_detail" placeholder="รายละเอียด" disabled></textarea>
    </div>
</div>

@push('scripts')
    <script>
        let doctorItemCounter = 0;

        function toggleDoctorMkytOptions() {
            const isChecked = $('#doctor_request_mkyt').is(':checked');
            $('#doctor_mkyt_options').toggleClass('hidden', !isChecked);
            if (!isChecked) {
                resetDoctorMkytRoles();
            }
        }

        function resetDoctorMkytRoles() {
            $('#doctor_mkyt_surgeon').prop('checked', false);
            $('#doctor_mkyt_radiologist').prop('checked', false);
            $('#doctor_mkyt_anaesthetist').prop('checked', false);
            $('#doctor_mkyt_checkup').prop('checked', false);
            $('#doctor_mkyt_careprovider').prop('checked', false);
        }

        function resetDoctorForm() {
            $('#doctor_name').val('');
            $('#doctor_request_mkyt').prop('checked', false);
            $('#doctor_request_windows').prop('checked', false);
            $('#doctor_request_email').prop('checked', false);
            $('#doctor_request_hclab').prop('checked', false);
            $('#doctor_request_pacs').prop('checked', false);
            $('#doctor_request_heartstream').prop('checked', false);
            $('#doctor_request_register').prop('checked', false);
            $('#doctor_request_detail').val('');
            $('#doctor_mkyt_options').addClass('hidden');
            resetDoctorMkytRoles();
        }

        function removeDoctor(containerId) {
            try {
                $(`#${containerId}`).remove();
                updateCreateFlags();
                Swal.fire({
                    icon: 'success',
                    title: 'ลบรายการเรียบร้อย',
                    showConfirmButton: false,
                    timer: 1500,
                });
            } catch (e) {
                console.error('Error removing doctor:', e);
            }
        }

        function appendDoctorData() {
            const name = $('#doctor_name').val().trim();
            const mkyt = $('#doctor_request_mkyt').is(':checked');
            const windows = $('#doctor_request_windows').is(':checked');
            const email = $('#doctor_request_email').is(':checked');
            const hclab = $('#doctor_request_hclab').is(':checked');
            const pacs = $('#doctor_request_pacs').is(':checked');
            const heartstream = $('#doctor_request_heartstream').is(':checked');
            const register = $('#doctor_request_register').is(':checked');
            const detail = $('#doctor_request_detail').val();

            const surgeon = $('#doctor_mkyt_surgeon').is(':checked');
            const radiologist = $('#doctor_mkyt_radiologist').is(':checked');
            const anaesthetist = $('#doctor_mkyt_anaesthetist').is(':checked');
            const checkup = $('#doctor_mkyt_checkup').is(':checked');
            const careprovider = $('#doctor_mkyt_careprovider').is(':checked');

            if (name === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'กรุณาใส่ชื่อแพทย์',
                    timer: 1000,
                    showConfirmButton: false,
                });
                return;
            }

            if (!mkyt && !windows && !email && !hclab && !pacs && !heartstream && !register) {
                Swal.fire({
                    icon: 'error',
                    title: 'กรุณาเลือกระบบที่ต้องการ',
                    timer: 1000,
                    showConfirmButton: false,
                });
                return;
            }

            if (mkyt && !surgeon && !radiologist && !anaesthetist && !checkup && !careprovider) {
                Swal.fire({
                    icon: 'error',
                    title: 'กรุณาเลือก MKyT Role',
                    timer: 1000,
                    showConfirmButton: false,
                });
                return;
            }

            doctorItemCounter += 1;
            const itemKey = `doctor_${doctorItemCounter}`;
            const containerId = `doctor-item-${doctorItemCounter}`;

            const mkytRoles = [];
            if (surgeon) {
                mkytRoles.push('Surgeon (Surgeon/Assistant Surgeon)');
            }
            if (radiologist) {
                mkytRoles.push('Radiologist');
            }
            if (anaesthetist) {
                mkytRoles.push('Anaesthetist (Anaesthetist/Assistant Anesthetist)');
            }
            if (checkup) {
                mkytRoles.push('Check up');
            }
            if (careprovider) {
                mkytRoles.push('Careprovider');
            }

            const systemsRequested = [
                mkyt ? `MKyT${mkytRoles.length ? ': ' + mkytRoles.join(', ') : ''}` : '',
                windows ? 'Windows' : '',
                email ? 'Email' : '',
                hclab ? 'HCLAB' : '',
                pacs ? 'PACS' : '',
                heartstream ? 'HeartStream' : '',
                register ? 'Register' : '',
            ].filter(Boolean).join(', ');

            $('#doctor_result_append').append(`
                <div class="bg-secondary text-white p-3 rounded-lg mb-2 doctor-item" id="${containerId}" data-doctor-key="${itemKey}">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="font-medium">${name}</div>
                            <div class="text-sm mt-1">
                                <span class="font-medium">ระบบที่ขอ:</span> ${systemsRequested || 'ไม่ได้ระบุ'}
                            </div>
                            ${detail ? `<div class="text-sm mt-1"><span class="font-medium">รายละเอียด:</span> ${detail}</div>` : ''}
                        </div>
                        <button type="button" class="btn btn-sm btn-error" onclick="removeDoctor('${containerId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <input type="hidden" name="doctors[${itemKey}][name]" value="${name}" />
                    <input type="hidden" name="doctors[${itemKey}][request][mkyt]" value="${mkyt}" />
                    <input type="hidden" name="doctors[${itemKey}][request][windows]" value="${windows}" />
                    <input type="hidden" name="doctors[${itemKey}][request][email]" value="${email}" />
                    <input type="hidden" name="doctors[${itemKey}][request][hclab]" value="${hclab}" />
                    <input type="hidden" name="doctors[${itemKey}][request][pacs]" value="${pacs}" />
                    <input type="hidden" name="doctors[${itemKey}][request][heartstream]" value="${heartstream}" />
                    <input type="hidden" name="doctors[${itemKey}][request][register]" value="${register}" />
                    <input type="hidden" name="doctors[${itemKey}][request][mkyt_surgeon]" value="${surgeon}" />
                    <input type="hidden" name="doctors[${itemKey}][request][mkyt_radiologist]" value="${radiologist}" />
                    <input type="hidden" name="doctors[${itemKey}][request][mkyt_anaesthetist]" value="${anaesthetist}" />
                    <input type="hidden" name="doctors[${itemKey}][request][mkyt_checkup]" value="${checkup}" />
                    <input type="hidden" name="doctors[${itemKey}][request][mkyt_careprovider]" value="${careprovider}" />
                    <input type="hidden" name="doctors[${itemKey}][request][detail]" value="${detail}" />
                </div>
            `);

            resetDoctorForm();
            updateCreateFlags();
        }
    </script>
@endpush
