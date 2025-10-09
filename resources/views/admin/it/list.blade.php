@extends("layouts.app")
@section("content")
    <div class="mx-8">
        <h1 class="text-primary text-2xl font-bold">รายการเอกสาร </h1>
        <span class="countdown font-mono text-sm">Refesh in <span class="bg-base-300 mx-2 rounded-md px-2" id="countdown" style="--value:30;"></span> seconds</span>
        <div class="divider"></div>
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
                                {{ $document->document_type_name }} <br>
                                @if (is_array($document->title))
                                    @foreach ($document->title as $title)
                                        {{ $title }}<br>
                                    @endforeach
                                @else
                                    {{ $document->title }}
                                @endif
                            </td>
                            <td class="text-xs">{!! $document->ListDetail !!}</td>
                            <td>
                                {{ $document->creator->name }}<br>
                                {{ $document->created_at->format("d/m/Y H:i:s") }}
                            </td>
                            <td class="text-xs">
                                @foreach ($document->approvers as $approver)
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
                            <td>
                                {{ $document->status }}
                            </td>
                            <td class="text-center">
                                @if ($action == "new")
                                    <button class="btn btn-accent" type="button" onclick="acceptDocument({{ $document->id }})">รับงาน</button>
                                @else
                                    <a href="{{ route("admin.it.view", ["document_id" => $document->id, "action" => $action]) }}">
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
                        axios.post("{{ route("admin.it.accept") }}", {
                            id: documentId
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
    </script>
@endpush
