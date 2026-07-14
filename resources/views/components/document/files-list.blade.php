@props(['files'])

@if (count($files) > 0)
    <p><strong>เอกสารแนบ</strong></p>
    <div class="flex flex-col gap-2">
        @foreach ($files as $file)
            <div class="border-base-200 flex flex-wrap items-center justify-between gap-3 border-b py-2">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">{{ $file->original_filename }}</p>
                    <p class="text-base-content/70 text-xs">{{ number_format($file->size / 1024, 2) }} KB</p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    @if ($file->isImage())
                        <button
                            class="btn btn-xs btn-accent"
                            type="button"
                            onclick="previewAttachment({{ \Illuminate\Support\Js::from([
                                'url' => route('document.files.show', $file->id),
                                'name' => $file->original_filename,
                                'type' => 'image',
                            ]) }})"
                        >
                            <i class="fas fa-eye me-1"></i> View
                        </button>
                        <a class="btn btn-xs btn-ghost" href="{{ route('document.files.download', $file->id) }}">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    @elseif ($file->isPdf())
                        <button
                            class="btn btn-xs btn-accent"
                            type="button"
                            onclick="previewAttachment({{ \Illuminate\Support\Js::from([
                                'url' => route('document.files.show', $file->id),
                                'name' => $file->original_filename,
                                'type' => 'pdf',
                            ]) }})"
                        >
                            <i class="fas fa-eye me-1"></i> View
                        </button>
                        <a class="btn btn-xs btn-ghost" href="{{ route('document.files.download', $file->id) }}">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    @else
                        <a class="btn btn-xs btn-secondary" href="{{ route('document.files.download', $file->id) }}">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @once
        @push('scripts')
            <script>
                function previewAttachment(file) {
                    const isImage = file.type === 'image';
                    const content = isImage
                        ? `<img src="${file.url}" alt="${file.name}" class="mx-auto max-h-[70vh] max-w-full rounded-md object-contain" />`
                        : `<iframe src="${file.url}" title="${file.name}" class="h-[70vh] w-full rounded-md border-0"></iframe>`;

                    Swal.fire({
                        title: file.name,
                        html: content,
                        width: isImage ? 'auto' : '80%',
                        showCloseButton: true,
                        showConfirmButton: false,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'max-w-[90vw]',
                            htmlContainer: 'overflow-auto m-0 p-2',
                        },
                    });
                }
            </script>
        @endpush
    @endonce
@endif
