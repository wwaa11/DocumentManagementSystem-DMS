<section class="mt-6 hidden" id="user-section">

    <h3 class="card-title text-primary mb-3 flex items-center text-xl">
        <i class="fas fa-user-cog mr-2"></i> ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม
    </h3>
    <div class="mb-6 flex flex-row gap-3">
        <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="user-title-add">
            <div class="flex items-center">
                <input class="radio radio-primary mr-3" id="user-title-add" value="ขอเพิ่ม" type="radio" name="title" onchange="selectRequestType('add')" />
                <div>
                    <h4 class="font-medium">ขอเพิ่ม</h4>
                </div>
            </div>
        </label>
        <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="user-title-remove">
            <div class="flex items-center">
                <input class="radio radio-primary mr-3" id="user-title-remove" value="ขอลด" type="radio" name="title" onchange="selectRequestType('remove')" />
                <div>
                    <h4 class="font-medium">ขอลด</h4>
                </div>
            </div>
        </label>
        <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="user-title-edit">
            <div class="flex items-center">
                <input class="radio radio-primary mr-3" id="user-title-edit" value="ขอแก้ไข" type="radio" name="title" onchange="selectRequestType('edit')" />
                <div>
                    <h4 class="font-medium">ขอแก้ไข</h4>
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

    <input type="hidden" name="createIT" value="false" />
    <input type="hidden" name="createHC" value="false" />
    <input type="hidden" name="createPAC" value="false" />

    <!-- Standard Request Form -->
    <div class="border-accent hidden rounded border-2 border-dashed p-6" id="user_search">
        <h4 class="card-title text-primary mb-4 flex items-center text-xl">
            <i class="fas fa-search mr-2"></i> ค้นหาข้อมูลพนักงาน
        </h4>

        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">รหัสพนักงาน</span>
                </label>
                <div class="join flex">
                    <button class="btn btn-square btn-warning join-item hidden" id="reset-search-btn" type="button" onclick="resetUserForm()">
                        <i class="fas fa-redo"></i>
                    </button>
                    <input class="join-item input input-bordered w-full" id="request_userid" placeholder="รหัสพนักงาน" type="text" />
                    <button class="btn btn-square btn-primary join-item" type="button" onclick="searchUserData()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text">ชื่อ-นามสกุล</span>
                </label>
                <input class="input input-bordered bg-base-200 w-full" id="request_name" placeholder="ชื่อ-นามสกุล" type="text" readonly />
                <input id="request_name_en" type="hidden" />
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text">แผนก</span>
                </label>
                <input class="input input-bordered bg-base-200 w-full" id="request_department" placeholder="แผนก" type="text" readonly />
            </div>
        </div>

        <h4 class="card-title text-primary mb-3 flex items-center text-xl">
            <i class="fas fa-key mr-2"></i> สิทธิ์การใช้งานที่ต้องการ
        </h4>

        <div class="mb-3 grid grid-cols-1 gap-4 md:grid-cols-3">
            <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
                <input class="checkbox checkbox-primary" id="request_ssb" type="checkbox" />
                <span class="label-text">SSB</span>
            </label>
            <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
                <input class="checkbox checkbox-primary" id="request_windows" type="checkbox" />
                <span class="label-text">Windows</span>
            </label>
            <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
                <input class="checkbox checkbox-primary" id="request_email" type="checkbox" />
                <span class="label-text">Email</span>
            </label>
            <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
                <input class="checkbox checkbox-primary" id="request_hclab" type="checkbox" />
                <span class="label-text">HCLAB</span>
            </label>
            <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
                <input class="checkbox checkbox-primary" id="request_pacs" type="checkbox" />
                <span class="label-text">PACS</span>
            </label>
            <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
                <input class="checkbox checkbox-primary" id="request_other_check" type="checkbox" />
                <span class="label-text">อื่นๆ:</span>
                <input class="input input-bordered input-sm flex-grow" id="request_other" type="text" />
            </label>
        </div>

        <div class="form-control mb-6 px-3">
            <label class="label">
                <span class="fas fa-info-circle"></span>
                <span class="label-text">รายละเอียดเพิ่มเติม</span>
            </label>
            <textarea class="textarea textarea-bordered h-24 w-full" id="request_detail" placeholder="รายละเอียดเพิ่มเติม"></textarea>
        </div>

        <div class="mt-6 flex justify-end">
            <button class="btn btn-accent w-full gap-2" onclick="appendUserData()" type="button">
                <i class="fas fa-plus"></i> เพิ่มรายการ
            </button>
        </div>

        <div class="mt-6" id="user_result_append"></div>
    </div>

    <!-- Doctor/HR Request Form -->
    <div class="border-accent hidden rounded border-2 border-dashed p-6" id="doctor_hr">
        <div class="flex flex-row gap-3">
            <div class="card flex-1 shadow-xl" onclick="toggleCjeckID('#doctor_hr_it')">
                <div class="card-body">
                    <h2 class="card-title text-accent">SSB, Windows, Email</h2>
                    <div class="form-control">
                        <label class="label cursor-pointer">
                            <span class="label-text">มีการขอ IT Request</span>
                            <input class="checkbox checkbox-accent" id="doctor_hr_it" onclick="toggleCjeckID('#doctor_hr_it')" type="checkbox" value="false" />
                        </label>
                    </div>
                </div>
            </div>

            <div class="card flex-1 shadow-xl" onclick="toggleCjeckID('#doctor_hr_hclab')">
                <div class="card-body">
                    <h2 class="card-title text-accent">HCLAB</h2>
                    <div class="form-control">
                        <label class="label cursor-pointer">
                            <span class="label-text">มีการขอ HC Request</span>
                            <input class="checkbox checkbox-accent" id="doctor_hr_hclab" onclick="toggleCjeckID('#doctor_hr_hclab')" type="checkbox" value="false" />
                        </label>
                    </div>
                </div>
            </div>

            <div class="card flex-1 shadow-xl" onclick="toggleCjeckID('#doctor_hr_pacs')">
                <div class="card-body">
                    <h2 class="card-title text-accent">PACS</h2>
                    <div class="form-control">
                        <label class="label cursor-pointer">
                            <span class="label-text">มีการขอ PAC Request</span>
                            <input class="checkbox checkbox-accent" id="doctor_hr_pacs" onclick="toggleCjeckID('#doctor_hr_pacs')" type="checkbox" value="false" />
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-control mt-6">
            <label class="label mb-3">
                <span class="fas fa-info-circle"></span>
                <span class="label-text">รายละเอียด</span>
            </label>
            <textarea class="textarea textarea-bordered focus:outline-primary w-full focus:border-0" id="user_detail" rows="20" name="user_detail" placeholder="รายละเอียด"></textarea>
        </div>

    </div>
