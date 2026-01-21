@extends('layouts.app')
@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="from-primary/10 to-base-100 rounded-lg bg-gradient-to-r p-6">
            <h2 class="text-primary mb-2 text-3xl font-bold tracking-tight">
                <i class="fas fa-file-alt mr-2"></i> ใบบันทึกการฝึกอบรมภาคอิสระ
            </h2>
            <div class="divider opacity-50"></div>
            <p class="text-base-content/70">กรอกข้อมูลด้านล่างเพื่อสร้างเอกสารใหม่</p>
        </div>
        <form id="create-form" action="{{ route('document.training.create') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <span class="fas fa-exclamation-triangle mr-2"></span>
                    <span>มีข้อผิดพลาดในการสร้างเอกสาร</span>
                </div>
                @foreach ($errors->all() as $error)
                    <div>- {{ $error }}</div>
                @endforeach
            @endif
            @include('document.approver_create')
            <div class="card border-base-300 bg-base-100 mb-6 p-6 shadow-xl">
                <div class="form-control">
                    <label class="label pb-1">
                        <span class="label-text text-base-content/70 flex items-center font-medium">
                            <i class="fas fa-briefcase text-primary mr-2"></i> ชื่อหลักสูตร
                            <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <div>
                        <input class="input input-bordered w-full" id="training_name" name="training_name" type="text" />
                    </div>
                </div>

                <div class="form-control mt-3">
                    <label class="label pb-1">
                        <span class="label-text text-base-content/70 flex items-center font-medium">
                            <i class="fas fa-briefcase text-primary mr-2"></i> ที่มา <span class="text-red-500">*</span>
                        </span>
                    </label>

                    <div class="mb-3 flex flex-wrap items-center gap-4">
                        <input class="radio radio-primary" type="radio" name="source_type" value="in_plan" />
                        <span class="whitespace-nowrap">จัดในแผน ลำดับที่</span>
                        <input class="input input-bordered input-sm flex-grow" type="text" name="plan_no"
                            placeholder="..." />
                        <span class="text-sm opacity-70">(อ้างอิงลำดับที่ในแผนการฝึกอบรมประจำปี)</span>
                    </div>

                    <div class="mb-3 flex flex-wrap items-center gap-4">
                        <input class="radio radio-primary" type="radio" name="source_type" value="substitute" />
                        <span class="whitespace-nowrap">จัดแทนในแผน เรื่อง</span>
                        <input class="input input-bordered input-sm flex-grow" type="text" name="substitute_topic" />
                        <span class="whitespace-nowrap">เนื่องจาก</span>
                        <input class="input input-bordered input-sm flex-grow" type="text" name="substitute_reason" />
                    </div>

                    <div class="mb-3 flex flex-wrap items-center gap-4">
                        <input class="radio radio-primary" type="radio" name="source_type" value="out_of_plan" />
                        <span class="whitespace-nowrap">จัดนอกแผน เนื่องจาก</span>
                        <input class="input input-bordered input-sm flex-grow" type="text" name="out_of_plan_reason" />
                    </div>
                </div>

                <div class="form-control mt-3">
                    <div class="grid grid-cols-1 items-end gap-6 md:grid-cols-2">
                        <div class="form-control">
                            <label class="label pb-1">
                                <span class="label-text text-base-content/70 flex items-center font-medium">
                                    <i class="fas fa-calendar-alt text-primary mr-2"></i> วันที่เริ่มฝึกอบรม
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <input class="input input-bordered w-full" type="date" name="start_date" />
                        </div>

                        <div class="form-control">
                            <label class="label pb-1">
                                <span class="label-text text-base-content/70 flex items-center font-medium">
                                    <i class="fas fa-calendar-check text-primary mr-2"></i> วันที่สิ้นสุดการฝึกอบรม
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <input class="input input-bordered w-full" type="date" name="end_date" />
                        </div>
                    </div>
                </div>

                <div class="form-control mt-3">
                    <div class="grid grid-cols-1 items-end gap-6 md:grid-cols-3">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">เวลาเริ่ม</span>
                                <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <input class="input input-bordered w-full" type="time" name="start_time" />
                                <span>ถึง</span>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">เวลาสิ้นสุด</span>
                                <span class="text-red-500">*</span></label>
                            <input class="input input-bordered w-full" type="time" name="end_time" />
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">รวมเวลา (ชั่วโมง/นาที)</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input class="input input-bordered w-full text-center" type="number"
                                    name="duration_hours" placeholder="0" />
                                <span>ชม.</span>
                                <input class="input input-bordered w-full text-center" type="number"
                                    name="duration_minutes" placeholder="0" />
                                <span>น.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-control mt-3">
                    <div class="mb-2 flex items-center justify-between">
                        <h3 class="card-title text-primary flex items-center text-xl">
                            <i class="fas fa-user-tie mr-2"></i> รายชื่อวิทยากร (Mentors)
                        </h3>
                        <button class="btn btn-primary btn-sm" type="button" onclick="openMentorModal()">
                            <i class="fas fa-plus mr-1"></i> เพิ่มวิทยากร
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-lg">
                        <table class="table w-full" id="mentor-table">
                            <thead>
                                <tr class="bg-base-200">
                                    <th>รหัสพนักงาน</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>ตำแหน่ง</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-control mt-6">
                    <div class="mb-2 flex items-center justify-between">
                        <h3 class="card-title text-primary flex items-center text-xl">
                            <i class="fas fa-users mr-2"></i> รายชื่อผู้เข้าร่วม (Participants)
                            <span class="text-red-500 text-sm">*</span>
                        </h3>
                        <button class="btn btn-primary btn-sm" type="button" onclick="openParticipantModal()">
                            <i class="fas fa-plus mr-1"></i> เพิ่มผู้เข้าร่วม
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-lg">
                        <table class="table w-full" id="participant-table">
                            <thead>
                                <tr class="bg-base-200">
                                    <th>รหัสพนักงาน</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>ตำแหน่ง</th>
                                    <th>แผนก</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-control mt-3">
                    <h3 class="card-title text-primary mb-2 flex items-center text-xl">
                        <div>
                            <i class="fas fa-paperclip text-primary mr-2"></i>แนบเอกสารประกอบการฝึกอบรม
                            <span class="text-accent text-xs">* ใส่เอกสารแนบได้ไม่เกิน 20 ไฟล์</span>
                        </div>
                    </h3>
                    <div class="border-base-300 hover:border-primary cursor-pointer rounded-lg border-2 border-dashed p-6 text-center transition-all"
                        id="drop-area">
                        <input class="hidden" id="file_input" type="file" name="document_files[]" multiple>
                        <p class="text-base-content/70"><i class="fas fa-cloud-upload-alt mr-2"></i>
                            ลากและวางไฟล์ที่นี่ หรือ <span class="text-primary font-bold">คลิกเพื่อเลือกไฟล์</span>
                        </p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2" id="file_display">
                        {{-- display file in this div with remove file button --}}
                    </div>
                </div>

                <div class="mt-6 flex justify-center">
                    <button class="btn btn-accent gap-2 transition-all duration-200 hover:scale-105" type="submit"
                        onclick="submitForm()">
                        <i class="fas fa-paper-plane"></i> สร้างเอกสาร
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        let files = []; // To store selected files, accessible globally within this script block
        let fileInput; // Declare fileInput in a higher scope

        document.addEventListener('DOMContentLoaded', function() {
            const dropArea = document.getElementById('drop-area');
            fileInput = document.getElementById('file_input'); // Assign to the higher-scoped variable
            const fileDisplay = document.getElementById('file_display');

            // Prevent default drag behaviors
            ;
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            // Highlight drop area when item is dragged over it
            ;
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });

            ;
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });

            // Handle dropped files
            dropArea.addEventListener('drop', handleDrop, false);

            // Handle file input change
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });

            // Handle click on drop area to open file input
            dropArea.addEventListener('click', function() {
                fileInput.click();
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight() {
                dropArea.classList.add('border-primary');
                dropArea.classList.remove('border-base-300');
            }

            function unhighlight() {
                dropArea.classList.remove('border-primary');
                dropArea.classList.add('border-base-300');
            }

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const newFiles = dt.files;
                handleFiles(newFiles);
            }

            function handleFiles(newFiles) {
                newFiles = Array.from(newFiles);
                newFiles.forEach(file => {
                    if (!files.some(existingFile => existingFile.name === file.name && existingFile.size ===
                            file.size)) {
                        files.push(file);
                    }
                });
                updateFileDisplay();
            }

            function updateFileDisplay() {
                fileDisplay.innerHTML = ''; // Clear current display
                files.forEach((file, index) => {
                    const fileElement = document.createElement('div');
                    fileElement.className = 'flex items-center gap-2 bg-base-200 p-2 rounded-md';
                    fileElement.innerHTML = `
                                                                                <span class="text-sm">${file.name}</span>
                                                                                <button type="button" class="remove-file-btn text-error hover:text-error-focus" data-index="${index}">
                                                                                    <i class="fas fa-times-circle"></i>
                                                                                </button>
                                                                            `;
                    fileDisplay.appendChild(fileElement);
                });
                updateFileInput();
            }

            function updateFileInput() {
                const dataTransfer = new DataTransfer();
                files.forEach(file => dataTransfer.items.add(file));
                fileInput.files = dataTransfer.files;
            }

            // Handle remove file button click
            fileDisplay.addEventListener('click', function(e) {
                if (e.target.closest('.remove-file-btn')) {
                    const indexToRemove = parseInt(e.target.closest('.remove-file-btn').dataset.index);
                    files.splice(indexToRemove, 1); // Remove file from array
                    updateFileDisplay();
                }
            });
        });
    </script>
    <script>
        function addRowToTable(tableId, data, hiddenPrefix) {
            const tableBody = document.querySelector(`#${tableId} tbody`);
            const row = document.createElement('tr');

            let cells = `
                                                    <td>${data.userid} <input type="hidden" name="${hiddenPrefix}_userid[]" value="${data.userid}"></td>
                                                    <td>${data.name} <input type="hidden" name="${hiddenPrefix}_name[]" value="${data.name}"></td>
                                                    <td>${data.position} <input type="hidden" name="${hiddenPrefix}_position[]" value="${data.position}"></td>
                                                `;

            if (data.department) {
                cells +=
                    `<td>${data.department} <input type="hidden" name="${hiddenPrefix}_dept[]" value="${data.department}"></td>`;
            }

            cells += `<td class="text-center">
                                                    <button type="button" class="btn btn-ghost btn-xs text-error" onclick="this.closest('tr').remove()">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                  </td>`;

            row.innerHTML = cells;
            tableBody.appendChild(row);
        }

        // Search User Function
        async function searchUser(userid) {
            try {
                const response = await axios.post('{{ route('user.search') }}', {
                    userid: userid
                });

                return response.data.user;
            } catch (error) {
                Swal.fire('Error', 'ไม่พบข้อมูลพนักงาน', 'error');
                return null;
            }
        }

        // Mentor Modal
        async function openMentorModal() {
            const {
                value: formValues
            } = await Swal.fire({
                title: '<span class="text-2xl font-bold text-base-content">เพิ่มวิทยากร</span>',
                html: `
                                                        <div class="text-left p-2">
                                                            <div class="form-control w-full mb-4">
                                                                <label class="label"><span class="label-text font-semibold">รหัสพนักงาน</span></label>
                                                                <div class="flex gap-2">
                                                                    <input id="swal-userid" class="input input-bordered w-full focus:border-primary" placeholder="รหัสพนักงาน">
                                                                    <button type="button" id="swal-search" class="btn btn-primary px-6">
                                                                        <i class="fas fa-search mr-1"></i> ค้นหา
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <div class="form-control w-full mb-4">
                                                                <label class="label"><span class="label-text font-semibold">ชื่อ-นามสกุล</span></label>
                                                                <input id="swal-name" class="input input-bordered w-full" placeholder="ระบุชื่อวิทยากร">
                                                            </div>

                                                            <div class="form-control w-full">
                                                                <label class="label"><span class="label-text font-semibold">ตำแหน่ง</span></label>
                                                                <input id="swal-position" class="input input-bordered w-full" placeholder="ระบุตำแหน่ง">
                                                            </div>
                                                        </div>
                                                    `,
                showCancelButton: true,
                confirmButtonText: 'บันทึกข้อมูล',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'btn btn-primary px-8 mx-2',
                    cancelButton: 'btn btn-ghost px-8 mx-2',
                    popup: 'rounded-2xl shadow-2xl bg-base-100', // Matches DaisyUI card style
                },
                buttonsStyling: false, // Disables default Swal buttons to use Tailwind classes
                focusConfirm: false,
                didOpen: () => {
                    const searchBtn = document.getElementById('swal-search');
                    searchBtn.addEventListener('click', async () => {
                        const id = document.getElementById('swal-userid').value;
                        if (!id) return Swal.showValidationMessage('กรุณากรอกรหัสพนักงาน');

                        searchBtn.classList.add('loading'); // Add DaisyUI loading spinner
                        const data = await searchUser(id);
                        searchBtn.classList.remove('loading');

                        if (data) {
                            document.getElementById('swal-name').value = data.name || '';
                            document.getElementById('swal-position').value = data.position ||
                                '';
                        }
                    });
                },
                preConfirm: () => {
                    const userid = document.getElementById('swal-userid').value;
                    const name = document.getElementById('swal-name').value;
                    if (!userid || !name) {
                        Swal.showValidationMessage('กรุณากรอกข้อมูลให้ครบถ้วน');
                        return false;
                    }
                    return {
                        userid: userid,
                        name: name,
                        position: document.getElementById('swal-position').value
                    }
                }
            });

            if (formValues) {
                addRowToTable('mentor-table', formValues, 'mentors');
            }
        }

        // Participant Modal
        async function openParticipantModal() {
            const {
                value: formValues
            } = await Swal.fire({
                title: '<span class="text-2xl font-bold text-base-content">เพิ่มผู้เข้าร่วม</span>',
                html: `
                                                        <div class="text-left p-2">
                                                            <div class="form-control w-full mb-4">
                                                                <label class="label"><span class="label-text font-semibold">รหัสพนักงาน</span></label>
                                                                <div class="flex gap-2">
                                                                    <input id="swal-userid" class="input input-bordered w-full focus:border-primary" placeholder="รหัสพนักงาน">
                                                                    <button type="button" id="swal-search-p" class="btn btn-primary px-6">
                                                                        <i class="fas fa-search mr-1"></i> ค้นหา
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <div class="grid grid-cols-1 gap-4">
                                                                <div class="form-control w-full">
                                                                    <label class="label"><span class="label-text font-semibold text-base-content/70">ชื่อ-นามสกุล</span></label>
                                                                    <input id="swal-name" class="input input-bordered w-full" placeholder="ระบุชื่อผู้เข้าร่วม">
                                                                </div>

                                                                <div class="grid grid-cols-2 gap-4">
                                                                    <div class="form-control w-full">
                                                                        <label class="label"><span class="label-text font-semibold text-base-content/70">ตำแหน่ง</span></label>
                                                                        <input id="swal-position" class="input input-bordered w-full" placeholder="ตำแหน่ง">
                                                                    </div>
                                                                    <div class="form-control w-full">
                                                                        <label class="label"><span class="label-text font-semibold text-base-content/70">แผนก</span></label>
                                                                        <input id="swal-dept" class="input input-bordered w-full" placeholder="แผนก/ฝ่าย">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save mr-2"></i> บันทึกผู้เข้าร่วม',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    confirmButton: 'btn btn-primary px-8 mx-2',
                    cancelButton: 'btn btn-ghost px-8 mx-2',
                    popup: 'rounded-3xl shadow-2xl bg-base-100 border border-base-200',
                },
                buttonsStyling: false,
                focusConfirm: false,
                didOpen: () => {
                    const searchBtn = document.getElementById('swal-search-p');
                    searchBtn.addEventListener('click', async () => {
                        const id = document.getElementById('swal-userid').value;
                        if (!id) return Swal.showValidationMessage(
                            'กรุณากรอกรหัสพนักงานก่อนค้นหา');

                        searchBtn.classList.add('loading');
                        const data = await searchUser(
                            id); // Using the axios function created earlier
                        searchBtn.classList.remove('loading');

                        if (data) {
                            document.getElementById('swal-name').value = data.name || '';
                            document.getElementById('swal-position').value = data.position ||
                                '';
                            document.getElementById('swal-dept').value = data.department || '';
                        }
                    });
                },
                preConfirm: () => {
                    const userid = document.getElementById('swal-userid').value;
                    const name = document.getElementById('swal-name').value;
                    if (!userid || !name) {
                        Swal.showValidationMessage('จำเป็นต้องระบุรหัสพนักงานและชื่อ');
                        return false;
                    }
                    return {
                        userid: userid,
                        name: name,
                        position: document.getElementById('swal-position').value,
                        department: document.getElementById('swal-dept').value
                    }
                }
            });

            if (formValues) {
                addRowToTable('participant-table', formValues, 'participants');
            }
        }

        function submitForm() {
            event.preventDefault();

            // Validate required fields
            let missingFields = [];
            let isValid = true;

            // 1. Training Name
            if (!document.getElementById('training_name').value) {
                missingFields.push('ชื่อหลักสูตร');
                isValid = false;
            }

            // 2. Source Type
            const sourceType = document.querySelector('input[name="source_type"]:checked');
            if (!sourceType) {
                missingFields.push('ที่มาหลักสูตร');
                isValid = false;
            } else if (sourceType.value === 'in_plan') {
                if (!document.querySelector('input[name="plan_no"]').value) {
                    missingFields.push('เลขที่แผน');
                    isValid = false;
                }
            } else if (sourceType.value === 'substitute') {
                if (!document.querySelector('input[name="substitute_topic"]').value) {
                    missingFields.push('เรื่อง');
                    isValid = false;
                }
                if (!document.querySelector('input[name="substitute_reason"]').value) {
                    missingFields.push('เนื่องจาก');
                    isValid = false;
                }
            } else if (sourceType.value === 'out_plan') {
                if (!document.querySelector('input[name="out_of_plan_reason"]').value) {
                    missingFields.push('เนื่องจาก');
                    isValid = false;
                }
            }

            // 3. Dates and Times
            const dateFields = ['start_date', 'end_date', 'duration_hours'];
            var dateTime = false;
            dateFields.forEach(name => {
                const element = document.querySelector(`input[name="${name}"]`);
                if (!element || !element.value) {
                    dateTime = true;
                }
            });
            if (dateTime) {
                missingFields.push('วันที่-เวลา');
                isValid = false;
            }

            // 4. Participants
            const participants = document.getElementsByName('participants_userid[]');
            if (participants.length === 0) {
                missingFields.push('ผู้เข้าร่วม');
                isValid = false;
            }

            // 5. Files
            if (files.length === 0) {
                missingFields.push('แนบเอกสารประกอบการฝึกอบรม');
                isValid = false;
            }

            if (!isValid) {
                Swal.fire({
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    html: missingFields.join('<br>'),
                    icon: 'error',
                    showConfirmButton: false,
                    buttonsStyling: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                return;
            }

            Swal.fire({
                title: 'ต้องการสร้างเอกสารหรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary mx-3',
                    cancelButton: 'btn btn-ghost mx-3'
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
                    const form = document.getElementById('create-form');
                    if (fileInput) {
                        const dataTransfer = new DataTransfer();
                        files.forEach(file => dataTransfer.items.add(file));
                        fileInput.files = dataTransfer.files;
                    }
                    form.submit();
                }
            });
        }
    </script>
@endpush
