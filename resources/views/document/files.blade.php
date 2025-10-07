    <p><strong>เอกสารแนบ</strong> </p>
    @foreach ($files as $file)
        <div class="border-base-200 flex items-center justify-between border-b py-1">
            <div class="flex flex-col">
                <p class="text-sm font-medium">{{ $file->original_filename }}</p>
                <div class="flex">
                    <p class="text-base-content/70 text-xs"><a class="text-secondary me-6" href="{{ route("document.files.download", $file->id) }}">Download</a> {{ number_format($file->size / 1024, 2) }} KB </p>
                </div>
            </div>
            <div class="flex gap-1">
                @php
                    $mimeType = $file->mime_type;
                    $viewableMimeTypes = ["image/jpeg", "image/png", "image/gif", "application/pdf", "text/plain", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"];
                @endphp
                @if (in_array(strtolower($mimeType), $viewableMimeTypes))
                    <a class="btn btn-xs btn-accent" href="{{ route("document.files.show", $file->id) }}" target="_blank">View</a>
                @endif

            </div>
        </div>
    @endforeach
