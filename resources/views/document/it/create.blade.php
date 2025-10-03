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
    <script type="module">
        $(function() {
            const fileInput = document.getElementById('file_input'); // Assign here
            const fileDisplay = document.getElementById('file_display');
            const dropArea = document.getElementById('drop-area');

            console.log(fileInput);

            fileStore = new DataTransfer(); // Assign here

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            // Highlight drop area when item is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => dropArea.classList.add('border-primary'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => dropArea.classList.remove('border-primary'), false);
            });

            // Handle dropped files
            dropArea.addEventListener('drop', handleDrop, false);

            fileInput.addEventListener('change', (event) => {
                addFiles(event.target.files);
                event.target.value = null; // Clear the input after adding files
            });

            dropArea.addEventListener('click', () => fileInput.click());

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                addFiles(files);
            }

            function addFiles(files) {
                Array.from(files).forEach(file => {
                    fileStore.items.add(file);
                });
                fileInput.files = fileStore.files;
                renderFileList();
            }

            function renderFileList() {
                fileDisplay.innerHTML = '';
                Array.from(fileStore.files).forEach((file, index) => {
                    const fileChip = document.createElement('div');
                    fileChip.classList.add(
                        'flex', 'items-center', 'gap-2', 'badge', 'badge-outline', 'badge-lg', 'mb-2', 'flex-nowrap'
                    );

                    const fileNameSpan = document.createElement('span');
                    fileNameSpan.textContent = file.name;

                    const removeButton = document.createElement('button');
                    removeButton.classList.add('btn', 'btn-xs', 'btn-circle', 'btn-error');
                    removeButton.innerHTML = '<i class="fas fa-times text-white"></i>';
                    removeButton.type = 'button';

                    removeButton.setAttribute('data-filename', file.name);

                    removeButton.addEventListener('click', (e) => {
                        removeFileByName(e.currentTarget.getAttribute('data-filename'));
                    });

                    fileChip.appendChild(fileNameSpan);
                    fileChip.appendChild(removeButton);
                    fileDisplay.appendChild(fileChip);
                });
                fileInput.files = fileStore.files;
            }

            function removeFileByName(fileNameToRemove) {
                let newFileStore = new DataTransfer();
                Array.from(fileStore.files).forEach((file) => {
                    if (file.name !== fileNameToRemove) {
                        newFileStore.items.add(file);
                    }
                });
                fileStore = newFileStore;
                renderFileList();
            }
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
            console.log('title: ' + title);
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
                    confirmButton: 'btn btn-primary mx-3', // DaisyUI Primary Color
                    cancelButton: 'btn btn-ghost mx-3' // DaisyUI Ghost/subtle style
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
                    if (fileInput && fileStore) {
                        fileInput.files = fileStore.files;
                    }
                    form.submit();
                }
            });
        }
    </script>
@endpush
