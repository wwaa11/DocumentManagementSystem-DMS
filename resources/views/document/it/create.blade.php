@extends("layouts.app")
@section("content")
    <div class="mx-auto max-w-5xl">
        <div class="mb-8">
            <h2 class="text-primary mb-2 text-3xl font-bold">
                เอกสาร แจ้งงาน/สนับสนุนการทำงาน IT
            </h2>
            <div class="divider"></div>
        </div>
        <form action="{{ route("document.it.create") }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include("document.approver")
            <div class="card bg-base-100 mb-6 mt-6 p-6 shadow-lg">
                <!-- Document Type Selection -->
                <h3 class="mb-4 flex items-center text-xl font-semibold">
                    <i class="fas fa-file-alt text-primary mr-2"></i>ประเภทเอกสาร
                </h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label class="card bg-base-100 hover:bg-primary/5 cursor-pointer border transition-all hover:shadow-md" for="type-user">
                        <div class="card-body p-4">
                            <div class="flex items-center">
                                <input class="radio radio-primary mr-3" id="type-user" type="radio" name="doc_type" value="user" onchange="selectDocType('user')" />
                                <div>
                                    <h4 class="font-medium">ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม</h4>
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="card bg-base-100 hover:bg-primary/5 cursor-pointer border transition-all hover:shadow-md" for="type-support">
                        <div class="card-body p-4">
                            <div class="flex items-center">
                                <input class="radio radio-primary mr-3" id="type-support" type="radio" name="doc_type" value="support" onchange="selectDocType('support')" />
                                <div>
                                    <h4 class="font-medium">ขอแจ้งงาน/สนับสนุนการทำงาน</h4>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                @include("document.it.create-user")

                @include("document.it.create-support")

                {{-- Common Detail --}}
                <h3 class="mb-4 mt-6 flex items-center text-xl font-semibold">
                    <i class="fas fa-paperclip text-primary mr-2"></i>ข้อมูลเพิ่มเติม
                </h3>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">เอกสารแนบ (ถ้ามี)</span>
                    </label>
                    <input class="file-input file-input-bordered w-full" id="file_input" type="file" name="files[]" multiple>
                    <div class="mt-2 flex-row" id="file_display">
                        {{-- display file in this div with remove file button --}}
                    </div>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">ส่งถึงแผนก IT</span>
                    </label>
                    <select class="select select-bordered w-full" name="it_admin">
                        <option selected disabled>โปรดระบุ</option>
                        @foreach ($it_admins as $it_admin)
                            <option value="{{ $it_admin->userid }}">{{ $it_admin->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">เบอร์โทรศัพท์ภายในติดต่อกลับ</span>
                    </label>
                    <input class="input input-bordered w-full" id="request_phone" type="text" placeholder="เบอร์โทรศัพท์ภายในติดต่อกลับ" />
                </div>

                <div class="mt-6 flex justify-end">
                    <button class="btn btn-primary gap-2" type="submit">
                        <i class="fas fa-paper-plane"></i> สร้างเอกสาร
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
@push("scripts")
    <script type="module">
        $(function() {
            const fileInput = document.getElementById('file_input');
            const fileDisplay = document.getElementById('file_display');

            let fileStore = new DataTransfer();

            fileInput.addEventListener('change', (event) => {
                Array.from(event.target.files).forEach(file => {
                    fileStore.items.add(file);
                });
                event.target.value = null;
                fileInput.files = fileStore.files;
                renderFileList();
            });

            function renderFileList() {
                fileDisplay.innerHTML = '';
                Array.from(fileStore.files).forEach((file, index) => {
                    const fileChip = document.createElement('div');
                    fileChip.classList.add(
                        'flex', 'p-2', 'badge', 'badge-outline', 'badge-lg', 'mb-2', 'flex-nowrap'
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
        function selectDocType(type) {
            if (type === 'user') {
                $('#type-user').prop('checked', true);
                $('#user-section').removeClass('hidden');
                $('#support-section').addClass('hidden');
            } else if (type === 'support') {
                $('#type-support').prop('checked', true);
                $('#user-section').addClass('hidden');
                $('#support-section').removeClass('hidden');
            }
        }
    </script>
@endpush
