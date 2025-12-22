<div class="card-body">
    <button class="text-accent w-24 cursor-pointer" onclick="window.history.back()"> <i class="fas fa-arrow-left"></i> ย้อนกลับ</button>
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
            <p><strong>เรื่อง:</strong>
                @if (is_array($document->title))
                    @foreach ($document->title as $title)
                        {{ $title }} <br>
                    @endforeach
                @else
                    {{ $document->title ?? $document->document_type_name }}
                @endif
            </p>
            <p><strong>วันที่:</strong> {{ $document->created_at->format("d/m/Y") }}</p>
        </div>
        <div>
            <p><strong>ผู้ขอ:</strong> {{ $document->creator->name }}</p>
            <p><strong>แผนก:</strong> {{ $document->creator->department }}</p>
            <p><strong>เบอร์โทร:</strong> {{ $document->document_phone ?? $document->documentUser->document_phone }}</p>
        </div>
    </div>
    @if ($document->files->count() > 0)
        @include("document.files", ["files" => $document->files])
        <div class="divider"></div>
    @endif
    <strong>รายละเอียด</strong>
    <p class="border-secondary min-h-48 rounded-md border p-4">{!! $document->detail ?? $document->documentUser->detail !!}</p>
    @if ($type == "BORROW")
        <strong>วันที่คาดว่าจะคืนอุปกรณ์</strong>
        <input class="input input-accent text-accent w-full" type="text" readonly value="{{ $document->estimate_return_date->format("d M Y") }}">
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
                @if (count($document->hardwares) > 0)
                    @foreach ($document->hardwares as $hardware)
                        <tr>
                            <td>
                                {{ $hardware->serial_number }}
                            </td>
                            <td>{{ $hardware->detail }}</td>
                            <td>{{ $hardware->borrow_date->format("d M Y") }}</td>
                            <td>
                                <div>{{ $hardware->return_date->format("d M Y") }}</div>
                                @if ($hardware->retrieve_date)
                                    <div class="text-accent">{{ $hardware->retrieve_date->format("d M Y") }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($document->status == "pending")
                                    <span class="btn btn-xs btn-error btn-soft" onclick="removeHardware('{{ $hardware->id }}')">ลบ</span>
                                @elseif($document->status == "borrow" && $hardware->return_date == null && $hardware->approver == auth()->user()->userid)
                                    <span class="btn btn-xs btn-secondary btn-soft" onclick="returnHardware('{{ $hardware->id }}')">คืน</span>
                                @elseif($document->status == "return_approve" && $hardware->return_date == null)
                                    <span class="btn btn-xs btn-secondary btn-soft" onclick="retrieveHardware('{{ $hardware->id }}')">รับคืน</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="text-center" colspan="5">ไม่มีรายการอุปกรณ์</td>
                    </tr>
                @endif
            </tbody>
        </table>

    @endif
    @include("document.tasks", ["tasks" => $document->tasks])
</div>
@push("scripts")
    <script>
        function returnHardware(id) {
            Swal.fire({
                title: "ยืนยันการคืนอุปกรณ์?",
                html: "กรุณานำของไปคืน เพื่อบันทึกข้อมูลการคืนอีกครั้ง!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "ยืนยัน",
                cancelButtonText: "ยกเลิก",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-primary me-2",
                    cancelButton: "btn btn-ghost"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post("{{ route("document.it.borrowlist.return") }}", {
                        id: id,
                    }).then((response) => {
                        if (response.data.status === "success") {
                            Swal.fire({
                                icon: "success",
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
                };
            });
        }
    </script>
@endpush
