<div class="card-body space-y-8 p-8">
    <!-- Action Header -->
    <div class="flex items-center justify-between">
        <button class="btn btn-ghost btn-sm hover:bg-base-200 gap-2 transition-all" onclick="window.history.back()">
            <i class="fas fa-arrow-left"></i>
            ย้อนกลับ
        </button>
        <div class="badge badge-lg badge-primary gap-2 py-4 font-bold uppercase tracking-widest">
            <i class="fas fa-graduation-cap"></i>
            DMS Training
        </div>
    </div>

    <!-- Main Header -->
    <div class="bg-base-100 border-base-200 relative flex flex-col items-center gap-6 overflow-hidden rounded-2xl border p-6 shadow-sm md:flex-row">
        <div class="absolute right-0 top-0 p-4 opacity-5">
            <i class="fas fa-certificate text-9xl"></i>
        </div>
        <img class="h-16 w-auto" src="{{ asset("images/Side Logo.png") }}" alt="Logo">
        <div class="flex-1 text-center md:text-left">
            <h2 class="text-primary text-3xl font-extrabold">ใบบันทึกการฝึกอบรมภาคอิสระ</h2>
            <p class="text-base-content/50 font-medium">Internal Training Record Document</p>
        </div>
    </div>

    <!-- Metadata Grid -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="bg-base-200/50 border-base-200 rounded-xl border p-4">
            <div class="text-[10px] font-bold uppercase tracking-widest opacity-40">ผู้ขอเอกสาร</div>
            <div class="mt-1 flex items-center gap-2 font-bold">
                <i class="fas fa-user text-primary/60 text-xs"></i>
                {{ $document->creator->name }}
            </div>
        </div>
        <div class="bg-base-200/50 border-base-200 rounded-xl border p-4">
            <div class="text-[10px] font-bold uppercase tracking-widest opacity-40">แผนก</div>
            <div class="mt-1 flex items-center gap-2 font-bold">
                <i class="fas fa-building text-primary/60 text-xs"></i>
                {{ $document->creator->department }}
            </div>
        </div>
        <div class="bg-base-200/50 border-base-200 rounded-xl border p-4">
            <div class="text-[10px] font-bold uppercase tracking-widest opacity-40">วันที่สร้างเอกสาร</div>
            <div class="mt-1 flex items-center gap-2 font-bold">
                <i class="fas fa-calendar-day text-primary/60 text-xs"></i>
                {{ $document->created_at->format("d M Y") }}
            </div>
        </div>
    </div>

    <!-- Course Information Section -->
    <div class="card bg-base-100 border-base-200 overflow-hidden border shadow-sm">
        <div class="bg-primary/5 border-primary/10 flex items-center gap-2 border-b px-6 py-3">
            <i class="fas fa-info-circle text-primary"></i>
            <span class="text-primary text-xs font-bold uppercase tracking-wider">รายละเอียดหลักสูตร</span>
        </div>
        <div class="card-body space-y-4 p-6">
            <div>
                <div class="text-xs font-bold uppercase tracking-tighter opacity-40">ชื่อหลักสูตร (Course Title)</div>
                <div class="text-base-content mt-1 text-lg font-extrabold">{{ $document->title }}</div>
            </div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <div class="text-xs font-bold uppercase tracking-tighter opacity-40">ที่มา (Source)</div>
                    <div class="bg-base-250 mt-1 rounded-lg p-2 text-sm font-bold">{{ $document->detail }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-tighter opacity-40">กำหนดการฝึกอบรม (Schedule)</div>
                    <div class="text-primary mt-1 flex items-center gap-2 font-extrabold">
                        <i class="fas fa-calendar-alt"></i>
                        {{ $document->start_date->format("d M Y") }}
                        <span class="opacity-30">→</span>
                        {{ $document->end_date->format("d M Y") }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attachments Section -->
    <div class="space-y-4">
        <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest opacity-60">
            <i class="fas fa-file-pdf"></i> ไฟล์แนบประกอบ (Files)
        </h3>
        <div class="bg-base-100 border-base-200 rounded-2xl border p-6">
            @if ($document->files->count() > 0)
                @include("document.files", ["files" => $document->files])
            @else
                <div class="flex flex-col items-center justify-center py-4 italic opacity-30">
                    <i class="fas fa-folder-open mb-2 text-2xl"></i>
                    <p class="text-xs">ไม่มีไฟล์แนบประกอบการอบรม</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Mentors Section -->
    <div class="card bg-base-100 border-base-200 overflow-hidden border shadow-sm">
        <div class="bg-secondary/5 border-secondary/10 flex items-center gap-2 border-b px-6 py-3">
            <i class="fas fa-user-tie text-secondary"></i>
            <span class="text-secondary text-xs font-bold uppercase tracking-wider">รายชื่อวิทยากร (Mentors)</span>
        </div>
        <div class="overflow-x-auto p-0">
            @if (count($document->mentors) > 0)
                <table class="table-zebra table w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-base-200/50">
                            <th class="py-4 pl-8 text-xs font-bold uppercase opacity-60">รหัสพนักงาน</th>
                            <th class="py-4 text-xs font-bold uppercase opacity-60">ชื่อ - นามสกุล</th>
                            <th class="py-4 pr-8 text-xs font-bold uppercase opacity-60">ตำแหน่ง</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($document->mentors as $mentor)
                            <tr class="hover:bg-base-200/20 transition-colors">
                                <td class="text-secondary py-4 pl-8 font-bold">{{ $mentor->mentor }}</td>
                                <td class="py-4 font-bold">{{ $mentor->mentor_name }}</td>
                                <td class="py-4 pr-8 text-sm font-medium opacity-70">{{ $mentor->mentor_position }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-8 text-center text-sm italic opacity-30">ไม่มีข้อมูลวิทยากร</div>
            @endif
        </div>
    </div>

    <!-- Participants & Attendance Section -->
    <div class="card bg-base-100 border-base-200 overflow-hidden border shadow-xl">
        <div class="bg-accent/5 border-accent/10 flex items-center justify-between border-b px-8 py-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-users text-accent"></i>
                <span class="text-accent text-xs font-bold uppercase leading-none tracking-wider">รายชื่อผู้เข้าร่วม และ ประวัติการเช็คอิน (Attendance)</span>
            </div>
            @if ($document->training_id != null)
                <span class="badge badge-accent badge-sm animate-pulse font-bold">Live Tracking</span>
            @endif
        </div>
        <div class="overflow-x-auto p-0">
            @if ($document->training_id != null)
                <table class="table-zebra table w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-base-200/50">
                            <th class="py-4 pl-8 text-xs font-bold uppercase opacity-60">พนักงาน</th>
                            <th class="py-4 text-xs font-bold uppercase opacity-60">ข้อมูลส่วนตัว</th>
                            <th class="py-4 text-xs font-bold uppercase opacity-60">Check-in Time</th>
                            <th class="py-4 pr-8 text-center text-xs font-bold uppercase opacity-60">Status / Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-base-100 divide-y" id="attendance-table">
                        <tr>
                            <td class="py-12" colspan="4">
                                <div class="flex flex-col items-center justify-center opacity-30">
                                    <span class="loading loading-spinner loading-lg mb-4"></span>
                                    <p class="text-sm font-bold uppercase tracking-widest">กำลังดึงข้อมูลการเช็คอิน...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            @elseif (count($document->participants) > 0)
                <table class="table-zebra table w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-base-200/50">
                            <th class="py-4 pl-8 text-xs font-bold uppercase opacity-60">รหัสพนักงาน</th>
                            <th class="py-4 text-xs font-bold uppercase opacity-60">ชื่อ - นามสกุล</th>
                            <th class="py-4 text-xs font-bold uppercase opacity-60">ตำแหน่ง</th>
                            <th class="py-4 pr-8 text-xs font-bold uppercase opacity-60">แผนก</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($document->participants as $participant)
                            <tr class="hover:bg-base-200/20 transition-colors">
                                <td class="text-accent py-4 pl-8 font-bold">{{ $participant->participant }}</td>
                                <td class="py-4 font-bold">{{ $participant->participant_name }}</td>
                                <td class="py-4 text-sm opacity-70">{{ $participant->participant_position }}</td>
                                <td class="py-4 pr-8 text-sm opacity-70">{{ $participant->participant_department }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-10 text-center text-sm italic opacity-30">ไม่มีข้อมูลผู้เข้าร่วม</div>
            @endif
        </div>
    </div>

    <!-- Approval Chain Section -->
    <div class="pt-4">
        <div class="divider my-10 text-[10px] font-bold uppercase tracking-widest opacity-20">Approval Status & History</div>
        @include("document.tasks", ["tasks" => $document->tasks])
    </div>
</div>

@push("scripts")
    @if ($document->training_id != null)
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                axios.post("{{ route("document.training.getAttendance") }}", {
                    project_id: '{{ $document->id }}',
                }).then((response) => {
                    if (response.data.success) {
                        let isApprove = {{ $document->tasks->where("task_user", auth()->user()->userid)->count() > 0 && $document->status == "pending" ? "true" : "false" }};
                        let html = "";
                        for (const date in response.data.transaction) {
                            html += `
                                <tr class="bg-base-300">
                                    <td colspan="4" class="pl-8 py-2 text-[10px] font-black uppercase tracking-widest text-base-content/60">Session Date: ${date}</td>
                                </tr>
                            `;
                            for (const time in response.data.transaction[date]) {
                                for (const user of response.data.transaction[date][time]) {
                                    var canApprove = user.attend_datetime != null && user.approve_datetime == null;
                                    html += `
                                    <tr class="hover:bg-base-200/30 transition-all">
                                        <td class="pl-8 py-4">
                                            <div class="badge badge-neutral badge-sm font-bold font-mono">${user.userid}</div>
                                        </td>
                                        <td class="py-4 font-bold">${user.name}</td>
                                        <td class="py-4">
                                            ${user.attend_datetime ? 
                                                `<div class="flex items-center gap-2 font-mono font-bold text-accent">
                                                            <i class="fas fa-clock text-[10px] opacity-40"></i>
                                                            ${user.attend_datetime}
                                                         </div>` : 
                                                `<span class="text-error italic text-xs font-medium opacity-50">Not Checked-in</span>`
                                            }
                                        </td>
                                        <td class="pr-8 py-4 text-center">
                                            ${canApprove && isApprove ? 
                                                `<button class='btn btn-accent btn-xs rounded-full px-4' onclick='approveAttendance("${user.id}", "${user.userid}")'>
                                                            <i class="fas fa-check mr-1"></i> อนุมัติ
                                                         </button>` : 
                                                user.approve_datetime ? 
                                                    `<div class="flex flex-col items-center">
                                                                <div class="text-[9px] font-bold opacity-30 tracking-tight uppercase">Approved At</div>
                                                                <div class="badge badge-success badge-sm font-bold font-mono text-[10px] py-1 h-auto">${user.approve_datetime}</div>
                                                             </div>` : 
                                                    `<span class="opacity-10">—</span>`
                                            }
                                        </td>
                                    </tr>
                                    `;
                                }
                            }
                        }
                        if (html === "") {
                            html = `<tr><td colspan="4" class="py-20 text-center opacity-30 italic">ไม่พบข้อมูลการเช็คอินในระบบหลัก</td></tr>`;
                        }
                        document.querySelector("#attendance-table").innerHTML = html;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            });

            function approveAttendance(id, userid) {
                Swal.fire({
                    title: 'ยืนยันการอนุมัติเข้าร่วม?',
                    text: `ต้องการอนุมัติการเข้าร่วมอบรมของพนักงานรหัส ${userid}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันอนุมัติ',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-accent px-8 mx-2',
                        cancelButton: 'btn btn-ghost px-8 mx-2'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังอนุมัติ...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        axios.post("{{ route("document.training.approveAttendance") }}", {
                                id,
                                userid,
                                project_id: '{{ $document->id }}'
                            })
                            .then((response) => {
                                if (response.data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'อนุมัติสำเร็จ',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => window.location.reload());
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: response.data.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                }
                            });
                    }
                });
            }
        </script>
    @endif
@endpush
