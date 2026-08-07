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
    @if( (str_contains($document->detail, "<br>") || str_contains($document->detail, "\n")) )
    <p class="border-secondary rounded-md border p-4 whitespace-pre-wrap min-h-[200px]" >{!! $document->detail !!}</p>
    @else
    <p class="border-secondary rounded-md border p-4 min-h-[200px]">{{ $document->detail }}</p>
    @endif
    @if ($type == 'BORROW')
        <strong>วันที่ขอยืมอุปกรณ์</strong>
        <input class="input input-accent text-accent w-full" type="text" readonly value="{{ $document->borrow_date?->format('d M Y') ?? '-' }}">
        <strong>วันที่คาดว่าจะคืนอุปกรณ์</strong>
        <input class="input input-accent text-accent w-full" type="text" readonly value="{{ $document->estimate_return_date->format('d M Y') }}">

        <div class="border-base-200 bg-base-200/30 mt-4 overflow-hidden rounded-xl border">
            <div class="border-base-200 flex flex-wrap items-center justify-between gap-2 border-b px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="bg-primary/10 text-primary flex h-8 w-8 items-center justify-center rounded-lg">
                        <i class="fas fa-laptop"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold">รายการอุปกรณ์ที่ยืม</p>
                        <p class="text-base-content/55 text-xs">{{ $document->hardwares->count() }} รายการ</p>
                    </div>
                </div>
                @if ($document->status == 'pending')
                    <span class="badge badge-warning badge-soft">สามารถลบรายการได้</span>
                @elseif ($document->status == 'borrow')
                    <span class="badge badge-info badge-soft">กดคืนเมื่อส่งมอบกลับ</span>
                @elseif ($document->status == 'return_approve')
                    <span class="badge badge-primary badge-soft">กดรับคืนเมื่อตรวจสอบแล้ว</span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-base-100 text-base-content/60 text-xs tracking-wide uppercase">
                        <tr>
                            <th>Serial Number</th>
                            <th>รายละเอียด</th>
                            <th class="min-w-36 text-center">วันที่ยืม</th>
                            <th class="min-w-48 text-center">วันที่คืน / รับคืน</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($document->hardwares as $hardware)
                            <tr class="hover:bg-base-100/80">
                                <td class="font-semibold">{{ $hardware->serial_number }}</td>
                                <td>{{ $hardware->detail ?: '—' }}</td>
                                <td class="text-center text-xs">{{ $hardware->borrow_date->format('d M Y') }}</td>
                                <td class="text-center text-xs">
                                    <div class="flex flex-col items-center gap-1">
                                        @if ($hardware->return_date)
                                            <span class="badge badge-warning badge-sm gap-1 text-xs">
                                                <i class="fas fa-undo"></i> {{ $hardware->return_date->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-base-content/40 text-xs">ยังไม่คืน</span>
                                        @endif
                                        @if ($hardware->retrieve_date)
                                            <span class="badge badge-primary badge badge-sm gap-1 text-xs">
                                                <i class="fas fa-check"></i> {{ $hardware->retrieve_date->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-base-content/40 text-xs">ยังไม่รับคืน</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center no-print">
                                    @if ($document->status == 'pending')
                                        <button
                                            class="btn btn-error btn-sm gap-2 shadow-sm w-24"
                                            type="button"
                                            onclick="removeHardware('{{ $hardware->id }}')"
                                            title="ลบอุปกรณ์นี้ออกจากรายการ"
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                            ลบ
                                        </button>
                                    @elseif ($document->status == 'borrow' && $hardware->return_date == null && $hardware->approver == auth()->user()->userid)
                                        <button
                                            class="btn btn-warning btn-sm gap-2 shadow-sm w-24"
                                            type="button"
                                            onclick="returnHardware('{{ $hardware->id }}')"
                                            title="บันทึกการคืนอุปกรณ์"
                                        >
                                            <i class="fas fa-undo"></i>
                                            คืนอุปกรณ์
                                        </button>
                                    @elseif ($document->status == 'return_approve' && $hardware->return_date != null && $hardware->retrieve_date == null)
                                        <button
                                            class="btn btn-primary btn-sm gap-2 shadow-sm w-24"
                                            type="button"
                                            onclick="retrieveHardware('{{ $hardware->id }}')"
                                            title="ยืนยันรับอุปกรณ์คืน"
                                        >
                                            <i class="fas fa-box-open"></i>
                                            รับคืน
                                        </button>
                                    @elseif ($hardware->retrieve_date)
                                        <span class="badge badge-success badge-soft gap-1 w-24">
                                            รับคืนแล้ว
                                        </span>
                                    @else
                                        <span class="text-base-content/35 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-base-content/50 py-10 text-center" colspan="5">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="fas fa-inbox text-2xl opacity-40"></i>
                                        <span>ไม่มีรายการอุปกรณ์</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    <x-document.task-timeline :tasks="$document->tasks" />
</div>
@push('scripts')
    <script>
        function removeHardware(id) {
            Swal.fire({
                title: 'ยืนยันการลบอุปกรณ์?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-error me-2',
                    cancelButton: 'btn btn-ghost'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('{{ route('admin.it.borrowlist.remove') }}', {
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

        function returnHardware(id) {
            Swal.fire({
                title: 'ยืนยันการคืนอุปกรณ์?',
                html: 'กรุณานำอุปกรณ์นี้ไปคืน เพื่อบันทึกข้อมูลการคืนอีกครั้ง!',
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

        function retrieveHardware(id) {
            Swal.fire({
                title: 'ยืนยันการรับอุปกรณ์?',
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
                    axios.post('{{ route('admin.it.borrowlist.retrieve') }}', {
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
