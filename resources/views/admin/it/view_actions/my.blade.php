<form id="process-form" action="{{ route("admin.it.process") }}" method="post" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ $document->id }}">
    <fieldset class="fieldset">
        <legend class="fieldset-legend">รายละเอียดการดำเนินงาน</legend>
        <textarea class="textarea textarea-primary w-full" id="detail" name="detail" rows="8" placeholder="รายละเอียดการทำงาน...."></textarea>
        <p class="label">แนบไฟล์ (ถ้ามี)</p>
        <div class="border-base-300 hover:border-primary cursor-pointer rounded-lg border-2 border-dashed p-6 text-center transition-all" id="drop-area">
            <input class="hidden" id="file_input" type="file" name="document_files[]" multiple>
            <p class="text-base-content/70"><i class="fas fa-cloud-upload-alt mr-2"></i> ลากและวางไฟล์ที่นี่ หรือ <span class="text-primary font-bold">คลิกเพื่อเลือกไฟล์</span></p>
        </div>
        <div class="mt-4 flex-col gap-2" id="file_display">
            {{-- display file in this div with remove file button --}}
        </div>
        <p class="label">ส่งต่องาน <span class="text-error">*กรณีมีการระบุ ใบงานจะถูกส่งต่อไปยังผู้ใช้งานนี้</span></p>
        <select class="select select-primary mb-2 w-full" name="transfer_userid">
            <option value="" selected>ใบงานนี้ดำเนินการเรียบร้อย</option>
            @foreach ($userList as $user)
                <option value="{{ $user->userid }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </fieldset>
    <button class="btn btn-soft btn-success w-full" type="button" onclick="submitForm()">ดำเนินการเสร็จสิ้น</button>
</form>
<button class="btn btn-ghost" onclick="cancelJob()" type="button">ยกเลิกการรับงาน</button>
<div class="divider"></div>
<button class="btn btn-dash btn-error" onclick="cancelDocument()" type="button">ยกเลิกการเอกสารนี้</button>
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
                    fileElement.className = 'flex items-center gap-2 bg-base-200 p-2 rounded-md mb-2';
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
        function submitForm() {
            const detail = $('#detail').val();
            const sendTo = $('select[name="transfer_userid"]').val();

            if (!detail && !sendTo) {
                Swal.fire({
                    title: "ไม่สามารถดำเนินการได้!",
                    text: "กรุณากรอกรายละเอียด หรือ ผู้รับงาน",
                    icon: "error",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                return;
            }

            var sendDetail = '';
            if (detail && !sendTo) {
                sendDetail = 'ยินยันการดำเนินการ และปิดใบงานนี้';
            } else if (detail && sendTo) {
                sendDetail = 'ยินยันการดำเนินการ และส่งงานไปยังผู้รับที่ระบุ';
            } else {
                sendDetail = 'ไม่มีการดำเนินการ ส่งงานไปยังผู้รับที่ระบุ';
            }

            Swal.fire({
                title: "ยืนยันการดำเนินการ?",
                text: sendDetail,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "ยืนยัน",
                cancelButtonText: "ไม่ยืนยัน",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-warning me-2",
                    cancelButton: "btn btn-ghost"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#process-form").submit();
                }
            });
        }

        async function cancelJob() {
            const swal = await Swal.fire({
                title: "ยืนยันการยกเลิกงานการรับงานนี้?",
                text: "งานนี้จะถูกส่งไปยังใบงานใหม่ เพื่อให้ผู้ใช้งานอื่นสามารถดำเนินการได้",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "ยกเลิกรับงานนี้",
                cancelButtonText: "ไม่ยกเลิก",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-ghost me-2",
                    cancelButton: "btn btn-warning"
                }
            });
            if (swal.isConfirmed) {
                axios.post("{{ route("admin.it.canceljob") }}", {
                    id: {{ $document->id }},
                }).then(function(response) {
                    if (response.data.status == "success") {
                        Swal.fire({
                            title: "ยกเลิกการรับงานสำเร็จ!",
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true,
                        }).then(function() {
                            window.location.href = "{{ route("admin.it.mylist") }}";
                        });
                    } else {
                        Swal.fire({
                            title: response.data.message,
                            icon: "error",
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true,
                        });
                    }
                });
            }
        }

        async function cancelDocument() {
            const swal = await Swal.fire({
                title: "ยืนยันการยกเลิกเอกสารนี้?",
                text: "เอกสารนี้จะถูกยกเลิกและไม่สามารถแก้ไขได้",
                icon: "warning",
                input: "textarea",
                inputPlaceholder: "กรุณาใส่เหตุผลการยกเลิก",
                showCancelButton: true,
                confirmButtonText: "ยกเลิกเอกสารนี้",
                cancelButtonText: "ไม่ยกเลิก",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-ghost me-2",
                    cancelButton: "btn btn-error"
                }
            });
            if (swal.isConfirmed && !swal.value) {
                Swal.fire({
                    title: "กรุณาใส่เหตุผลการยกเลิก",
                    icon: "warning",
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: true,
                });
            }
            if (swal.isConfirmed && swal.value) {
                axios.post("{{ route("admin.it.cancel") }}", {
                    id: {{ $document->id }},
                    reason: swal.value,
                }).then(function(response) {
                    if (response.data.status == "success") {
                        Swal.fire({
                            title: "ยกเลิกเอกสารสำเร็จ!",
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true,
                        }).then(function() {
                            window.location.href = "{{ route("admin.it.mylist") }}";
                        });
                    }
                });
            }

        }
    </script>
@endpush
