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
        :phone="$document->document_phone ?? $document->documentUser->document_phone ?? null"
    />
    @if ($document->files->count() > 0)
        <x-document.files-list :files="$document->files" />
        <div class="divider"></div>
    @endif
    <strong>รายละเอียด</strong>
    <p class="border-secondary min-h-48 rounded-md border p-4">{!! $document->detail ?? $document->documentUser->detail !!}</p>
    @if ($type == 'BORROW')
        <strong>วันที่ขอยืมอุปกรณ์</strong>
        <input class="input input-accent text-accent w-full" type="text" readonly value="{{ $document->borrow_date?->format('d M Y') ?? '-' }}">
        <strong>วันที่คาดว่าจะคืนอุปกรณ์</strong>
        <input class="input input-accent text-accent w-full" type="text" readonly value="{{ $document->estimate_return_date->format('d M Y') }}">
        <strong>รายการอุปกรณ์</strong>
        <table class="table">
            <thead>
                <th>Serial Number</th>
                <th>รายละเอียด</th>
                <th>วันที่ยืม</th>
                <th>วันที่คืน</th>
                <th class="text-end">#</th>
            </thead>
            <tbody>
                @forelse ($document->hardwares as $hardware)
                    <tr>
                        <td>{{ $hardware->serial_number }}</td>
                        <td>{{ $hardware->detail }}</td>
                        <td>{{ $hardware->borrow_date->format('d M Y') }}</td>
                        <td>
                            @if ($hardware->return_date)
                                <div>{{ $hardware->return_date->format('d M Y') }}</div>
                            @endif
                            @if ($hardware->retrieve_date)
                                <div class="text-primary">{{ $hardware->retrieve_date->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($document->status == 'pending')
                                <span class="btn btn-xs btn-error btn-soft" onclick="removeHardware('{{ $hardware->id }}')">ลบ</span>
                            @elseif ($document->status == 'borrow' && $hardware->return_date == null && $hardware->approver == auth()->user()->userid)
                                <span class="btn btn-xs btn-secondary btn-soft" onclick="returnHardware('{{ $hardware->id }}')">คืน</span>
                            @elseif ($document->status == 'return_approve' && $hardware->return_date != null && in_array(auth()->user()->role, ['admin', 'it']))
                                <span class="btn btn-xs btn-secondary btn-soft" onclick="retrieveHardware('{{ $hardware->id }}')">รับคืน</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center" colspan="5">ไม่มีรายการอุปกรณ์</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
    <x-document.task-timeline :tasks="$document->tasks" />
</div>
@push('scripts')
    <script>
        function returnHardware(id) {
            Swal.fire({
                title: 'ยืนยันการคืนอุปกรณ์?',
                html: 'กรุณานำของไปคืน เพื่อบันทึกข้อมูลการคืนอีกครั้ง!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary me-2',
                    cancelButton: 'btn btn-ghost'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('{{ route('document.it.borrowlist.return') }}', {
                        id: id,
                    }).then((response) => {
                        if (response.data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                text: response.data.message,
                                timer: 1000,
                                timerProgressBar: true,
                                allowOutsideClick: false,
                                showConfirmButton: false,
                            }).then(() => {
                                window.location.reload()
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush
