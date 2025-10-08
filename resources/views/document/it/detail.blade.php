<div class="card bg-base-100 mb-4 shadow-xl">
    <div class="card-body">
        <div class="flex items-center">
            <img class="mr-4 h-auto w-36" src="{{ asset("images/Side Logo.png") }}" alt="Side Logo">
            <div class="flex-1 text-end">
                <h2 class="text-2xl font-bold">QF-ITD-09/Rev.3 (15-06-66)</h2>
                <p class="text-sm text-gray-500">เลขที่เอกสาร: {{ $document->document_number }}</p>
                <p class="text-sm text-gray-500">ประเภทเอกสาร: {{ $document->document_type_name }}</p>
            </div>
        </div>
        <div class="divider"></div>
        <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <p><strong>เรื่อง:</strong> {{ $document->title }}</p>
                <p><strong>วันที่:</strong> {{ $document->created_at->format("d-m-Y") }}</p>
            </div>
            <div>
                <p><strong>ผู้ขอ:</strong> {{ $document->creator->name }}</p>
                <p><strong>แผนก:</strong> {{ $document->creator->department }}</p>
                <p><strong>เบอร์โทร:</strong> {{ $document->document_phone }}</p>
            </div>
        </div>
        @if ($document->files->count() > 0)
            @include("document.files", ["files" => $document->files])
            <div class="divider"></div>
        @endif
        <strong>รายละเอียด</strong>
        <p class="border-secondary min-h-48 rounded-md border p-4">{!! $document->detail !!}</p>
        @include("document.tasks", ["tasks" => $document->tasks])
    </div>
</div>
