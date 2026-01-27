@if ($document->tasks->where("task_user", auth()->user()->userid)->count() > 0 && $document->status == "pending")
    <div class="card bg-base-100 border-base-200 mb-8 mt-6 overflow-hidden border shadow-xl">
        <div class="bg-base-200/50 border-base-200 flex items-center gap-2 border-b px-8 py-4">
            <i class="fas fa-tasks text-primary"></i>
            <span class="text-xs font-bold uppercase leading-none tracking-widest">Training Management Actions</span>
        </div>
        <div class="card-body p-8">
            @if ($document->training_id == null)
                <div class="flex flex-col items-center gap-6 md:flex-row">
                    <div class="bg-accent/10 rounded-2xl p-4">
                        <i class="fas fa-rocket text-accent text-4xl"></i>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h3 class="text-xl font-black">เริ่มต้นการฝึกอบรม</h3>
                        <p class="mt-1 text-sm opacity-50">สร้างโครงการฝึกอบรมในระบบเพื่อเปิดให้พนักงานเช็คอิน (Check-in)</p>
                    </div>
                    <button class="btn btn-accent btn-lg shadow-accent/20 w-full gap-2 rounded-full px-10 shadow-lg transition-all hover:scale-105 active:scale-95 md:w-auto" onclick="createTraining()">
                        <i class="fas fa-plus-circle"></i>
                        สร้างการฝึกอบรม
                    </button>
                </div>
            @else
                <div class="space-y-6">
                    <div class="bg-primary/5 border-primary/10 overflow-hidden rounded-2xl border">
                        <div class="bg-primary/10 border-primary/10 flex items-center justify-between border-b px-6 py-3">
                            <h3 class="text-primary text-sm font-bold uppercase tracking-wider">การประเมินผลผู้ตรวจประเมิน (Participant Assessment)</h3>
                            <button class="btn btn-primary btn-sm rounded-full" onclick="saveAllAssessments()">
                                <i class="fas fa-save mr-1"></i> บันทึกผลการประเมินทั้งหมด
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="table-sm table w-full border-separate border-spacing-0">
                                <thead>
                                    <tr class="bg-base-100">
                                        <th class="py-3 pl-6 text-[10px] font-bold uppercase opacity-50">ผู้เข้าร่วม</th>
                                        <th class="py-3 text-[10px] font-bold uppercase opacity-50">วันที่ประเมิน</th>
                                        <th class="py-3 text-[10px] font-bold uppercase opacity-50">วิธีการประเมิน</th>
                                        <th class="py-3 pr-6 text-[10px] font-bold uppercase opacity-50">คะแนน/สรุปผล</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($document->participants as $participant)
                                        @php
                                            $defaultDate = $participant->assetment_date ? $participant->assetment_date->format("Y-m-d") : date("Y-m-d");
                                        @endphp
                                        <tr class="hover:bg-base-200/50 assessment-row transition-colors" data-id="{{ $participant->id }}">
                                            <td class="border-base-200 border-t py-3 pl-6">
                                                <div class="text-xs font-bold">{{ $participant->participant_name }}</div>
                                                <div class="font-mono text-[10px] opacity-40">{{ $participant->participant }}</div>
                                            </td>
                                            <td class="border-base-200 border-t py-3">
                                                <input class="input input-bordered input-xs focus:input-primary assessment-date w-32" type="date" value="{{ $defaultDate }}">
                                            </td>
                                            <td class="border-base-200 border-t py-3">
                                                <select class="select select-bordered select-xs focus:select-primary assessment-type w-full max-w-[150px]">
                                                    <option value="" disabled {{ !$participant->assetment_type ? "selected" : "" }}>เลือกวิธี...</option>
                                                    <option value="P" {{ $participant->assetment_type == "P" ? "selected" : "" }}>P</option>
                                                    <option value="O" {{ $participant->assetment_type == "O" ? "selected" : "" }}>O</option>
                                                    <option value="I" {{ $participant->assetment_type == "I" ? "selected" : "" }}>I</option>
                                                </select>
                                            </td>
                                            <td class="border-base-200 border-t py-3 pr-6">
                                                <select class="select select-bordered select-xs focus:select-primary assessment-score w-full">
                                                    <option value="" disabled {{ !$participant->score ? "selected" : "" }}>คะแนน...</option>
                                                    <option value="3" {{ $participant->score == "3" ? "selected" : "" }}>3</option>
                                                    <option value="2" {{ $participant->score == "2" ? "selected" : "" }}>2</option>
                                                    <option value="1" {{ $participant->score == "1" ? "selected" : "" }}>1</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="text-error flex flex-col gap-2 text-xs">
                        <p>*วิธีการประเมิน P = ฝึกปฏิบัติจริง O = สังเกตการปฏิบัติงาน I = ถาม-ตอบ</p>
                        <p>*ผลการประเมิน 3 = ปฏิบัติงานและแก้ไขปัญหาได้ 2 = ปฏิบัติงานและแก้ไขปัญหาได้บ้าง 1 = การปฏิบัติงานยังต้องปรับปรุง</p>
                    </div>

                    <div class="divider"></div>

                    <div class="flex flex-col items-center gap-6 md:flex-row">
                        <div class="bg-warning/10 text-warning rounded-2xl p-4">
                            <i class="fas fa-flag-checkered text-4xl"></i>
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h3 class="text-xl font-black">เสร็จสิ้นการฝึกอบรม</h3>
                            <p class="mt-1 text-sm opacity-50">ปิดรับการเช็คอินและบันทึกประวัติการอบรมลงในฐานข้อมูลบุคลากร</p>
                        </div>
                        <button class="btn btn-warning btn-lg shadow-warning/20 w-full gap-2 rounded-full px-10 shadow-lg transition-all hover:scale-105 active:scale-95 md:w-auto" onclick="closeProject()">
                            <i class="fas fa-check-double"></i>
                            จบการฝึกอบรม
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

