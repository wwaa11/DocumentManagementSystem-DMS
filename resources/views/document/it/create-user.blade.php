<section class="mt-6 hidden" id="user-section">
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body p-6">
            <h3 class="card-title text-primary mb-4 flex items-center text-2xl">
                <i class="fas fa-user-cog mr-2"></i> ขอรหัสผู้ใช้งาน/สิทธิ์
            </h3>

            <div class="mb-6">
                <h4 class="font-medium text-lg mb-3">ประเภทการขอ</h4>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
                    <label class="card bg-base-100 hover:bg-primary/5 cursor-pointer border transition-all hover:shadow-md" for="user-title-add">
                        <div class="card-body p-4">
                            <div class="flex items-center">
                                <input class="radio radio-primary mr-3" id="user-title-add" value="ขอเพิ่ม" type="radio" name="title" onchange="selectRequestType('add')" />
                                <div>
                                    <h4 class="font-medium">ขอเพิ่ม</h4>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="card bg-base-100 hover:bg-primary/5 cursor-pointer border transition-all hover:shadow-md" for="user-title-remove">
                        <div class="card-body p-4">
                            <div class="flex items-center">
                                <input class="radio radio-primary mr-3" id="user-title-remove" value="ขอลด" type="radio" name="title" onchange="selectRequestType('remove')" />
                                <div>
                                    <h4 class="font-medium">ขอลด</h4>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="card bg-base-100 hover:bg-primary/5 cursor-pointer border transition-all hover:shadow-md" for="user-title-edit">
                        <div class="card-body p-4">
                            <div class="flex items-center">
                                <input class="radio radio-primary mr-3" id="user-title-edit" value="ขอแก้ไข" type="radio" name="title" onchange="selectRequestType('edit')" />
                                <div>
                                    <h4 class="font-medium">ขอแก้ไข</h4>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="card bg-base-100 hover:bg-primary/5 cursor-pointer border transition-all hover:shadow-md" for="user-title-doctor">
                        <div class="card-body p-4">
                            <div class="flex items-center">
                                <input class="radio radio-primary mr-3" id="user-title-doctor" value="เลขาแพทย์" type="radio" name="title" onchange="selectRequestType('doctor')" />
                                <div>
                                    <h4 class="font-medium">เลขาแพทย์</h4>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="card bg-base-100 hover:bg-primary/5 cursor-pointer border transition-all hover:shadow-md" for="user-title-hr">
                        <div class="card-body p-4">
                            <div class="flex items-center">
                                <input class="radio radio-primary mr-3" id="user-title-hr" value="ฝ่ายบุคคล" type="radio" name="title" onchange="selectRequestType('hr')" />
                                <div>
                                    <h4 class="font-medium">ฝ่ายบุคคล</h4>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Standard Request Form -->
            <div class="hidden" id="user_search">
                <div class="bg-base-200 mb-4 rounded-lg p-6">
                    <h4 class="mb-4 flex items-center font-medium text-xl">
                        <i class="fas fa-search text-primary mr-2"></i> ค้นหาข้อมูลพนักงาน
                    </h4>

                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">รหัสพนักงาน</span>
                            </label>
                            <div class="join">
                                <input class="join-item input input-bordered w-full" id="request_userid" placeholder="รหัสพนักงาน" type="text" />
                                <button class="btn btn-square btn-primary join-item" type="button" onclick="searchUserData()">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button class="btn btn-square btn-warning join-item hidden" id="reset-search-btn" type="button" onclick="resetUserForm()">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">ชื่อ-นามสกุล</span>
                            </label>
                            <input class="input input-bordered bg-base-100/50 w-full" id="request_name" placeholder="ชื่อ-นามสกุล" type="text" readonly />
                            <input id="request_name_en" type="hidden" />
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">แผนก</span>
                            </label>
                            <input class="input input-bordered bg-base-100/50 w-full" id="request_department" placeholder="แผนก" type="text" readonly />
                        </div>
                    </div>

                    <h4 class="mb-4 font-medium text-xl">สิทธิ์การใช้งานที่ต้องการ</h4>
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="hover:bg-base-100 flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors">
                            <input class="checkbox checkbox-primary" id="request_ssb" type="checkbox" />
                            <span class="label-text">SSB</span>
                        </label>
                        <label class="hover:bg-base-100 flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors">
                            <input class="checkbox checkbox-primary" id="request_windows" type="checkbox" />
                            <span class="label-text">Windows</span>
                        </label>
                        <label class="hover:bg-base-100 flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors">
                            <input class="checkbox checkbox-primary" id="request_email" type="checkbox" />
                            <span class="label-text">Email</span>
                        </label>
                        <label class="hover:bg-base-100 flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors">
                            <input class="checkbox checkbox-primary" id="request_hclab" type="checkbox" />
                            <span class="label-text">HCLAB</span>
                        </label>
                        <label class="hover:bg-base-100 flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors">
                            <input class="checkbox checkbox-primary" id="request_pacs" type="checkbox" />
                            <span class="label-text">PACS</span>
                        </label>
                        <label class="hover:bg-base-100 flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors">
                            <input class="checkbox checkbox-primary" id="request_other_check" type="checkbox" />
                            <span class="label-text">อื่นๆ:</span>
                            <input class="input input-bordered input-sm flex-grow" id="request_other" type="text" />
                        </label>
                    </div>

                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">รายละเอียดเพิ่มเติม</span>
                        </label>
                        <textarea class="textarea textarea-bordered h-24 w-full" id="request_detail" placeholder="รายละเอียดเพิ่มเติม"></textarea>
                    </div>

                    <div class="mt-3 flex justify-end">
                        <button class="btn btn-primary w-full gap-2" onclick="appendUserData()" type="button">
                            <i class="fas fa-plus"></i> เพิ่มรายการ
                        </button>
                    </div>

                    <div class="mt-6" id="user_result_append"></div>

                </div>
            </div>

            <!-- Doctor/HR Request Form -->
            <div class="hidden" id="doctor_hr">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text w-full">รายละเอียด</span>
                    </label>
                    <textarea class="textarea textarea-bordered h-40 w-full" id="user_detail" name="user_detail" placeholder="รายละเอียด" disabled></textarea>
                </div>
            </div>
        </div>
    </div>
