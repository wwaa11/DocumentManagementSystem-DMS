<div class="card-body">
    <h5 class="card-title">Files</h5>
    @foreach ($files as $file)
        <div class="border-base-200 flex items-center justify-between border-b py-1">
            <div class="flex flex-col">
                <p class="text-sm font-medium">{{ $file->original_filename }}</p>
                <p class="text-base-content/70 text-xs">{{ number_format($file->size / 1024, 2) }} KB</p>
            </div>
            <div class="flex gap-1">
                @php
                    $mimeType = $file->mime_type;
                    $viewableMimeTypes = ["image/jpeg", "image/png", "image/gif", "application/pdf", "text/plain", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"];
                @endphp
                @if (in_array(strtolower($mimeType), $viewableMimeTypes))
                    <a class="btn btn-xs btn-info" href="{{ route("document.files.show", $file->id) }}" target="_blank">View</a>
                @endif
                <a class="btn btn-xs btn-success" href="{{ route("document.files.download", $file->id) }}">Download</a>
            </div>
        </div>
    @endforeach
</div>
