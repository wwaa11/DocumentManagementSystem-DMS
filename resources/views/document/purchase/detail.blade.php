<div class="card-body">
    <x-ui.back-button />
    <x-document.detail-masthead
        :document-number="$document->document_number"
        :document-type-name="$document->document_type_name"
    />
    <div class="divider"></div>
    <x-document.meta-grid
        :title="is_array($document->title) ? $document->title : ($document->title ?? $document->document_type_name)"
        :created-at="$document->created_at"
        :requester-name="$document->creator->name"
        :department="$document->creator->department"
        :phone="$document->document_phone"
    />

    @if ($document->type === 'po_edit')
        <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
                <strong>เลขที่ใบสั่งซื้อ</strong>
                <p class="border-secondary rounded-md border p-3">{{ $document->po_number }}</p>
            </div>
            <div>
                <strong>Detail</strong>
                <p class="border-secondary rounded-md border p-3">{{ $document->po_reason }}</p>
            </div>
        </div>
    @endif

    @if ($document->files->count() > 0)
        <x-document.files-list :files="$document->files" />
        <div class="divider"></div>
    @endif

    <strong>รายละเอียดเพิ่มเติม</strong>
    <p class="border-secondary min-h-48 rounded-md border p-4">{!! $document->detail !!}</p>

    <x-document.task-timeline :tasks="$document->tasks" />
</div>
