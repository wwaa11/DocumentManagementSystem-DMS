 <!-- Approver Section -->
 @if (auth()->user()->getapprover)
     {{-- Approver Information Card --}}
     <div class="card card-compact border-base-300 bg-base-100 border shadow-lg">
         <div class="card-body">
             {{-- Header/Status --}}
             <h2 class="card-title text-success">
                 <i class="fas fa-user-check"></i> ผู้อนุมัติแผนก {{ auth()->user()->department }}
             </h2>
             {{-- Display Approver Details (Using a Grid for better alignment) --}}
             <div class="mt-2 grid grid-cols-1 gap-4 md:grid-cols-2">
                 {{-- User ID & Name --}}
                 <label class="form-control">
                     <div class="label pb-0">
                         <span class="text-success"><i class="fas fa-id-badge"></i></span> <span class="label-text text-base-content/70 font-medium">รหัสพนักงาน / ชื่อ</span>
                     </div>
                     <div class="input-group">
                         <input id="approver_userid" type="hidden" name="approver[userid]" value="{{ auth()->user()->getapprover->approver->userid }}">
                         <input class="input input-bordered w-full font-semibold" id="approver_userid_name" type="text" readonly value="{{ auth()->user()->getapprover->approver->userid }} - {{ auth()->user()->getapprover->approver->name }}" />
                     </div>
                 </label>
                 {{-- Position --}}
                 <label class="form-control">
                     <div class="label pb-0">
                         <span class="text-success"><i class="fas fa-briefcase"></i></span> <span class="label-text text-base-content/70 font-medium">ตำแหน่ง</span>
                     </div>
                     <div class="input-group">
                         <input class="input input-bordered w-full" id="approver_position" type="text" readonly value="{{ auth()->user()->getapprover->approver->position }}" />
                     </div>
                 </label>
                 {{-- Email --}}
                 <label class="form-control md:col-span-2">
                     <div class="label pb-0">
                         <span class="text-success"><i class="fas fa-envelope"></i></span><span class="label-text text-base-content/70 font-medium">อีเมล</span>
                     </div>
                     <div class="input-group">
                         <input class="input input-bordered w-full" id="approver_email" type="email" readonly name="approver[email]" value="{{ auth()->user()->getapprover->approver->email }}" />
                     </div>
                 </label>
             </div>

             {{-- Action Button (Change Approver) --}}
             <div class="card-actions mt-4 justify-end">
                 <button class="btn btn-sm btn-outline btn-primary gap-2" id="change-approver-btn" type="button" onclick="showApproverSelection()">
                     <i class="fas fa-exchange-alt"></i> เปลี่ยนผู้อนุมัติ
                 </button>
             </div>
         </div>
     </div>
     {{-- Approver Selection Dropdown (Initially Hidden) --}}
     <div class="dropdown dropdown-end mt-4 max-h-0 w-full overflow-hidden opacity-0 transition-all duration-500 ease-in-out" id="approver-selection">
         <div class="bg-base-200 rounded-box p-4 shadow-xl">
             <label class="form-control">
                 <div class="label">
                     <span class="label-text font-semibold">ค้นหาผู้อนุมัติ</span>
                 </div>
                 <div class="join w-full">
                     <input class="join-item input input-bordered w-full" id="approver-search" type="text" placeholder="ค้นหาด้วยชื่อหรือรหัสพนักงาน">
                     <button class="join-item btn btn-square btn-primary" type="button" onclick="searchApprover()">
                         <i class="fas fa-search"></i>
                     </button>
                 </div>
             </label>
         </div>
     </div>
 @else
     {{-- No Approver Found Alert --}}
     <div class="alert alert-error shadow-lg" role="alert">
         <i class="fas fa-exclamation-triangle"></i>
         <div>
             <h3 class="font-bold">ไม่มีผู้อนุมัติกำหนดไว้!</h3>
             <div class="text-xs">โปรดติดต่อผู้ดูแลระบบเพื่อเพิ่มผู้อนุมัติสำหรับคุณ</div>
         </div>
     </div>
 @endif

 @push("scripts")
     <script>
         function showApproverSelection() {
             const $selectionDiv = $('#approver-selection');
             // Check if the element is currently hidden (has max-h-0)
             if ($selectionDiv.hasClass('max-h-0')) {
                 // SHOW: Remove hidden state and apply visible state
                 $selectionDiv.removeClass('max-h-0 opacity-0');
                 // Add a max-height large enough to show all content (e.g., max-h-96 or max-h-screen)
                 $selectionDiv.addClass('max-h-96 opacity-100');
             } else {
                 // HIDE: Remove visible state and apply hidden state
                 $selectionDiv.removeClass('max-h-96 opacity-100');
                 $selectionDiv.addClass('max-h-0 opacity-0');
             }
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
