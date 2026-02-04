@extends("layouts.app")
@section("content")
    <div class="mx-auto max-w-5xl pb-10">
        <!-- Header Section -->
        @include("document.header", ["title" => "ใบบันทึกการฝึกอบรมภาคอิสระ", "description" => "กรอกข้อมูลหลักสูตรและรายละเอียดผู้เข้าร่วมเพื่อขออนุมัติจัดฝึกอบรม", "icon" => "fas fa-graduation-cap"])

        <form class="space-y-8" id="create-form" action="{{ route("document.training.create") }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Approver Selection -->
            @include("document.approver_create")

            <!-- Training Details Card -->
            <div class="card bg-base-100 border-base-200 border shadow-xl">
                <div class="card-body p-0">
                    <div class="bg-base-200/50 border-base-200 flex items-center gap-2 border-b px-8 py-4">
                        <i class="fas fa-info-circle text-primary"></i>
                        <span class="text-sm font-bold uppercase tracking-wider">ข้อมูลรายละเอียดการฝึกอบรม</span>
                    </div>

                    <div class="space-y-6 p-8">
                        <!-- Course Name -->
                        <div class="form-control">
                            <label class="label pt-0">
                                <span class="label-text text-base-content/70 font-bold">ชื่อหลักสูตร <span class="text-error">*</span></span>
                            </label>
                            <input class="input input-bordered focus:input-primary w-full shadow-sm transition-all" id="training_name" name="training_name" type="text" placeholder="ระบุชื่อหลักสูตรการฝึกอบรม" />
                        </div>

                        <!-- Source/Origin -->
                        <div class="form-control">
                            <label class="label pb-1">
                                <span class="label-text text-base-content/70 font-bold">ที่มาของหลักสูตร <span class="text-error">*</span></span>
                            </label>
                            <div class="mt-2 space-y-4">
                                <!-- In Plan -->
                                <div class="bg-base-200/30 border-base-200 hover:border-primary/20 flex flex-wrap items-center gap-4 rounded-xl border p-4 transition-all">
                                    <input class="radio radio-primary radio-sm" id="src_in_plan" type="radio" name="source_type" value="in_plan" />
                                    <label class="flex flex-grow cursor-pointer items-center gap-2 font-medium" for="src_in_plan">
                                        จัดในแผน ลำดับที่
                                    </label>
                                    <input class="input input-bordered input-sm focus:input-primary w-32" type="text" name="plan_no" placeholder="..." />
                                    <span class="text-xs italic opacity-50">(อ้างอิงลำดับที่ในแผนประจำปี)</span>
                                </div>

                                <!-- Substitute -->
                                <div class="bg-base-200/30 border-base-200 hover:border-primary/20 flex flex-wrap items-center gap-4 rounded-xl border p-4 transition-all">
                                    <input class="radio radio-primary radio-sm" id="src_sub" type="radio" name="source_type" value="substitute" />
                                    <label class="flex cursor-pointer items-center gap-2 font-medium" for="src_sub">
                                        จัดแทนเรื่อง
                                    </label>
                                    <input class="input input-bordered input-sm focus:input-primary flex-grow" type="text" name="substitute_topic" placeholder="ชื่อวิชาในแผนที่ถูกแทน" />
                                    <span class="font-medium">เนื่องจาก</span>
                                    <input class="input input-bordered input-sm focus:input-primary flex-grow" type="text" name="substitute_reason" placeholder="เหตุผลที่จัดแทน" />
                                </div>

                                <!-- Out of Plan -->
                                <div class="bg-base-200/30 border-base-200 hover:border-primary/20 flex flex-wrap items-center gap-4 rounded-xl border p-4 transition-all">
                                    <input class="radio radio-primary radio-sm" id="src_out" type="radio" name="source_type" value="out_of_plan" />
                                    <label class="flex cursor-pointer items-center gap-2 font-medium" for="src_out">
                                        จัดนอกแผน เนื่องจาก
                                    </label>
                                    <input class="input input-bordered input-sm focus:input-primary flex-grow" type="text" name="out_of_plan_reason" placeholder="ระบุเหตุผลที่จัดนอกแผน" />
                                </div>
                            </div>
                        </div>

                        <!-- Date & Time Selection -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="label py-0">
                                    <span class="label-text text-base-content/70 font-bold">วันและเวลาที่ฝึกอบรม <span class="text-error">*</span></span>
                                </label>

                                <div class="bg-base-200/50 border-base-300 flex rounded-xl border p-1 shadow-inner">
                                    <label class="has-[:checked]:bg-primary has-[:checked]:text-primary-content flex cursor-pointer items-center gap-2 rounded-lg px-3 py-1.5 transition-all has-[:checked]:shadow-sm">
                                        <input class="hidden" type="radio" name="date_mode" value="range" checked />
                                        <i class="fas fa-calendar-range text-xs"></i>
                                        <span class="text-[10px] font-bold uppercase tracking-wider">แบบช่วงเวลา</span>
                                    </label>
                                    <label class="has-[:checked]:bg-primary has-[:checked]:text-primary-content flex cursor-pointer items-center gap-2 rounded-lg px-3 py-1.5 transition-all has-[:checked]:shadow-sm">
                                        <input class="hidden" type="radio" name="date_mode" value="specific" />
                                        <i class="fas fa-calendar-day text-xs"></i>
                                        <span class="text-[10px] font-bold uppercase tracking-wider">แบบระบุวันที่</span>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Range Mode -->
                                <div class="form-control space-y-2" id="range_mode_wrapper">
                                    <label class="label pt-0"><span class="label-text text-[11px] font-bold uppercase opacity-50">เลือกช่วงวันที่</span></label>
                                    <div class="join ring-base-300 focus-within:ring-primary/50 w-full overflow-hidden rounded-xl shadow-sm ring-1 transition-all">
                                        <div class="join-item bg-base-100 border-base-300 flex items-center border-r px-4">
                                            <i class="fas fa-calendar-alt text-primary/40"></i>
                                        </div>
                                        <input class="input input-ghost join-item focus:bg-base-100 w-full text-sm font-medium" id="start_date" type="date" name="start_date" />
                                        <div class="join-item bg-base-200 border-base-300 flex items-center border-x px-3">
                                            <i class="fas fa-long-arrow-right opacity-30"></i>
                                        </div>
                                        <input class="input input-ghost join-item focus:bg-base-100 w-full text-sm font-medium" id="end_date" type="date" name="end_date" />
                                    </div>
                                </div>

                                <!-- Range Time Wrapper (Shared for Range Mode) -->
                                <div class="form-control space-y-2" id="range_time_container">
                                    <label class="label pt-0"><span class="label-text text-[11px] font-bold uppercase opacity-50">ช่วงเวลา (ร่วมทุกวัน)</span></label>
                                    <div class="join ring-base-300 focus-within:ring-primary/50 w-full overflow-hidden rounded-xl shadow-sm ring-1 transition-all">
                                        <div class="join-item bg-base-100 border-base-300 flex items-center border-r px-4">
                                            <i class="fas fa-clock text-primary/40"></i>
                                        </div>
                                        <input class="input input-ghost join-item focus:bg-base-100 w-full text-sm font-medium" id="start_time" type="time" name="start_time" />
                                        <div class="join-item bg-base-200 border-base-300 flex items-center border-x px-3">
                                            <i class="fas fa-ellipsis-v opacity-10"></i>
                                        </div>
                                        <input class="input input-ghost join-item focus:bg-base-100 w-full text-sm font-medium" id="end_time" type="time" name="end_time" />
                                    </div>
                                    <div class="text-base-content/40 px-1 text-[10px] font-medium italic">
                                        <i class="fas fa-info-circle mr-1 text-[8px]"></i> ใช้เวลานี้กับทุกวันที่อยู่ในช่วงที่ระบุ
                                    </div>
                                </div>

                                <!-- Specific Mode Wrapper (Full width when active) -->
                                <div class="col-span-full hidden space-y-4" id="specific_mode_wrapper">
                                    <div class="bg-base-200/30 border-base-200 space-y-3 rounded-2xl border p-4" id="specific_date_list">
                                        <div class="bg-base-100 border-base-200 hover:border-primary/30 group relative rounded-xl border p-4 shadow-sm transition-all">
                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                <div class="form-control">
                                                    <label class="label pt-0"><span class="label-text text-[10px] font-bold uppercase tracking-wider opacity-50">วันที่</span></label>
                                                    <div class="relative">
                                                        <i class="fas fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-xs opacity-20"></i>
                                                        <input class="input input-bordered focus:input-primary h-10 w-full pl-9 text-sm font-medium" type="date" name="specific_date[]" />
                                                    </div>
                                                </div>
                                                <div class="form-control">
                                                    <label class="label pt-0"><span class="label-text text-[10px] font-bold uppercase tracking-wider opacity-50">เวลาเริ่ม</span></label>
                                                    <div class="relative">
                                                        <i class="fas fa-hourglass-start absolute left-3 top-1/2 -translate-y-1/2 text-xs opacity-20"></i>
                                                        <input class="input input-bordered focus:input-primary h-10 w-full pl-9 text-sm font-medium" type="time" name="specific_start_time[]" />
                                                    </div>
                                                </div>
                                                <div class="form-control">
                                                    <label class="label pt-0"><span class="label-text text-[10px] font-bold uppercase tracking-wider opacity-50">เวลาสิ้นสุด</span></label>
                                                    <div class="relative">
                                                        <i class="fas fa-hourglass-end absolute left-3 top-1/2 -translate-y-1/2 text-xs opacity-20"></i>
                                                        <input class="input input-bordered focus:input-primary h-10 w-full pl-9 text-sm font-medium" type="time" name="specific_end_time[]" />
                                                    </div>
                                                </div>
                                            </div>
                                            <button class="btn btn-ghost btn-circle btn-xs text-error bg-base-100 absolute -right-2 -top-2 cursor-default opacity-0 shadow-sm transition-all group-hover:opacity-100" type="button">
                                                <i class="fas fa-times text-[10px]"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline btn-primary btn-sm bg-base-100 gap-2 rounded-xl font-bold shadow-sm transition-all hover:-translate-y-0.5" type="button" onclick="addSpecificDateLine()">
                                        <i class="fas fa-plus-circle"></i> เพิ่มวันที่และเวลาฝึกอบรม
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="form-control">
                            <label class="label mb-2">
                                <span class="label-text text-base-content/70 text-xs font-bold uppercase tracking-wider">รวมเวลาทั้งหมด (Total Duration) <span class="text-error">*</span></span>
                            </label>

                            <div class="bg-primary/5 border-primary/20 ring-primary/5 flex w-fit items-center gap-6 rounded-2xl border p-4 shadow-sm ring-4">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="relative">
                                        <input class="input input-bordered bg-base-100 focus:ring-primary/20 w-24 text-center text-lg font-bold shadow-inner transition-all focus:ring-2" id="duration_hours" type="number" name="duration_hours" placeholder="0" min="0" />
                                        <div class="badge badge-primary badge-xs absolute -right-2 -top-2">h</div>
                                    </div>
                                    <span class="text-primary text-[10px] font-bold uppercase tracking-widest opacity-60">ชั่วโมง</span>
                                </div>

                                <div class="text-primary text-2xl font-light opacity-30">:</div>

                                <div class="flex flex-col items-center gap-1">
                                    <div class="relative">
                                        <input class="input input-bordered bg-base-100 focus:ring-primary/20 w-24 text-center text-lg font-bold shadow-inner transition-all focus:ring-2" id="duration_minutes" type="number" name="duration_minutes" placeholder="0" min="0" max="59" />
                                        <div class="badge badge-secondary badge-xs absolute -right-2 -top-2">m</div>
                                    </div>
                                    <span class="text-secondary text-[10px] font-bold uppercase tracking-widest opacity-60">นาที</span>
                                </div>

                                <div class="divider divider-horizontal mx-0 opacity-20"></div>

                                <div class="flex items-center gap-3 pr-2">
                                    <div class="bg-primary/10 rounded-full p-3">
                                        <i class="fas fa-history text-primary transition-all"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-black uppercase tracking-widest opacity-40">Auto-Calculate</span>
                                        <span class="text-primary text-[10px] font-bold italic">เวลาสุทธิ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Mentors & Participants Grid -->
            <div class="grid grid-cols-1 gap-8">
                <!-- Mentors -->
                <div class="card bg-base-100 border-base-200 overflow-hidden border shadow-xl">
                    <div class="bg-base-200/50 border-base-200 flex items-center justify-between border-b px-8 py-4">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user-tie text-primary"></i>
                            <span class="text-sm font-bold uppercase tracking-wide">รายชื่อวิทยากร (Mentors)</span>
                        </div>
                        <button class="btn btn-primary btn-sm pulse-on-hover rounded-full" type="button" onclick="openMentorModal()">
                            <i class="fas fa-plus mr-1"></i> เพิ่มวิทยากร
                        </button>
                    </div>
                    <div class="p-0">
                        <table class="table w-full" id="mentor-table">
                            <thead>
                                <tr class="bg-base-100 italic">
                                    <th class="pl-8">รหัสพนักงาน</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>ตำแหน่ง</th>
                                    <th class="pr-8 text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-base-100 divide-y">
                                <!-- Empty state by default -->
                            </tbody>
                        </table>
                        <div class="py-8 text-center text-sm italic opacity-40" id="mentor-empty">ยังไม่มีรายชื่อวิทยากร</div>
                    </div>
                </div>

                <!-- Participants -->
                <div class="card bg-base-100 border-base-200 overflow-hidden border shadow-xl">
                    <div class="bg-base-200/50 border-base-200 flex items-center justify-between border-b px-8 py-4">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-users text-primary"></i>
                            <span class="text-sm font-bold uppercase tracking-wide">รายชื่อผู้เข้าร่วม (Participants) <span class="text-error">*</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="btn btn-outline btn-primary btn-sm pulse-on-hover rounded-full" type="button" onclick="openDepartmentModal()">
                                <i class="fas fa-building mr-1"></i> เพิ่มจากแผนก
                            </button>
                            <button class="btn btn-primary btn-sm pulse-on-hover rounded-full" type="button" onclick="openParticipantModal()">
                                <i class="fas fa-plus mr-1"></i> เพิ่มผู้เข้าร่วม
                            </button>
                        </div>
                    </div>
                    <div class="p-0">
                        <table class="table w-full" id="participant-table">
                            <thead>
                                <tr class="bg-base-100 italic">
                                    <th class="pl-8">รหัสพนักงาน</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>ตำแหน่ง / แผนก</th>
                                    <th class="pr-8 text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-base-100 divide-y">
                                <!-- Empty state by default -->
                            </tbody>
                        </table>
                        <div class="py-8 text-center text-sm italic opacity-40" id="participant-empty">ยังไม่มีรายชื่อผู้เข้าร่วม</div>
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            <div class="card bg-base-100 border-base-200 border shadow-xl">
                <div class="card-body p-0">
                    <div class="bg-base-200/50 border-base-200 flex items-center gap-2 border-b px-8 py-4">
                        <i class="fas fa-paperclip text-primary"></i>
                        <span class="text-sm font-bold uppercase tracking-wide">เอกสารประกอบการฝึกอบรม (Attachments)</span>
                    </div>
                    <div class="p-8">
                        <div class="border-base-200 hover:border-primary/30 hover:bg-primary/5 group cursor-pointer rounded-2xl border-4 border-dashed p-10 text-center transition-all" id="drop-area">
                            <input class="hidden" id="file_input" type="file" name="document_files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                            <div class="bg-base-200 group-hover:bg-primary group-hover:text-primary-content mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full shadow-inner transition-all">
                                <i class="fas fa-cloud-upload-alt text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-bold">ลากและวางไฟล์ที่นี่</h4>
                            <p class="mt-1 text-sm opacity-50">หรือ <span class="text-primary font-bold">คลิกเพื่อเลือกไฟล์</span> จากเครื่องของคุณ</p>
                            <div class="mt-4 text-[10px] font-bold uppercase tracking-widest opacity-40">Max 20 Files • PDF, JPG, PNG, Word , Excel , PPT (100 MB)</div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3" id="file_display">
                            {{-- files dynamically inserted here --}}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button Area -->
            <div class="sticky bottom-8 z-30 mt-10 flex justify-center">
                <button class="btn btn-primary btn-lg gap-3 rounded-full px-12 shadow-2xl transition-all hover:scale-105 active:scale-95" type="submit" onclick="submitForm()">
                    <i class="fas fa-paper-plane"></i>
                    <span class="text-lg">สร้างเอกสารการฝึกอบรม</span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push("scripts")
    <script>
        let files = [];
        let fileInput;

        document.addEventListener('DOMContentLoaded', function() {
            const dropArea = document.getElementById('drop-area');
            fileInput = document.getElementById('file_input');
            const fileDisplay = document.getElementById('file_display');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });

            dropArea.addEventListener('drop', handleDrop, false);
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });
            dropArea.addEventListener('click', function() {
                fileInput.click();
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight() {
                dropArea.classList.add('border-primary', 'bg-primary/5');
            }

            function unhighlight() {
                dropArea.classList.remove('border-primary', 'bg-primary/5');
            }

            function handleDrop(e) {
                handleFiles(e.dataTransfer.files);
            }

            function handleFiles(newFiles) {
                const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
                const maxFiles = 20;
                const maxSize = 100 * 1024 * 1024; // 100MB

                newFiles = Array.from(newFiles);
                let errors = [];

                newFiles.forEach(file => {
                    const ext = file.name.split('.').pop().toLowerCase();

                    // Validate Extension
                    if (!allowedExtensions.includes(ext)) {
                        errors.push(`ไฟล์ <b>${file.name}</b> ประเภทไฟล์ไม่ถูกต้อง`);
                        return;
                    }

                    // Validate Max Files
                    if (files.length >= maxFiles) {
                        if (!errors.includes('จำกัดจำนวนไฟล์สูงสุด 20 ไฟล์')) {
                            errors.push('จำกัดจำนวนไฟล์สูงสุด 20 ไฟล์');
                        }
                        return;
                    }

                    // Validate Duplicate
                    if (!files.some(f => f.name === file.name && f.size === file.size)) {
                        const currentTotalSize = files.reduce((sum, f) => sum + f.size, 0);
                        if (currentTotalSize + file.size > maxSize) {
                            if (!errors.includes('ขนาดไฟล์รวมเกิน 100 MB')) {
                                errors.push('ขนาดไฟล์รวมเกิน 100 MB');
                            }
                            return;
                        }
                        files.push(file);
                    }
                });

                if (errors.length > 0) {
                    Swal.fire({
                        title: 'พบข้อผิดพลาดในการอัปโหลด',
                        html: `<div class="text-left text-sm opacity-70">${errors.map(e => `• ${e}`).join('<br>')}</div>`,
                        icon: 'error',
                        confirmButtonText: 'ตกลง',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-primary px-10'
                        }
                    });
                }

                updateFileDisplay();
            }

            function updateFileDisplay() {
                fileDisplay.innerHTML = '';
                files.forEach((file, index) => {
                    const fileElement = document.createElement('div');
                    fileElement.className = 'flex items-center justify-between gap-3 bg-base-200/50 p-3 rounded-xl border border-base-300 group hover:border-primary/30 transition-all';
                    fileElement.innerHTML = `
                        <div class="flex items-center gap-3 overflow-hidden">
                            <i class="fas fa-file text-primary/60"></i>
                            <span class="text-xs font-bold truncate">${file.name}</span>
                        </div>
                        <button type="button" class="btn btn-circle btn-ghost btn-xs text-error opacity-0 group-hover:opacity-100 transition-all" onclick="removeFile(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    fileDisplay.appendChild(fileElement);
                });
                updateFileInput();
            }

            window.removeFile = function(index) {
                files.splice(index, 1);
                updateFileDisplay();
            }

            function updateFileInput() {
                const dataTransfer = new DataTransfer();
                files.forEach(file => dataTransfer.items.add(file));
                fileInput.files = dataTransfer.files;
            }

            // --- Duration Calculation Logic ---
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            const durationHoursInput = document.getElementById('duration_hours');
            const durationMinutesInput = document.getElementById('duration_minutes');

            window.addSpecificDateLine = function() {
                const container = document.getElementById('specific_date_list');
                const newLine = document.createElement('div');
                newLine.className = 'bg-base-100 border-base-200 group relative rounded-xl border p-4 shadow-sm transition-all hover:border-primary/30 mt-3';
                newLine.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-[10px] font-bold uppercase tracking-wider opacity-50">วันที่</span></label>
                            <div class="relative">
                                <i class="fas fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-xs opacity-20"></i>
                                <input class="input input-bordered focus:input-primary h-10 w-full pl-9 text-sm font-medium" type="date" name="specific_date[]" />
                            </div>
                        </div>
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-[10px] font-bold uppercase tracking-wider opacity-50">เวลาเริ่ม</span></label>
                             <div class="relative">
                                <i class="fas fa-hourglass-start absolute left-3 top-1/2 -translate-y-1/2 text-xs opacity-20"></i>
                                <input class="input input-bordered focus:input-primary h-10 w-full pl-9 text-sm font-medium" type="time" name="specific_start_time[]" />
                            </div>
                        </div>
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-[10px] font-bold uppercase tracking-wider opacity-50">เวลาสิ้นสุด</span></label>
                            <div class="relative">
                                <i class="fas fa-hourglass-end absolute left-3 top-1/2 -translate-y-1/2 text-xs opacity-20"></i>
                                <input class="input input-bordered focus:input-primary h-10 w-full pl-9 text-sm font-medium" type="time" name="specific_end_time[]" />
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-ghost btn-circle btn-xs text-error absolute -top-2 -right-2 bg-base-100 shadow-sm opacity-0 group-hover:opacity-100 transition-all border border-base-200" onclick="this.closest('.group').remove(); calculateDuration();">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                `;
                container.appendChild(newLine);
                newLine.querySelectorAll('input').forEach(input => {
                    input.addEventListener('change', calculateDuration);
                });
            }


            function calculateDuration() {
                const mode = document.querySelector('input[name="date_mode"]:checked').value;
                let totalMinutes = 0;

                if (mode === 'range') {
                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value;
                    const startTime = startTimeInput.value;
                    const endTime = endTimeInput.value;

                    if (startDate && endDate && startTime && endTime) {
                        const start = new Date(startDate);
                        const end = new Date(endDate);
                        if (end >= start) {
                            const [hStart, mStart] = startTime.split(':').map(Number);
                            const [hEnd, mEnd] = endTime.split(':').map(Number);
                            const dailyMinutes = (hEnd * 60 + mEnd) - (hStart * 60 + mStart);
                            if (dailyMinutes >= 0) {
                                const totalDays = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)) + 1;
                                totalMinutes = dailyMinutes * totalDays;
                            }
                        }
                    }
                } else {
                    const specificGroups = document.querySelectorAll('#specific_date_list .group');
                    specificGroups.forEach(group => {
                        const date = group.querySelector('input[name="specific_date[]"]').value;
                        const sTime = group.querySelector('input[name="specific_start_time[]"]').value;
                        const eTime = group.querySelector('input[name="specific_end_time[]"]').value;

                        if (date && sTime && eTime) {
                            const [hS, mS] = sTime.split(':').map(Number);
                            const [hE, mE] = eTime.split(':').map(Number);
                            const diff = (hE * 60 + mE) - (hS * 60 + mS);
                            if (diff > 0) totalMinutes += diff;
                        }
                    });
                }

                if (totalMinutes > 0) {
                    durationHoursInput.value = Math.floor(totalMinutes / 60);
                    durationMinutesInput.value = totalMinutes % 60;
                } else {
                    durationHoursInput.value = 0;
                    durationMinutesInput.value = 0;
                }
            }

            // Listen for changes
            [startDateInput, endDateInput, startTimeInput, endTimeInput].forEach(input => {
                input.addEventListener('change', calculateDuration);
            });

            document.querySelectorAll('#specific_date_list input').forEach(input => {
                input.addEventListener('change', calculateDuration);
            });

            document.querySelectorAll('input[name="date_mode"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const isRange = this.value === 'range';
                    document.getElementById('range_mode_wrapper').classList.toggle('hidden', !isRange);
                    document.getElementById('specific_mode_wrapper').classList.toggle('hidden', isRange);
                    document.getElementById('range_time_container').classList.toggle('hidden', !isRange);
                    calculateDuration();
                });
            });
        });

        // Search User Function
        async function searchUser(userid) {
            try {
                const response = await axios.post('{{ route("user.search") }}', {
                    userid: userid
                });
                return response.data.user;
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่พบข้อมูล',
                    text: 'ไม่พบข้อมูลพนักงานรหัสนี้ในระบบ',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return null;
            }
        }

        function toggleEmptyStates() {
            const mentorTable = document.querySelector('#mentor-table tbody');
            const participantTable = document.querySelector('#participant-table tbody');
            document.getElementById('mentor-empty').style.display = mentorTable.children.length > 0 ? 'none' : 'block';
            document.getElementById('participant-empty').style.display = participantTable.children.length > 0 ? 'none' : 'block';
        }

        function addRowToTable(tableId, data, hiddenPrefix) {
            // Check for duplicates
            const existing = document.querySelector(`#${tableId} input[name="${hiddenPrefix}_userid[]"][value="${data.userid}"]`);
            if (existing) return;

            const tableBody = document.querySelector(`#${tableId} tbody`);
            const row = document.createElement('tr');
            row.className = 'hover:bg-base-200/20 transition-all';

            let cells = `
                <td class="pl-8 py-4">
                    <span class="badge badge-neutral font-bold">${data.userid}</span>
                    <input type="hidden" name="${hiddenPrefix}_userid[]" value="${data.userid}">
                </td>
                <td class="py-4 font-bold text-sm">${data.name} <input type="hidden" name="${hiddenPrefix}_name[]" value="${data.name}"></td>
                <td class="py-4">
                    <div class="text-xs font-bold opacity-70">${data.position}</div>
                    ${data.department ? `<div class="text-[10px] opacity-40 uppercase">${data.department}</div>` : ''}
                    <input type="hidden" name="${hiddenPrefix}_position[]" value="${data.position}">
                    ${data.department ? `<input type="hidden" name="${hiddenPrefix}_dept[]" value="${data.department}">` : ''}
                </td>
                <td class="text-center pr-8 py-4">
                    <button type="button" class="btn btn-ghost btn-circle btn-sm text-error hover:bg-error/10" onclick="this.closest('tr').remove(); toggleEmptyStates();">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;

            row.innerHTML = cells;
            tableBody.appendChild(row);
            toggleEmptyStates();
        }

        // Mentor Modal
        async function openMentorModal() {
            const {
                value: formValues
            } = await Swal.fire({
                title: 'เพิ่มวิทยากร',
                html: `
                    <div class="text-left py-4 space-y-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold">รหัสพนักงาน</span></label>
                            <div class="join w-full">
                                <input id="swal-userid" class="input input-bordered join-item w-full" placeholder="Ex: 650000">
                                <button type="button" id="swal-search" class="btn btn-primary join-item px-6"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold">ชื่อ-นามสกุล</span></label>
                            <input id="swal-name" class="input input-bordered w-full" placeholder="ชื่อวิทยากร">
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold">ตำแหน่ง</span></label>
                            <input id="swal-position" class="input input-bordered w-full" placeholder="ตำแหน่ง">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'เพิ่มรายชื่อ',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'btn btn-primary px-10 mx-2',
                    cancelButton: 'btn btn-ghost px-10 mx-2',
                    popup: 'rounded-3xl shadow-2xl border border-base-200'
                },
                buttonsStyling: false,
                didOpen: () => {
                    const searchBtn = document.getElementById('swal-search');
                    searchBtn.addEventListener('click', async () => {
                        const id = document.getElementById('swal-userid').value;
                        if (!id) return;
                        searchBtn.classList.add('loading');
                        const data = await searchUser(id);
                        searchBtn.classList.remove('loading');
                        if (data) {
                            document.getElementById('swal-name').value = data.name || '';
                            document.getElementById('swal-position').value = data.position || '';
                        }
                    });
                },
                preConfirm: () => {
                    const userid = document.getElementById('swal-userid').value;
                    const name = document.getElementById('swal-name').value;
                    if (!userid || !name) return Swal.showValidationMessage('กรุณากรอกข้อมูลให้ครบถ้วน');
                    return {
                        userid,
                        name,
                        position: document.getElementById('swal-position').value
                    }
                }
            });
            if (formValues) addRowToTable('mentor-table', formValues, 'mentors');
        }

        // Participant Modal
        async function openParticipantModal() {
            const {
                value: formValues
            } = await Swal.fire({
                title: 'เพิ่มผู้เข้าร่วม',
                html: `
                    <div class="text-left py-4 space-y-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold">รหัสพนักงาน</span></label>
                            <div class="join w-full">
                                <input id="swal-userid" class="input input-bordered join-item w-full" placeholder="Ex: 650000">
                                <button type="button" id="swal-search-p" class="btn btn-primary join-item px-6"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold">ชื่อ-นามสกุล</span></label>
                            <input id="swal-name" class="input input-bordered w-full" placeholder="ชื่อพนักงาน">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold">ตำแหน่ง</span></label>
                                <input id="swal-position" class="input input-bordered w-full" placeholder="ตำแหน่ง">
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold">แผนก</span></label>
                                <input id="swal-dept" class="input input-bordered w-full" placeholder="แผนก">
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'เพิ่มผู้เข้าร่วม',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'btn btn-primary px-10 mx-2',
                    cancelButton: 'btn btn-ghost px-10 mx-2',
                    popup: 'rounded-3xl shadow-2xl border border-base-200'
                },
                buttonsStyling: false,
                didOpen: () => {
                    const searchBtn = document.getElementById('swal-search-p');
                    searchBtn.addEventListener('click', async () => {
                        const id = document.getElementById('swal-userid').value;
                        if (!id) return;
                        searchBtn.classList.add('loading');
                        const data = await searchUser(id);
                        searchBtn.classList.remove('loading');
                        if (data) {
                            document.getElementById('swal-name').value = data.name || '';
                            document.getElementById('swal-position').value = data.position || '';
                            document.getElementById('swal-dept').value = data.department || '';
                        }
                    });
                },
                preConfirm: () => {
                    const userid = document.getElementById('swal-userid').value;
                    const name = document.getElementById('swal-name').value;
                    if (!userid || !name) return Swal.showValidationMessage('กรุณากรอกข้อมูลให้ครบถ้วน');
                    return {
                        userid,
                        name,
                        position: document.getElementById('swal-position').value,
                        department: document.getElementById('swal-dept').value
                    }
                }
            });
            if (formValues) addRowToTable('participant-table', formValues, 'participants');
        }

        function submitForm() {
            event.preventDefault();
            let missingFields = [];

            if (!document.getElementById('training_name').value) missingFields.push('ชื่อหลักสูตร');
            if (!document.querySelector('input[name="source_type"]:checked')) missingFields.push('ที่มาหลักสูตร');

            const dateMode = document.querySelector('input[name="date_mode"]:checked').value;
            if (dateMode === 'range') {
                if (!document.querySelector('input[name="start_date"]').value || !document.querySelector('input[name="end_date"]').value) {
                    missingFields.push('วันที่ฝึกอบรม (ระบุช่วงเวลา)');
                }
            } else {
                const specificDates = Array.from(document.querySelectorAll('input[name="specific_date[]"]')).filter(i => i.value);
                if (specificDates.length === 0) {
                    missingFields.push('วันที่ฝึกอบรม (ระบุวันที่)');
                } else {
                    // Check if each date has a time
                    const specificGroups = document.querySelectorAll('#specific_date_list .group');
                    specificGroups.forEach((group, idx) => {
                        const date = group.querySelector('input[name="specific_date[]"]').value;
                        const sTime = group.querySelector('input[name="specific_start_time[]"]').value;
                        const eTime = group.querySelector('input[name="specific_end_time[]"]').value;
                        if (date && (!sTime || !eTime)) {
                            missingFields.push(`กรุณาระบุเวลาสำหรับวันที่รายการที่ ${idx + 1}`);
                        }
                    });
                }
            }

            if (document.querySelectorAll('input[name="participants_userid[]"]').length === 0) missingFields.push('รายชื่อผู้เข้าร่วม');
            if (files.length === 0) missingFields.push('เอกสารแนบประกอบการอบรม');

            if (missingFields.length > 0) {
                Swal.fire({
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    html: `<div class="text-left mt-2"><ul class="list-disc pl-5"><li>${missingFields.join('</li><li>')}</li></ul></div>`,
                    icon: 'error',
                    confirmButtonText: 'รับทราบ',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary px-10'
                    }
                });
                return;
            }

            // Check total file size (User requirement: max 100MB)
            const totalSize = files.reduce((sum, f) => sum + f.size, 0);
            if (totalSize > 100 * 1024 * 1024) {
                Swal.fire({
                    title: 'ขนาดไฟล์รวมใหญ่เกินไป',
                    html: `ขนาดไฟล์รวมทั้งหมดคือ <b>${(totalSize / (1024 * 1024)).toFixed(2)} MB</b><br>กรุณาลดขนาดไฟล์รวมให้ไม่เกิน <b>100 MB</b>`,
                    icon: 'error',
                    confirmButtonText: 'ตกลง',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary px-10'
                    }
                });
                return;
            }

            Swal.fire({
                title: 'ยืนยันการสร้างเอกสาร?',
                text: "กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนกดตกลง",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันสร้างเอกสาร',
                cancelButtonText: 'ตรวจสอบอีกครั้ง',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary px-10 mx-2',
                    cancelButton: 'btn btn-ghost px-10 mx-2'
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังสร้างเอกสาร...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById('create-form').submit();
                }
            });
        }

        // Department Modal
        async function openDepartmentModal() {
            const deptsList = @json($departments ?? []);

            if (deptsList.length === 0) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'ไม่มีข้อมูลแผนก',
                    text: 'ไม่พบข้อมูลแผนกในระบบ'
                });
            }

            // Step 1: Select Mode
            const {
                value: mode
            } = await Swal.fire({
                title: 'เพิ่มผู้เข้าร่วมจากแผนก',
                text: 'เลือกรูปแบบการดึงข้อมูล',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'เพิ่มทั้งแผนก',
                denyButtonText: 'เลือกตามตำแหน่ง',
                showDenyButton: true,
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'btn btn-primary px-6 mx-1',
                    denyButton: 'btn btn-outline btn-primary px-6 mx-1',
                    cancelButton: 'btn btn-ghost px-6 mx-1',
                    popup: 'rounded-3xl shadow-2xl border border-base-200'
                },
                buttonsStyling: false,
            });

            if (mode === undefined) return; // Cancel or close

            const isAllMode = mode === true; // confirmButton returns true

            // Step 2: Select Department
            const deptOptions = {};
            deptsList.forEach(dept => {
                deptOptions[dept] = dept;
            });

            const {
                value: selectedDept
            } = await Swal.fire({
                title: 'เลือกแผนก',
                input: 'select',
                inputOptions: deptOptions,
                inputPlaceholder: 'กรุณาเลือกแผนก',
                showCancelButton: true,
                confirmButtonText: isAllMode ? 'ค้นหารายชื่อแผนก' : 'ต่อไป',
                cancelButtonText: 'ย้อนกลับ',
                customClass: {
                    confirmButton: 'btn btn-primary px-10 mx-2',
                    cancelButton: 'btn btn-ghost px-10 mx-2',
                    popup: 'rounded-3xl shadow-2xl border border-base-200'
                },
                buttonsStyling: false,
                preConfirm: (value) => {
                    if (!value) return Swal.showValidationMessage('กรุณาเลือกแผนก');
                    return value;
                }
            });

            if (!selectedDept) {
                if (selectedDept === "") return; // just closed
                return openDepartmentModal(); // Go back
            }

            let selectedPosition = null;
            if (!isAllMode) {
                // Step 3: Fetch and Select Position
                Swal.fire({
                    title: 'กำลังดึงข้อมูลตำแหน่ง...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await axios.post('{{ route("user.position") }}', {
                        department: selectedDept
                    });
                    if (response.data.status) {
                        const positions = response.data.positions || [];
                        const posOptions = {};
                        positions.forEach(p => {
                            if (p) posOptions[p] = p;
                        });

                        const {
                            value: pos
                        } = await Swal.fire({
                            title: 'เลือกตำแหน่ง',
                            input: 'select',
                            inputOptions: posOptions,
                            inputPlaceholder: 'รายชื่อตำแหน่งในแผนก',
                            showCancelButton: true,
                            confirmButtonText: 'ค้นหารายชื่อ',
                            cancelButtonText: 'ย้อนกลับ',
                            customClass: {
                                confirmButton: 'btn btn-primary px-10 mx-2',
                                cancelButton: 'btn btn-ghost px-10 mx-2',
                                popup: 'rounded-3xl shadow-2xl border border-base-200'
                            },
                            buttonsStyling: false,
                            preConfirm: (value) => {
                                if (!value) return Swal.showValidationMessage('กรุณาเลือกตำแหน่ง');
                                return value;
                            }
                        });

                        if (!pos) return; // Cancelled
                        selectedPosition = pos;
                    } else {
                        throw new Error('Positions not found');
                    }
                } catch (error) {
                    return Swal.fire({
                        icon: 'error',
                        title: 'ไม่พบข้อมูลตำแหน่ง',
                        text: 'ไม่สามารถดึงข้อมูลตำแหน่งของแผนกนี้ได้'
                    });
                }
            }

            // Step 4: Fetch Users
            Swal.fire({
                title: 'กำลังค้นรายชื่อพนักงาน...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const endpoint = selectedPosition ? '{{ route("user.formDepartmentPosition") }}' : '{{ route("user.formDepartment") }}';
                const postData = {
                    department: selectedDept
                };
                if (selectedPosition) postData.position = selectedPosition;

                const response = await axios.post(endpoint, postData);
                if (response.data.status == 1) {
                    const users = response.data.users || [];
                    if (users.length === 0) {
                        return Swal.fire({
                            icon: 'info',
                            title: 'ไม่พบพนักงาน',
                            text: 'ไม่พบพนักงานในเงื่อนไขที่ระบุ'
                        });
                    }

                    // Step 5: User Selection Modal
                    let userHtml = `
                        <div class="max-h-96 overflow-y-auto mt-4 px-2">
                            <div class="form-control mb-4">
                                <div class="bg-base-200 p-3 rounded-xl flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase opacity-50">เลือกทั้งหมด</span>
                                    <input type="checkbox" id="swal-select-all" class="checkbox checkbox-primary checkbox-sm">
                                </div>
                            </div>
                            <div class="space-y-2" id="swal-user-list">
                                ${users.map(u => `
                                                        <label class="flex items-center gap-4 p-3 bg-base-100 border border-base-200 rounded-xl hover:bg-base-200/50 cursor-pointer transition-all">
                                                            <input type="checkbox" name="swal-users" value="${u.userid}" data-user='${JSON.stringify(u)}' class="checkbox checkbox-primary checkbox-sm">
                                                            <div class="flex flex-col text-left overflow-hidden">
                                                                <span class="text-sm font-bold truncate">${u.name}</span>
                                                                <span class="text-[10px] opacity-40 uppercase truncate">${u.userid} | ${u.position}</span>
                                                            </div>
                                                        </label>
                                                    `).join('')}
                            </div>
                        </div>
                    `;

                    const {
                        value: selectedUsers
                    } = await Swal.fire({
                        title: 'เลือกรายชื่อผู้เข้าร่วม',
                        html: userHtml,
                        width: '32rem',
                        showCancelButton: true,
                        confirmButtonText: 'เพิ่มที่เลือก',
                        cancelButtonText: 'ยกเลิก',
                        customClass: {
                            confirmButton: 'btn btn-primary px-10 mx-2',
                            cancelButton: 'btn btn-ghost px-10 mx-2',
                            popup: 'rounded-3xl shadow-2xl border border-base-200'
                        },
                        buttonsStyling: false,
                        didOpen: () => {
                            const selectAll = document.getElementById('swal-select-all');
                            const userChecks = document.querySelectorAll('input[name="swal-users"]');
                            selectAll.addEventListener('change', (e) => {
                                userChecks.forEach(c => c.checked = e.target.checked);
                            });
                        },
                        preConfirm: () => {
                            const checked = Array.from(document.querySelectorAll('input[name="swal-users"]:checked'));
                            if (checked.length === 0) return Swal.showValidationMessage('กรุณาเลือกอย่างน้อย 1 รายชื่อ');
                            return checked.map(c => JSON.parse(c.getAttribute('data-user')));
                        }
                    });

                    if (selectedUsers) {
                        let added = 0;
                        selectedUsers.forEach(u => {
                            // Ensure department is passed correctly if it's not in the user object
                            if (!u.department) u.department = selectedDept;

                            const exists = document.querySelector(`#participant-table input[name="participants_userid[]"][value="${u.userid}"]`);
                            if (!exists) {
                                addRowToTable('participant-table', u, 'participants');
                                added++;
                            }
                        });

                        Swal.fire({
                            icon: 'success',
                            title: 'เพิ่มรายชื่อสำเร็จ',
                            text: `เพิ่มรายชื่อพนักงานจำนวน ${added} คน`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    throw new Error('API request failed');
                }
            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถดึงข้อมูลพนักงานได้'
                });
            }
        }
    </script>
@endpush