</section>

@push("scripts")
    <script>
        function toggleCjeckID(id) {
            $(id).prop("checked", !$(id).prop("checked"));
            updateCreateFlags();
        }

        function updateCreateFlags() {
            const createIT = $("#doctor_hr_it").prop("checked");
            const createHC = $("#doctor_hr_hclab").prop("checked");
            const createPACS = $("#doctor_hr_pacs").prop("checked");

            $("input[name='createIT']").val(createIT);
            $("input[name='createHC']").val(createHC);
            $("input[name='createPAC']").val(createPACS);
        }

        function selectRequestType(type) {
            $("input[name='title']").prop("checked", false);
            $('#user-title-' + type).prop("checked", true);
            if (type === "add" || type === "remove" || type === "edit") {
                $("#user_search").removeClass("hidden");
                $("#doctor_hr").addClass("hidden");
                $("#user_detail").prop("disabled", true);
            } else if (type === "hr" || type === "doctor") {
                $("#user_search").addClass("hidden");
                $("#doctor_hr").removeClass("hidden");
                $("#user_detail").prop("disabled", false);
            }
            $('#document-addtional-info').removeClass('hidden');
        }

        async function searchUserData() {
            const userid = $("#request_userid").val();
            if (userid === "") {
                Swal.fire({
                    icon: "error",
                    title: "กรุณาใส่รหัสพนักงาน",
                    timer: 1500,
                    showConfirmButton: false,
                });
                return;
            }
            // Call function form Approver Page
            const user = await searchUser(userid);
            if (user) {
                $("#request_name").val(user.name);
                $("#request_name_en").val(user.name_EN);
                $("#request_department").val(user.department);

                $("#request_userid").prop('disabled', true);
                $("#search-btn").addClass('hidden');
                $("#reset-search-btn").removeClass('hidden');

                Swal.fire({
                    icon: "success",
                    title: "โหลดข้อมูลสำเร็จ",
                    timer: 1500,
                    showConfirmButton: false,
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "ไม่พบข้อมูลพนักงาน",
                    text: "กรุณาตรวจสอบรหัสพนักงานอีกครั้ง",
                    timer: 1500,
                    showConfirmButton: false,
                });
                $("#request_name").val("");
                $("#request_name_en").val("");
                $("#request_department").val("");
            }
        }

        function resetUserForm() {
            // Clear the core user data fields
            $("#request_userid").val("");
            $("#request_name").val("");
            $("#request_name_en").val("");
            $("#request_department").val("");

            // 🔥 Re-enable the User ID input and hide the Reset button
            $("#request_userid").prop('disabled', false);
            $("#reset-search-btn").addClass('hidden');
            $("#search-btn").removeClass('hidden');

            // Also clear the specific request checkboxes/fields (optional, but good practice)
            $("#request_ssb").prop("checked", false);
            $("#request_windows").prop("checked", false);
            $("#request_email").prop("checked", false);
            $("#request_hclab").prop("checked", false);
            $("#request_pacs").prop("checked", false);
            $("#request_other_check").prop("checked", false);
            $("#request_other").val("");
            $("#request_detail").val("");
        }

        function updateCreateFlags() {
            // Check doctor_hr checkboxes
            const doctorHrIT = $('#doctor_hr_it').is(':checked');
            const doctorHrHCLab = $('#doctor_hr_hclab').is(':checked');
            const doctorHrPACS = $('#doctor_hr_pacs').is(':checked');

            // Check standard request checkboxes
            const hasITRequest = $('input[name^="users["][name$="[request][ssb]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][windows]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][email]"]').val() === 'true' ||
                $('input[name^="users["][name$="[request][other_check]"]').val() === 'true';
            const hasHCRequest = $('input[name^="users["][name$="[request][hclab]"]').val() === 'true';
            const hasPACRequest = $('input[name^="users["][name$="[request][pacs]"]').val() === 'true';

            // Update hidden inputs
            $('input[name="createIT"]').val(doctorHrIT || hasITRequest ? 'true' : 'false');
            $('input[name="createHC"]').val(doctorHrHCLab || hasHCRequest ? 'true' : 'false');
            $('input[name="createPAC"]').val(doctorHrPACS || hasPACRequest ? 'true' : 'false');
        }

        function appendUserData() {
            const userid = $("#request_userid").val();
            const name = $("#request_name").val();
            const name_en = $("#request_name_en").val();
            const department = $("#request_department").val();
            const ssb = $("#request_ssb").is(":checked");
            const windows = $("#request_windows").is(":checked");
            const email = $("#request_email").is(":checked");
            const hclab = $("#request_hclab").is(":checked");
            const pacs = $("#request_pacs").is(":checked");
            const other_check = $("#request_other_check").is(":checked");
            const other = $("#request_other").val();
            const detail = $("#request_detail").val();
            const title = $('input[name="title"]:checked').val(); // Get selected request type

            if (userid === "" || name === "" || department === "") {
                Swal.fire({
                    icon: "error",
                    title: "กรุณาใส่ข้อมูลผู้ใช้งาน",
                    timer: 1500,
                    showConfirmButton: false,
                });
                return;
            }

            if (!ssb && !windows && !email && !hclab && !pacs && !other_check) {
                Swal.fire({
                    icon: "error",
                    title: "กรุณาเลือกระบบที่ต้องการ",
                    timer: 1500,
                    showConfirmButton: false,
                });
                return;
            }

            // Check for duplicate userid using the data-userid attribute on displayed items
            if ($(`#user_result_append div[data-userid="${userid}"]`).length > 0) {
                Swal.fire({
                    icon: "error",
                    title: "ผู้ใช้งานนี้ถูกเพิ่มแล้ว",
                    timer: 1500,
                    showConfirmButton: false,
                });
                return;
            }


            // --- Hidden Input Generation ---
            // Use a unique ID based on the userid for easy removal later
            const containerId = `user-item-${userid}`;

            // Collect systems requested for display
            const systemsRequested = [
                ssb ? "SSB" : "",
                windows ? "Windows" : "",
                email ? "Email" : "",
                hclab ? "HCLAB" : "",
                pacs ? "PACS" : "",
                other_check ? other : ""
            ].filter(Boolean).join(", ");

            // Build the HTML for the displayed item, including all hidden inputs
            $("#user_result_append").append(`
                <div class="bg-secondary text-white p-3 rounded-lg mb-2 user-item" id="${containerId}" data-userid="${userid}">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="font-medium">${userid} - ${name} (${title})</div>
                            <div class="text-sm ">${department}</div>
                            <div class="text-sm mt-1">
                                <span class="font-medium">ระบบที่ขอ:</span> ${systemsRequested || "ไม่ได้ระบุ"}
                            </div>
                            ${detail ? `<div class="text-sm mt-1"><span class="font-medium">รายละเอียด:</span> ${detail}</div>` : ''}
                        </div>
                        <button type="button" class="btn btn-sm btn-error" onclick="removeUser('${containerId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <input type="hidden" name="users[${userid}][userid]" value="${userid}" />
                    <input type="hidden" name="users[${userid}][name]" value="${name}" />
                    <input type="hidden" name="users[${userid}][name_en]" value="${name_en}" />
                    <input type="hidden" name="users[${userid}][department]" value="${department}" />
                    <input type="hidden" name="users[${userid}][title]" value="${title}" />
                    <input type="hidden" name="users[${userid}][request][ssb]" value="${ssb}" />
                    <input type="hidden" name="users[${userid}][request][windows]" value="${windows}" />
                    <input type="hidden" name="users[${userid}][request][email]" value="${email}" />
                    <input type="hidden" name="users[${userid}][request][hclab]" value="${hclab}" />
                    <input type="hidden" name="users[${userid}][request][pacs]" value="${pacs}" />
                    <input type="hidden" name="users[${userid}][request][other_check]" value="${other_check}" />
                    <input type="hidden" name="users[${userid}][request][other]" value="${other}" />
                    <input type="hidden" name="users[${userid}][request][detail]" value="${detail}" />
                </div>
            `);

            // Clear form fields after adding
            resetUserForm();
            updateCreateFlags();

        }

        function removeUser(containerId) {
            try {
                $(`#${containerId}`).remove();
                updateCreateFlags();
                Swal.fire({
                    icon: "success",
                    title: "ลบผู้ใช้งานเรียบร้อย",
                    showConfirmButton: false,
                    timer: 1500
                });
            } catch (e) {
                console.error("Error removing user:", e);
            }
        }
    </script>
@endpush
