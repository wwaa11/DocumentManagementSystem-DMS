@props([
    'name' => 'document_files[]',
    'dropId' => 'drop-area',
    'inputId' => 'file_input',
    'displayId' => 'file_display',
    'hint' => null,
    'withScript' => true,
])

@if ($hint)
    <div class="text-accent mb-2 text-xs">{{ $hint }}</div>
@endif

<div
    {{ $attributes->merge(['class' => 'border-base-300 hover:border-primary cursor-pointer rounded-lg border-2 border-dashed p-6 text-center transition-all']) }}
    id="{{ $dropId }}"
>
    <input class="hidden" id="{{ $inputId }}" type="file" name="{{ $name }}" multiple>
    <p class="text-base-content/70">
        <i class="fas fa-cloud-upload-alt mr-2"></i>
        ลากและวางไฟล์ที่นี่ หรือ <span class="text-primary font-bold">คลิกเพื่อเลือกไฟล์</span>
    </p>
</div>
<div class="mt-4 flex flex-wrap gap-2" id="{{ $displayId }}"></div>

@if ($withScript)
    @once
        @push('scripts')
            <script>
                window.initFileDropzone = function(dropId, inputId, displayId) {
                    const dropArea = document.getElementById(dropId);
                    const fileInput = document.getElementById(inputId);
                    const fileDisplay = document.getElementById(displayId);
                    if (!dropArea || !fileInput || !fileDisplay) {
                        return;
                    }

                    let files = [];

                    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
                        dropArea.addEventListener(eventName, preventDefaults, false);
                        document.body.addEventListener(eventName, preventDefaults, false);
                    });

                    ['dragenter', 'dragover'].forEach((eventName) => {
                        dropArea.addEventListener(eventName, highlight, false);
                    });

                    ['dragleave', 'drop'].forEach((eventName) => {
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
                        dropArea.classList.add('border-primary');
                        dropArea.classList.remove('border-base-300');
                    }

                    function unhighlight() {
                        dropArea.classList.remove('border-primary');
                        dropArea.classList.add('border-base-300');
                    }

                    function handleDrop(e) {
                        handleFiles(e.dataTransfer.files);
                    }

                    function handleFiles(newFiles) {
                        Array.from(newFiles).forEach((file) => {
                            if (!files.some((existing) => existing.name === file.name && existing.size === file.size)) {
                                files.push(file);
                            }
                        });
                        updateFileDisplay();
                    }

                    function updateFileDisplay() {
                        fileDisplay.innerHTML = '';
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

                        const dataTransfer = new DataTransfer();
                        files.forEach((file) => dataTransfer.items.add(file));
                        fileInput.files = dataTransfer.files;
                    }

                    fileDisplay.addEventListener('click', function(e) {
                        const btn = e.target.closest('.remove-file-btn');
                        if (!btn) {
                            return;
                        }
                        files.splice(parseInt(btn.dataset.index, 10), 1);
                        updateFileDisplay();
                    });
                };

                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('[data-file-dropzone]').forEach((el) => {
                        window.initFileDropzone(el.dataset.dropId, el.dataset.inputId, el.dataset.displayId);
                    });
                });
            </script>
        @endpush
    @endonce

    <div
        class="hidden"
        data-file-dropzone
        data-drop-id="{{ $dropId }}"
        data-input-id="{{ $inputId }}"
        data-display-id="{{ $displayId }}"
    ></div>
@endif