@if ($document->status == "complete")
    <div class="card bg-base-100 border-base-200 mb-8 mt-6 overflow-hidden border shadow-xl">
        <div class="bg-success/5 border-success/10 flex items-center gap-2 border-b px-8 py-4">
            <i class="fas fa-file-pdf text-success"></i>
            <span class="text-success text-xs font-bold uppercase leading-none tracking-widest">Training Report Available</span>
        </div>
        <div class="card-body p-8">
            <div class="flex flex-col items-center gap-6 md:flex-row">
                <div class="bg-success/10 text-success rounded-2xl p-4">
                    <i class="fas fa-check-circle text-4xl"></i>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-xl font-black">การฝึกอบรมเสร็จสมบูรณ์</h3>
                    <p class="mt-1 text-sm opacity-50">คุณสามารถดาวน์โหลดเอกสารสรุปผลการฝึกอบรมในรูปแบบ PDF ได้ที่นี่</p>
                </div>
                <a class="btn btn-success btn-lg shadow-success/20 w-full gap-2 rounded-full px-10 shadow-lg transition-all hover:scale-105 active:scale-95 md:w-auto" href="{{ route("document.training.downloadPDF", $document->id) }}">
                    <i class="fas fa-download"></i>
                    ดาวน์โหลด PDF
                </a>
            </div>
        </div>
    </div>
@endif

@push("scripts")
    <script>
        async function saveAllAssessments() {
            const rows = document.querySelectorAll('.assessment-row');
            let assessments = {};
            let isValid = true;

            rows.forEach(row => {
                const id = row.getAttribute('data-id');
                const date = row.querySelector('.assessment-date').value;
                const type = row.querySelector('.assessment-type').value;
                const score = row.querySelector('.assessment-score').value;

                if (!type || !score) {
                    isValid = false;
                }

                assessments[id] = {
                    date,
                    type,
                    score
                };
            });

            if (!isValid) {
                const confirmEmpty = await Swal.fire({
                    title: 'ข้อมูลไม่ครบถ้วน',
                    text: 'มีบางรายการยังไม่ได้เลือกวิธีการประเมินหรือคะแนน ต้องการบันทึกส่วนที่เหลือหรือไม่?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'บันทึกเท่าที่มี',
                    cancelButtonText: 'กลับไปแก้ไข',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary px-8 mx-2',
                        cancelButton: 'btn btn-ghost px-8 mx-2'
                    }
                });
                if (!confirmEmpty.isConfirmed) return;
            }

            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            axios.post("{{ route("document.training.saveAssessment") }}", {
                project_id: '{{ $document->id }}',
                assessments: assessments
            }).then((response) => {
                if (response.data.status == "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกผลการประเมินสำเร็จ',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: response.data.message
                    });
                }
            }).catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาดในการเชื่อมต่อ'
                });
            });
        }

        async function createTraining() {
            const result = await Swal.fire({
                title: 'สร้างการฝึกอบรม?',
                html: 'หลังจากสร้างการฝึกอบรมแล้ว <br>กรณีที่ต้องการยกเลิก กรุณาติดต่อ <b class="text-primary">ฝ่ายบุคคล (HR)</b>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันสร้างโครงการ',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-accent px-10 mx-2',
                    cancelButton: 'btn btn-ghost px-10 mx-2'
                },
            });

            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังดําเนินการ...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                axios.post("{{ route("document.training.createTraining") }}", {
                    project_id: '{{ $document->id }}',
                }).then((response) => {
                    if (response.data.status == "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'สร้างการฝึกอบรมสำเร็จ',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: response.data.message,
                            confirmButtonText: 'ตกลง',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                });
            }
        }

        async function closeProject() {
            const result = await Swal.fire({
                title: 'เสร็จสิ้นการฝึกอบรม?',
                html: 'ต้องการปิดโครงการฝึกอบรมนี้ ใช่หรือไม่? <br><b class="text-error">กรุณาบันทึกผลการประเมิณก่อน<br>หลังจากปิดโครงการจะไม่สามารถประเมินได้อีก</b> ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันปิดโครงการ',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-warning px-10 mx-2',
                    cancelButton: 'btn btn-ghost px-10 mx-2'
                },
            });

            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังดําเนินการ...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                axios.post("{{ route("document.training.closeProject") }}", {
                    project_id: '{{ $document->id }}',
                }).then((response) => {
                    if (response.data.status == "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'เสร็จสิ้นการฝึกอบรมสำเร็จ',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: response.data.message,
                            confirmButtonText: 'ตกลง',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                });
            }
        }
    </script>
@endpush
