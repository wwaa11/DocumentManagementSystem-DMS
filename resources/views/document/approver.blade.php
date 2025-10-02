<!-- Approver Section -->
 @if (auth()->user()->getapprover)
     {{-- Approver Information Card --}}
     <div class="card border-base-300 bg-base-100 shadow-lg">
         <div class="card-body p-6">
             {{-- Header/Status --}}
             <h2 class="card-title text-success mb-4 text-2xl">
                 <i class="fas fa-user-check mr-2"></i> ผู้อนุมัติแผนก {{ auth()->user()->department }}
             </h2>
             {{-- Display Approver Details (Using a Grid for better alignment) --}}
             <div class="grid grid-cols-1 gap-y-4 gap-x-6 md:grid-cols-2">
                 {{-- User ID & Name --}}
                 <div class="form-control">
                     <label class="label pb-1">
                         <span class="label-text text-base-content/70 font-medium flex items-center">
                             <i class="fas fa-id-badge text-success mr-2"></i> รหัสพนักงาน / ชื่อ
                         </span>
                     </label>
                     <div class="input-group">
                         <input id="approver_userid" type="hidden" name="approver[userid]" value="{{ auth()->user()->getapprover->approver->userid }}">
                         <input class="input input-bordered w-full font-semibold" id="approver_userid_name" type="text" readonly value="{{ auth()->user()->getapprover->approver->userid }} - {{ auth()->user()->getapprover->approver->name }}" />
                     </div>
                 </div>
                 {{-- Position --}}
                 <div class="form-control">
                     <label class="label pb-1">
                         <span class="label-text text-base-content/70 font-medium flex items-center">
                             <i class="fas fa-briefcase text-success mr-2"></i> ตำแหน่ง
                         </span>
                     </label>
                     <div class="input-group">
                         <input class="input input-bordered w-full" id="approver_position" type="text" readonly value="{{ auth()->user()->getapprover->approver->position }}" />
                     </div>
                 </div>
                 {{-- Email --}}
                 <div class="form-control md:col-span-2">
                     <label class="label pb-1">
                         <span class="label-text text-base-content/70 font-medium flex items-center">
                             <i class="fas fa-envelope text-success mr-2"></i> อีเมล
                         </span>
                     </label>
                     <div class="input-group">
                         <input class="input input-bordered w-full" id="approver_email" type="email" readonly name="approver[email]" value="{{ auth()->user()->getapprover->approver->email }}" />
                     </div>
                 </div>
             </div>

             {{-- Action Button (Change Approver) --}}
             <div class="card-actions mt-6 justify-end">
                 <button class="btn btn-outline btn-primary gap-2" id="change-approver-btn" type="button" onclick="showApproverSelection()">
                     <i class="fas fa-exchange-alt"></i> เปลี่ยนผู้อนุมัติ
                 </button>
             </div>
         </div>
     </div>
     {{-- Approver Selection Dropdown (Initially Hidden) --}}
     <div class="dropdown dropdown-end mt-4 w-full hidden" id="approver-selection">
         <div class="bg-base-200 rounded-box p-4 shadow-xl">
             <label class="form-control mb-4">
                 <div class="label">
                     <span class="label-text font-semibold text-lg">ค้นหาผู้อนุมัติ</span>
                 </div>
                 <div class="join w-full">
                     <input class="join-item input input-bordered w-full" id="approver-search" type="text" placeholder="ค้นหาด้วยชื่อหรือรหัสพนักงาน">
                     <button class="join-item btn btn-primary" type="button" onclick="searchApprover()">
                         <i class="fas fa-search"></i> ค้นหา
                     </button>
                 </div>
             </label>
             {{-- Search Results Display --}}
             <div id="approver-search-results" class="mt-4">
                 {{-- Results will be appended here --}}
             </div>
         </div>
     </div>
 @else
     {{-- No Approver Found Alert --}}
     <div class="alert alert-error shadow-lg rounded-lg p-4 flex items-center">
         <i class="fas fa-exclamation-triangle text-2xl mr-3"></i>
         <div>
             <h3 class="font-bold text-lg">ไม่มีผู้อนุมัติกำหนดไว้!</h3>
             <p class="text-sm">โปรดติดต่อผู้ดูแลระบบเพื่อเพิ่มผู้อนุมัติสำหรับคุณ</p>
         </div>
     </div>
 @endif

 @push("scripts")
     <script>
         function showApproverSelection() {
             const $selectionDiv = $('#approver-selection');
             $selectionDiv.toggleClass('hidden');
         }

         async function searchApprover() {
             const userid = document.getElementById('approver-search').value;
             const user = await searchUser(userid);
             if (user) {
                 $('#approver_userid').val(user.userid);
                 $('#approver_userid_name').val(user.userid + ' - ' + user.name);
                 $('#approver_position').val(user.position);
                 $('#approver_email').val(user.email);
             }
         }

         async function searchUser(userid) {
             if (!userid) {
                 Swal.fire({
                     icon: 'error',
                     title: 'ผิดพลาด',
                     text: 'กรุณาใส่ User ID',
                 });
                 return;
             }
             var user = null;

             await axios.post('{{ route("user.search") }}', {
                     userid: userid,
                 })
                 .then(function(response) {
                     if (response.data.status) {
                         user = response.data.user;
                     }
                 })
                 .catch(function(error) {
                     Swal.fire({
                         icon: 'error',
                         title: 'ผิดพลาด',
                         text: 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์',
                     });
                 });

             return user;
         }
     </script>
 @endpush
