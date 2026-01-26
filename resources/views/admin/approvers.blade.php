@extends("layouts.app")

@push("scripts")
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endpush

@section("content")
    <div class="mx-8 pb-10">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-primary text-3xl font-bold">Department Approvers</h1>
                <p class="text-base-content/60 text-sm">จัดการรายชื่อผู้มีอำนาจอนุมัติเอกสารแยกตามแผนก</p>
            </div>
            @if (isset($noti) && $noti["error"] > 0)
                <div class="alert alert-warning w-auto px-4 py-2 shadow-sm">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="text-xs">พบ {{ $noti["error"] }} แผนกที่ยังไม่มีผู้อนุมัติ</span>
                </div>
            @endif
        </div>

        <div class="divider my-6"></div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Left Side: Search & Update Form -->
            <div class="space-y-6 lg:col-span-1">
                <!-- Search Card -->
                <div class="card bg-base-100 border-base-200 border shadow-xl">
                    <div class="card-body p-6">
                        <h3 class="mb-4 flex items-center text-sm font-bold">
                            <i class="fas fa-search text-primary mr-2"></i> ค้นหาแผนก
                        </h3>
                        <div class="form-control w-full">
                            <div class="join">
                                <span class="join-item bg-base-200 border-base-300 flex items-center border px-3">
                                    <i class="fas fa-building text-base-content/40"></i>
                                </span>
                                <input class="input input-bordered input-sm join-item w-full grow focus:outline-none" id="deptSearch" type="search" list="dept-suggestions" placeholder="พิมพ์ชื่อแผนกแล้วกด Enter..." />
                            </div>
                            <datalist id="dept-suggestions">
                                @foreach ($depts as $id => $item)
                                    <option value="{{ $item }}">
                                @endforeach
                            </datalist>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50 italic">* ค้นหาเพื่อเลือกแผนกที่ต้องการแก้ไข</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Update Form Card -->
                <div class="card bg-base-100 border-base-200 overflow-hidden border shadow-xl">
                    <div class="bg-primary/5 border-primary/10 border-b px-6 py-4">
                        <h3 class="text-primary flex items-center text-sm font-bold">
                            <i class="fas fa-edit mr-2"></i> แก้ไขข้อมูลผู้อนุมัติ
                        </h3>
                    </div>
                    <form class="card-body space-y-4 p-6" id="updateForm" action="{{ route("approvers.update") }}" method="POST">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-error mb-2 py-2 text-xs shadow-sm">
                                <ul class="list-disc pl-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-control w-full">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">แผนก (Department)</span></label>
                            <input class="input input-bordered input-sm bg-base-200 font-bold" id="form_dept" type="text" name="department" placeholder="กรุณาค้นหาแผนกก่อน..." readonly>
                        </div>

                        <div class="divider my-1 text-[10px] uppercase tracking-widest opacity-30">ข้อมูลเจ้าหน้าที่</div>

                        <div class="form-control w-full">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">User ID</span></label>
                            <div class="join w-full">
                                <input class="input input-bordered input-sm join-item w-full focus:outline-none" id="form_userid" type="text" name="userid">
                                <button class="btn btn-secondary btn-sm join-item" type="button" onclick="getUserData()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-control w-full">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">ชื่อ-นามสกุล (Name)</span></label>
                            <input class="input input-bordered input-sm w-full focus:outline-none" id="form_name" type="text" name="name">
                        </div>

                        <div class="form-control w-full">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">ตำแหน่ง (Position)</span></label>
                            <input class="input input-bordered input-sm w-full focus:outline-none" id="form_position" type="text" name="position">
                        </div>

                        <div class="form-control w-full">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">อีเมล (Email)</span></label>
                            <input class="input input-bordered input-sm w-full focus:outline-none" id="form_email" type="text" name="email">
                        </div>

                        <div class="card-actions mt-4">
                            <button class="btn btn-primary btn-sm w-full shadow-md" id="submitBtn" type="submit" disabled>
                                <i class="fas fa-save mr-1"></i> Update Approver
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Side: Approvers Table -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 border-base-200 h-[calc(100vh-220px)] border shadow-xl lg:sticky lg:top-8">
                    <div class="card-body flex flex-col overflow-hidden p-0">
                        <div class="bg-base-200/50 border-base-200 flex shrink-0 items-center justify-between border-b px-6 py-3">
                            <span class="text-[10px] font-bold uppercase tracking-widest opacity-60">Department List</span>
                            <span class="badge badge-sm badge-outline opacity-50">{{ $datas->count() }} Departments</span>
                        </div>
                        <div class="custom-scrollbar grow overflow-x-auto overflow-y-auto">
                            <table class="table-zebra table w-full border-separate border-spacing-0">
                                <thead class="bg-base-100 sticky top-0 z-20 shadow-sm">
                                    <tr class="bg-base-200/50">
                                        <th class="py-4 pl-6 text-xs font-bold uppercase tracking-wider">Department</th>
                                        <th class="py-4 text-xs font-bold uppercase tracking-wider">Approver Info</th>
                                        <th class="py-4 pr-6 text-xs font-bold uppercase tracking-wider">Last Update</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($datas as $index => $item)
                                        @php $hasUser = !empty($item->userid) && $item->userid !== '-'; @endphp
                                        <tr class="hover:bg-base-200/30 row-item cursor-pointer transition-colors" onclick="fillUpdateForm(this)">
                                            <td class="border-base-200/50 border-b py-4 pl-6 align-top">
                                                <div class="text-primary font-bold" id="{{ $item->id }}_department">{{ $item->department }}</div>
                                                <div class="hidden" id="{{ $item->id }}_userid">{{ $item->userid }}</div>
                                            </td>
                                            <td class="border-base-200/50 border-b py-4">
                                                @if ($hasUser)
                                                    <div class="flex flex-col gap-0.5">
                                                        <div class="flex items-center gap-1.5 font-bold">
                                                            {{ $item->name }}
                                                            <span class="bg-base-200 text-base-content/60 rounded px-1.5 py-0.5 text-[10px] font-medium">{{ $item->userid }}</span>
                                                        </div>
                                                        <div class="text-[11px] font-medium opacity-70" id="{{ $item->id }}_position">{{ $item->position }}</div>
                                                        <div class="text-primary text-[11px] underline" id="{{ $item->id }}_email">{{ $item->email }}</div>
                                                        <div class="hidden" id="{{ $item->id }}_name">{{ $item->name }}</div>
                                                    </div>
                                                @else
                                                    <div class="text-error flex items-center gap-2 text-sm italic">
                                                        <i class="fas fa-user-slash text-xs"></i>
                                                        ยังไม่มีข้อมูลผู้อนุมัติ
                                                    </div>
                                                    <div class="hidden" id="{{ $item->id }}_name">-</div>
                                                    <div class="hidden" id="{{ $item->id }}_position">-</div>
                                                    <div class="hidden" id="{{ $item->id }}_email">-</div>
                                                @endif
                                            </td>
                                            <td class="border-base-200/50 border-b py-4 pr-6 align-top">
                                                @if ($item->last_update)
                                                    <div class="flex flex-col items-end text-right">
                                                        <div class="text-[10px] font-bold uppercase tracking-tighter opacity-40">Updated At</div>
                                                        <div class="text-[11px] font-medium">{{ date("d/m/Y H:i", strtotime($item->last_update)) }}</div>
                                                        @if ($item->last_userid || $item->last_username)
                                                            <div class="text-primary bg-primary/5 mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold">
                                                                <i class="fas fa-signature mr-1 scale-75"></i>
                                                                {{ $item->last_username ?? $item->last_userid }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="text-right text-[10px] italic opacity-30">No history</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script>
        document.getElementById('deptSearch').addEventListener('input', function(e) {
            const filter = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.row-item');

            rows.forEach(row => {
                const deptText = row.querySelector('[id*="_department"]').textContent.toLowerCase().trim();
                const isMatch = deptText.includes(filter);
                row.style.display = isMatch ? '' : 'none';
            });
        });

        document.getElementById('deptSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const filter = this.value.toLowerCase().trim();
                const visibleRows = Array.from(document.querySelectorAll('.row-item')).filter(r => r.style.display !== 'none');

                if (visibleRows.length > 0) {
                    fillUpdateForm(visibleRows[0]);

                    // Highlight effect
                    visibleRows[0].classList.add('bg-primary/10');
                    setTimeout(() => visibleRows[0].classList.remove('bg-primary/10'), 2000);
                }
            }
        });

        function fillUpdateForm(row) {
            // Remove previous highlights
            document.querySelectorAll('.row-item').forEach(r => r.classList.remove('ring-1', 'ring-primary', 'bg-primary/5'));

            // Add current highlight
            row.classList.add('ring-1', 'ring-primary', 'bg-primary/5');

            // Extract data from row IDs
            const dept = row.querySelector('[id*="_department"]').innerText.trim();
            const userid = row.querySelector('[id*="_userid"]').innerText.trim();
            const name = row.querySelector('[id*="_name"]').innerText.trim();
            const position = row.querySelector('[id*="_position"]').innerText.trim();
            const email = row.querySelector('[id*="_email"]').innerText.trim();

            document.getElementById('form_dept').value = dept;
            document.getElementById('form_userid').value = userid === '-' ? '' : userid;
            document.getElementById('form_name').value = name === '-' ? '' : name;
            document.getElementById('form_position').value = position === '-' ? '' : position;
            document.getElementById('form_email').value = email === '-' ? '' : email;

            // Enable submit button
            document.getElementById('submitBtn').disabled = false;

            // Scroll form into view if on mobile
            if (window.innerWidth < 1024) {
                document.getElementById('updateForm').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            // Visual feedback on inputs
            const inputs = ['form_userid', 'form_name', 'form_position', 'form_email'];
            inputs.forEach(id => {
                const el = document.getElementById(id);
                el.classList.add('ring-1', 'ring-primary/30');
                setTimeout(() => el.classList.remove('ring-1', 'ring-primary/30'), 500);
            });
        }

        function getUserData() {
            const userid = document.getElementById('form_userid').value.trim();
            const btn = event.currentTarget;

            if (!userid) {
                Swal.fire({
                    title: "กรุณาระบุ User ID",
                    icon: "warning",
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            btn.classList.add('loading');
            btn.innerHTML = '';

            axios.post('{{ route("approvers.getuser") }}', {
                    userid
                })
                .then(response => {
                    if (response.data.success) {
                        document.getElementById('form_name').value = response.data.user.name;
                        document.getElementById('form_position').value = response.data.user.position;
                        document.getElementById('form_email').value = response.data.user.email;

                        Swal.fire({
                            title: "พบข้อมูลผู้ใช้งาน",
                            icon: "success",
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        throw new Error("User not found");
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: "ไม่พบข้อมูล!",
                        text: "รหัสพนักงานไม่ถูกต้อง หรือไม่มีในระบบ",
                        icon: "error",
                        confirmButtonText: 'ตกลง',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-error'
                        }
                    });
                })
                .finally(() => {
                    btn.classList.remove('loading');
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i>';
                });
        }
    </script>
@endpush
