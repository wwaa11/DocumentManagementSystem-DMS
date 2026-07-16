 <!-- Edit Right Request Form -->
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
         <i class="fas fa-key mr-2"></i> ประเภทสิทธิ
     </h4>
     <div class="mb-6 flex flex-row gap-3">
         <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="request_type_add">
             <div class="flex items-center">
                 <input class="radio radio-primary mr-3" id="request_type_add" value="ขอเพิ่ม" type="radio" name="request_type" />
                 <div>
                     <h4 class="font-medium">ขอเพิ่ม</h4>
                 </div>
             </div>
         </label>
         <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="request_type_subtract">
             <div class="flex items-center">
                 <input class="radio radio-primary mr-3" id="request_type_subtract" value="ขอลด" type="radio" name="request_type" />
                 <div>
                     <h4 class="font-medium">ขอลด</h4>
                 </div>
             </div>
         </label>
         <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="request_type_edit">
             <div class="flex items-center">
                 <input class="radio radio-primary mr-3" id="request_type_edit" value="ขอแก้ไข" type="radio" name="request_type" />
                 <div>
                     <h4 class="font-medium">ขอเแก้ไข</h4>
                 </div>
             </div>
         </label>
     </div>

     <h4 class="card-title text-primary mb-3 flex items-center text-xl">
         <i class="fas fa-key mr-2"></i> สิทธิการใช้งานที่ต้องการ
     </h4>

     <div class="mb-3 grid grid-cols-1 gap-4 md:grid-cols-3">
         <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
             <input class="checkbox checkbox-primary" id="request_ssb" type="checkbox" />
             <span class="label-text">SSB</span>
         </label>
         <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
            <input class="checkbox checkbox-primary" id="request_MKyT" type="checkbox" />
            <span class="label-text">MKyT</span>
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
             <input class="checkbox checkbox-primary" id="request_heartstream" type="checkbox" />
             <span class="label-text">Heart Stream</span>
         </label>
         <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded-lg p-3 transition-colors">
             <input class="checkbox checkbox-primary" id="request_register" type="checkbox" />
             <span class="label-text">Register</span>
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

 @push("scripts")
     <script>
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
             $('input[name="request_type"]').prop("checked", false);
             $("#request_ssb").prop("checked", false);
             $("#request_MKyT").prop("checked", false);
             $("#request_windows").prop("checked", false);
             $("#request_email").prop("checked", false);
             $("#request_hclab").prop("checked", false);
             $("#request_pacs").prop("checked", false);
             $("#request_heartstream").prop("checked", false);
             $("#request_register").prop("checked", false);
             $("#request_other_check").prop("checked", false);
             $("#request_other").val("");
             $("#request_detail").val("");
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

         function appendUserData() {
             const userid = $("#request_userid").val();
             const name = $("#request_name").val();
             const name_en = $("#request_name_en").val();
             const department = $("#request_department").val();
             const request_type = $('input[name="request_type"]:checked').val();
             const ssb = $("#request_ssb").is(":checked");
             const mkyt = $("#request_MKyT").is(":checked");
             const windows = $("#request_windows").is(":checked");
             const email = $("#request_email").is(":checked");
             const hclab = $("#request_hclab").is(":checked");
             const pacs = $("#request_pacs").is(":checked");
             const heartstream = $("#request_heartstream").is(":checked");
             const register = $("#request_register").is(":checked");
             const other_check = $("#request_other_check").is(":checked");
             const other = $("#request_other").val();
             const detail = $("#request_detail").val();
             const title = $('input[name="title"]:checked').val(); // Get selected request type

             if (userid === "" || name === "" || department === "") {
                 Swal.fire({
                     icon: "error",
                     title: "กรุณาใส่ข้อมูลผู้ใช้งาน",
                     timer: 1000,
                     showConfirmButton: false,
                 });
                 return;
             }

             if (request_type === "") {
                 Swal.fire({
                     icon: "error",
                     title: "ประเภทสิทธิ",
                     timer: 1000,
                     showConfirmButton: false,
                 });
                 return;
             }

             if (!ssb && !mkyt && !windows && !email && !hclab && !pacs && !heartstream && !register && !other_check) {
                 Swal.fire({
                     icon: "error",
                     title: "กรุณาเลือกระบบที่ต้องการ",
                     timer: 1000,
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
                 mkyt ? "MKyT" : "",
                 windows ? "Windows" : "",
                 email ? "Email" : "",
                 hclab ? "HCLAB" : "",
                 pacs ? "PACS" : "",
                 heartstream ? "Heart Stream" : "",
                 register ? "Register" : "",
                 other_check ? other : ""
             ].filter(Boolean).join(", ");

             // Build the HTML for the displayed item, including all hidden inputs
             $("#user_result_append").append(`
            <div class="bg-secondary text-white p-3 rounded-lg mb-2 user-item" id="${containerId}" data-userid="${userid}">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="font-medium">${userid} - ${name} (${title})</div>
                    <div class="text-sm ">${department}</div>
                    <div class="text-sm mt-1">ประเภท: ${request_type}</div>
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
            <input type="hidden" name="users[${userid}][type]" value="${request_type}" />
            <input type="hidden" name="users[${userid}][request][ssb]" value="${ssb}" />
            <input type="hidden" name="users[${userid}][request][mkyt]" value="${mkyt}" />
            <input type="hidden" name="users[${userid}][request][windows]" value="${windows}" />
            <input type="hidden" name="users[${userid}][request][email]" value="${email}" />
            <input type="hidden" name="users[${userid}][request][hclab]" value="${hclab}" />
            <input type="hidden" name="users[${userid}][request][pacs]" value="${pacs}" />
            <input type="hidden" name="users[${userid}][request][heartstream]" value="${heartstream}" />
            <input type="hidden" name="users[${userid}][request][register]" value="${register}" />
            <input type="hidden" name="users[${userid}][request][other_check]" value="${other_check}" />
            <input type="hidden" name="users[${userid}][request][other]" value="${other}" />
            <input type="hidden" name="users[${userid}][request][detail]" value="${detail}" />
            </div>
            `);

             // Clear form fields after adding
             resetUserForm();
             updateCreateFlags();

         }
     </script>
 @endpush
