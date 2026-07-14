<div class="card-body">
    <x-ui.back-button />
    <x-document.detail-masthead
        :document-number="$document->document_number"
        :document-type-name="$document->document_type_name"
    />
    <div class="divider"></div>
    <x-document.meta-grid
        :title="$document->title"
        :created-at="$document->created_at"
        :requester-name="$document->creator->name"
        :department="$document->creator->department"
        :phone="$document->document_phone"
    />

    <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
        <div>
            <strong>วันที่ต้องการ</strong>
            <p class="border-secondary rounded-md border p-3">{{ $document->required_date?->format('d/m/Y') }}</p>
        </div>
        <div>
            <strong>ประเภทสื่อ</strong>
            <p class="border-secondary rounded-md border p-3">{{ $document->document_type_name }}</p>
        </div>
    </div>

    @if ($document->type === 'sign')
        <strong>สถานที่ติดตั้งป้าย</strong>
        <p class="border-secondary mb-4 rounded-md border p-3">{{ $document->sign_location }}</p>
        <strong>รายการป้าย</strong>
        <div class="mb-4 flex flex-col gap-3">
            @forelse ($document->signItems as $item)
                <div class="border-secondary rounded-md border p-3">
                    <div class="flex flex-wrap items-start gap-4">
                        @if ($item->reference_image_url)
                            <img
                                class="border-base-300 max-h-64 w-48 rounded-md border object-contain"
                                src="{{ $item->reference_image_url }}"
                                alt="{{ $item->sign_type_label }}"
                            />
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="font-medium">{{ $item->sign_type_label }}</div>
                            @if ($item->detail)
                                <p class="mt-1 text-sm">{!! nl2br(e($item->detail)) !!}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-base-content/50">ไม่มีรายการป้าย</p>
            @endforelse
        </div>
    @endif

    @if ($document->type === 'brochure')
        <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
                <strong>ขนาด</strong>
                <p class="border-secondary rounded-md border p-3">{{ implode(', ', $document->brochure_sizes ?? []) }}</p>
            </div>
            <div>
                <strong>ประเภทการพิมพ์</strong>
                <p class="border-secondary rounded-md border p-3">{{ $document->brochure_print_type }}</p>
            </div>
        </div>
    @endif

    @if ($document->type === 'photo_video')
        <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
                <strong>ลักษณะงาน</strong>
                <p class="border-secondary rounded-md border p-3">{{ implode(', ', $document->photo_work_types ?? []) }}</p>
            </div>
            <div>
                <strong>วันที่ถ่ายทำ</strong>
                <p class="border-secondary rounded-md border p-3">{{ $document->photo_date?->format('d/m/Y') }}</p>
            </div>
            <div>
                <strong>เวลาถ่ายทำ</strong>
                <p class="border-secondary rounded-md border p-3">{{ $document->photo_time }}</p>
            </div>
            <div>
                <strong>สถานที่ถ่ายทำ</strong>
                <p class="border-secondary rounded-md border p-3">{{ $document->photo_location }}</p>
            </div>
        </div>
    @endif

    @if ($document->files->count() > 0)
        <x-document.files-list :files="$document->files" />
        <div class="divider"></div>
    @endif

    <strong>รายละเอียด</strong>
    <p class="border-secondary min-h-48 rounded-md border p-4">{!! $document->detail !!}</p>

    <x-document.task-timeline :tasks="$document->tasks" />
</div>