</section>

@push("scripts")
    <script>
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
        }

        async function searchUserData() {
            const userid = $("#request_userid").val();
            if (userid === "") {
                Swal.fire({
                    icon: "error",
                    title: "กรุณาใส่รหัสพนักงาน",
                });
                return;
            }
            // Call function form Approver Page
            const user = await searchUser(userid);
            console.log(user);
            if (user) {
                $("#request_name").val(user.name);
                $("#request_name_en").val(user.name_EN);
                $("#request_department").val(user.department);

                $("#request_userid").prop('disabled', true);
                $("#search-btn").addClass('hidden');
                $("#reset-search-btn").removeClass('hidden');
            } else {
                Swal.fire({
                    icon: "error",
                    title: "ไม่พบข้อมูลพนักงาน",
                    text: "กรุณาตรวจสอบรหัสพนักงานอีกครั้ง",
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
                });
                return;
            }

            if (!ssb && !windows && !email && !hclab && !pacs && !other_check) {
                Swal.fire({
                    icon: "error",
                    title: "กรุณาเลือกระบบที่ต้องการ",
                });
                return;
            }

            // Check for duplicate userid using the data-userid attribute on displayed items
            if ($(`#user_result_append div[data-userid="${userid}"]`).length > 0) {
                Swal.fire({
                    icon: "error",
                    title: "ผู้ใช้งานนี้ถูกเพิ่มแล้ว",
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
                <div class="bg-base-200 p-3 rounded-lg mb-2 user-item" id="${containerId}" data-userid="${userid}">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="font-medium">${userid} - ${name} (${title})</div>
                            <div class="text-sm text-gray-600">${department}</div>
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
        }

        function removeUser(containerId) {
            try {
                $(`#${containerId}`).remove();

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
