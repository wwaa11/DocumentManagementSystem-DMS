@extends("layouts.app")
@section("content")
    <div class="mx-auto max-w-5xl">
        <div class="from-primary/10 to-base-100 rounded-lg bg-gradient-to-r p-6">
            <h2 class="text-primary mb-2 text-3xl font-bold tracking-tight">
                <i class="fas fa-file-alt mr-2"></i> เอกสาร แจ้งงาน/สนับสนุนการทำงาน IT
            </h2>
            <div class="divider opacity-50"></div>
            <p class="text-base-content/70">กรอกข้อมูลด้านล่างเพื่อสร้างเอกสารใหม่</p>
        </div>
        <form id="create-form" action="{{ route("document.it.create") }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include("document.component.create_approver")

            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <span class="fas fa-exclamation-triangle mr-2"></span>
                    <span>มีข้อผิดพลาดในการสร้างเอกสาร</span>
                </div>
                @foreach ($errors->all() as $error)
                    <div>- {{ $error }}</div>
                @endforeach
            @endif

            <input id="selfApprove" type="hidden" name="selfApprove" value="true">
            <input id="isHardware" type="hidden" name="isHardware" value="false">
            <input id="documentCode" type="hidden" name="documentCode" value="">

            <div class="card bg-base-100 mb-8 p-6 shadow-xl">
                <!-- Document Type Selection -->
                <h3 class="card-title text-primary mb-4 flex items-center text-xl">
                    <i class="fas fa-file-alt text-primary mr-2"></i>ประเภทเอกสาร
                </h3>
                <div class="flex flex-col gap-3">
                    <label class="bg-base-100 hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="type-user">
                        <div class="flex items-center">
                            <input class="radio radio-primary mr-3" id="type-user" type="radio" name="document_type" value="user" onchange="selectDocType('user')" />
                            <div>
                                <h4 class="font-medium">ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม</h4>
                            </div>
                        </div>
                    </label>

                    <label class="bg-base-100 hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="type-support">
                        <div class="flex items-center">
                            <input class="radio radio-primary mr-3" id="type-support" type="radio" name="document_type" value="support" onchange="selectDocType('support')" />
                            <div>
                                <h4 class="font-medium">ขอแจ้งงาน/สนับสนุนการทำงาน</h4>
                            </div>
                        </div>
                    </label>
                </div>

                @include("document.it.create-user")

                @include("document.it.create-support")

                <div class="hidden" id="document-addtional-info">
                    <div class="divider"></div>

                    <h3 class="card-title text-primary mb-2 flex items-center text-xl">
                        <i class="fas fa-paperclip text-primary mr-2"></i>เอกสารแนบ (ถ้ามี)
                    </h3>
                    <div class="border-base-300 hover:border-primary cursor-pointer rounded-lg border-2 border-dashed p-6 text-center transition-all" id="drop-area">
                        <input class="hidden" id="file_input" type="file" name="document_files[]" multiple>
                        <p class="text-base-content/70"><i class="fas fa-cloud-upload-alt mr-2"></i> ลากและวางไฟล์ที่นี่ หรือ <span class="text-primary font-bold">คลิกเพื่อเลือกไฟล์</span></p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2" id="file_display">
                        {{-- display file in this div with remove file button --}}
                    </div>

                    @push("scripts")
                    @endpush

                    <h3 class="card-title text-primary mb-2 mt-3 flex items-center text-xl">
                        <i class="fas fa-user-shield text-primary mr-2"></i>ส่งถึงแผนก IT
                    </h3>
                    <select class="select select-bordered w-full" name="document_admin">
                        <option selected disabled>โปรดระบุ</option>
                        @foreach ($it_admins as $it_admin)
                            <option value="{{ $it_admin->userid }}">{{ $it_admin->name }}</option>
                        @endforeach
                    </select>

                    <h3 class="card-title text-primary mb-2 mt-6 flex items-center text-xl">
                        <i class="fas fa-phone-alt text-primary mr-2"></i>เบอร์โทรศัพท์ภายในติดต่อกลับ
                    </h3>
                    <input class="input input-bordered w-full" id="document_phone" name="document_phone" type="text" placeholder="เบอร์โทรศัพท์ภายในติดต่อกลับ" />

                    <div class="mt-6 flex justify-center">
                        <button class="btn btn-accent gap-2 transition-all duration-200 hover:scale-105" type="submit" onclick="submitForm()">
                            <i class="fas fa-paper-plane"></i> สร้างเอกสาร
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push("scripts")
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
                    if (!files.some(existingFile => existingFile.name === file.name && existingFile.size === file.size)) {
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
        function selectDocType(document_type) {
            if (document_type === 'user') {
                $('#type-user').prop('checked', true);
                $('#user-section').removeClass('hidden');
                $('#support-section').addClass('hidden');
                $('#support_detail').prop('disabled', true);
                setDataApprove(false, false, 'ITU');
            } else if (document_type === 'support') {
                $('#type-support').prop('checked', true);
                $('#user-section').addClass('hidden');
                $('#support-section').removeClass('hidden');
                $('#support_detail').prop('disabled', false);

                $('input[name="createIT"]').val('true');
                $('input[name="createHC"]').val('false');
                $('input[name="createPAC"]').val('false');
            }
        }

        function setDataApprove(isSelfApprove, isHardward, code) {
            $('#selfApprove').val(isSelfApprove);
            $('#isHardware').val(isHardward);
            $('#documentCode').val(code);
        }

        function submitForm() {
            event.preventDefault();

            const type = $('#type-user').is(':checked') ? 'user' : 'support';
            const title = $('input[name="title"]:checked').val();

            if (type === 'user') {
                doctor_hr_it = $('#doctor_hr_it').is(':checked');
                doctor_hr_hclab = $('#doctor_hr_hclab').is(':checked');
                doctor_hr_pacs = $('#doctor_hr_pacs').is(':checked');

                console.log('doctor_hr_it: ' + doctor_hr_it);
                console.log('doctor_hr_hclab: ' + doctor_hr_hclab);
                console.log('doctor_hr_pacs: ' + doctor_hr_pacs);
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
                    // Ensure fileInput.files is updated before submission
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
