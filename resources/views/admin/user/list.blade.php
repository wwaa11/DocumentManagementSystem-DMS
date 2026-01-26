@extends("layouts.app")
@section("content")
    <div class="mx-8">
        <h1 class="text-primary text-2xl font-bold">รายการเอกสาร </h1>
        <span class="countdown font-mono text-sm">Refesh in <span class="bg-base-300 mx-2 rounded-md px-2" id="countdown" style="--value:30;"></span> seconds</span>
        <div class="divider"></div>
        @if ($action == "approve")
            <div class="mb-3 text-end">
                <button class="btn btn-primary" type="button" onclick="approveAllDocuments()">อนุมัติเอกสารทั้งหมด</button>
            </div>
        @elseif($action == "all")
            <div class="border-base-content/5 bg-base-100 mb-4 overflow-hidden rounded-lg border">
                <div class="border-base-content/5 bg-base-200/30 border-b px-4 py-3">
                    <form class="grid grid-cols-1 items-end gap-4 md:grid-cols-5" action="{{ route("admin.user.alllist", ["type" => $type]) }}" method="GET">
                        <div class="form-control col-span-1 md:col-span-2">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">ค้นหา</span></label>
                            <input class="input input-bordered input-sm w-full" type="text" name="search" value="{{ $search ?? "" }}" placeholder="เลขที่, ชื่อเอกสาร, รายละเอียด...">
                        </div>
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">สถานะ</span></label>
                            <select class="select select-bordered select-sm w-full" name="status">
                                <option value="">ทั้งหมด</option>
                                <option value="wait_approval" {{ isset($status) && $status == "wait_approval" ? "selected" : "" }}>รออนุมัติจากหน่วยงาน</option>
                                <option value="pending" {{ isset($status) && $status == "pending" ? "selected" : "" }}>รอการดำเนินการ</option>
                                <option value="process" {{ isset($status) && $status == "process" ? "selected" : "" }}>กำลังดำเนินการ</option>
                                <option value="done" {{ isset($status) && $status == "done" ? "selected" : "" }}>เอกสารรออนุมัติ</option>
                                <option value="complete" {{ isset($status) && $status == "complete" ? "selected" : "" }}>เสร็จสมบูรณ์</option>
                                <option value="reject" {{ isset($status) && $status == "reject" ? "selected" : "" }}>ยกเลิกเอกสาร</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">วันที่เริ่ม</span></label>
                            <input class="input input-bordered input-sm w-full" type="date" name="start_date" value="{{ $start_date ?? "" }}">
                        </div>
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">วันที่สิ้นสุด</span></label>
                            <input class="input input-bordered input-sm w-full" type="date" name="end_date" value="{{ $end_date ?? "" }}">
                        </div>
                        <div class="col-span-1 flex justify-end gap-2 md:col-span-5">
                            <button class="btn btn-primary btn-sm px-8" type="submit">
                                <i class="fas fa-search mr-1"></i> ค้นหา
                            </button>
                            <a class="btn btn-ghost btn-sm border-base-content/20 px-8" href="{{ route("admin.user.alllist", ["type" => $type]) }}">
                                <i class="fas fa-redo mr-1"></i> ล้างค่า
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <div>
                {{ $documents->links() }}
            </div>
        @endif
        <div class="border-base-content/5 bg-base-100 overflow-x-auto rounded-lg border">
            <table class="table">
                <thead>
                    <tr class="text-center">
                        <th>เลขที่</th>
                        <th>ชื่อเอกสาร</th>
                        <th>รายละเอียด</th>
                        <th>ผู้ขอ/วันที่ขอ</th>
                        <th>ผู้อนุมัติ</th>
                        <th>สถานะ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                        <tr class="hover:bg-base-300">
                            <td class="text-center">{{ $document->document_number }}</td>
                            <td class="text-xs">
                                {{ $document->documentUser->document_type_name }}
                            </td>
                            <td class="text-xs">
                                <div class="preview-trigger cursor-pointer hover:text-blue-600" data-fulltext="{{ $document->documentUser->detail }}" onclick="showDetail(this)">
                                    {{ Str::limit($document->documentUser->ListDetail, 50) }}
                                    <i class="fas fa-search-plus ml-1 text-gray-400"></i>
                                </div>
                            </td>
                            <td>
                                {{ $document->documentUser->creator->name }}<br>
                                {{ $document->created_at->format("d/m/Y H:i:s") }}
                            </td>
                            <td class="text-xs">
                                @foreach ($document->documentUser->approvers as $approver)
                                    @if ($approver->status == "approve")
                                        <i class="fas fa-check text-primary"></i>
                                    @elseif($approver->status == "reject" || $approver->status == "cancel")
                                        <i class="fas fa-times text-error"></i>
                                    @else
                                        <i class="fas fa-hourglass-half text-ghost"></i>
                                    @endif
                                    {{ $approver->user->name ?? $approver->userid }}
                                @endforeach
                            </td>
                            <td class="text-center">
                                @php
                                    switch ($document->status) {
                                        case "wait_approval":
                                            $text = "รออนุมัติจากหน่วยงาน";
                                            $class = "badge-soft badge-warning";
                                            break;
                                        case "not_approval":
                                            $text = "หน่วยงานไม่อนุมัติ";
                                            $class = "badge-soft badge-error";
                                            break;
                                        case "cancel":
                                            $text = "ผู้ขอยกเลิกเอกสาร";
                                            $class = "badge-soft badge-error";
                                            break;
                                        case "pending":
                                            $text = "รอการดำเนินการ";
                                            $class = "badge-soft badge-warning";
                                            break;
                                        case "reject":
                                            $text = "ยกเลิกเอกสาร";
                                            $class = "badge-soft badge-error";
                                            break;
                                        case "process":
                                            $text = "กำลังดำเนินการ";
                                            $class = "badge-soft badge-warning";
                                            break;
                                        case "done":
                                            $text = "เอกสารรออนุมัติ";
                                            $class = "badge-soft badge-success";
                                            break;
                                        case "complete":
                                            $text = "เอกสารเสร็จสมบูรณ์";
                                            $class = "badge-soft badge-success";
                                            break;
                                    }
                                @endphp
                                <div class="badge {{ $class }}">{{ $text }}</div>
                                @if ($document->status == "process")
                                    <div class="bg-primary mt-1 rounded">{{ $document->assigned_user_id }} :
                                        {{ $document->assigned_user->name }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($action == "new")
                                    <button class="btn btn-accent" type="button" onclick="acceptDocument({{ $document->id }})">รับงาน</button>
                                @else
                                    <a href="{{ route("admin.user.view", ["document_id" => $document->id, "action" => $action, "type" => $type]) }}">
                                        <button class="btn btn-accent">ดูเอกสาร</button>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@push("scripts")
    @if ($action == "new")
        <script>
            function acceptDocument(documentId) {
                Swal.fire({
                    title: 'ยืนยันการรับงาน?',
                    text: "ต้องการรับงานเอกสารนี้หรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-accent me-2',
                        cancelButton: 'btn btn-ghost'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.post("{{ route("admin.user.accept") }}", {
                            id: documentId,
                            type: "{{ $type }}"
                        }).then((response) => {
                            if (response.data.status == "success") {
                                Swal.fire({
                                    title: 'สำเร็จ',
                                    text: response.data.message,
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                    timer: 1000
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'ผิดพลาด',
                                    text: response.data.message,
                                    icon: 'error',
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                    timer: 1000
                                });
                            }
                        });
                    }
                });
            }
        </script>
    @elseif($action == "approve")
        <script>
            function approveAllDocuments() {
                Swal.fire({
                    title: 'ยืนยันการอนุมัติ?',
                    text: "ต้องการอนุมัติเอกสารทั้งหมดหรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-accent me-2',
                        cancelButton: 'btn btn-ghost'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.post("{{ route("admin.user.completeall") }}", {
                            type: "{{ $type }}"
                        }).then((response) => {
                            if (response.data.status == "success") {
                                Swal.fire({
                                    title: 'สำเร็จ',
                                    text: response.data.message,
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                    timer: 1000
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'ผิดพลาด',
                                    text: response.data.message,
                                    icon: 'error',
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                    timer: 1000
                                });
                            }
                        });
                    }
                });
            }
        </script>
    @endif
    <script>
        let seconds = 30;

        function countdown() {
            document.getElementById('countdown').style.setProperty('--value', seconds);
            if (seconds === 0) {
                location.reload();
            } else {
                seconds--;
                setTimeout(countdown, 1000);
            }
        }
        countdown();

        function showDetail(element) {
            const content = element.getAttribute('data-fulltext');

            Swal.fire({
                title: '<strong>รายละเอียด</strong>',
                html: `<div class="text-left" style="font-size: 0.9rem; line-height: 1.5;">
                    ${content}
                </div>`,
                icon: 'info',
                showConfirmButton: false,
                width: '600px'
            });
        }
    </script>
@endpush
