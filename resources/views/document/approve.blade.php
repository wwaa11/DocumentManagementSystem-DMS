@extends("layouts.app")
@section("content")
    <div class="justify-center gap-3 lg:flex">
        <div class="card bg-base-100 mb-4 shadow-xl lg:max-w-[600px]">
            @if ($document_type == "IT")
                @include("document.it.detail")
            @elseif ($document_type == "USER")
                @include("document.user.detail")
            @endif
        </div>
        <div class="card bg-base-100 mb-4 shadow-xl">
            <div class="card-body min-w-[400px]">
                <button class="btn btn-primary w-full" type="button" onclick="approveDocument()">Approve</button>
                <button class="btn btn-error w-full" type="button" onclick="rejectDocument()">Reject</button>
                <div class="divider"></div>
                @if ($document_type == "IT")
                    @include("document.logs", ["logs" => $document->logs])
                @elseif ($document_type == "USER")
                    @include("document.user.logs", ["logs" => $document->getAllDocuments()])
                @endif
            </div>
        </div>
    </div>
@endsection
@push("scripts")
    <script>
        async function approveDocument() {
            document.querySelector(".btn-primary").disabled = true;
            document.querySelector(".btn-error").disabled = true;
            const confirm = await Swal.fire({
                title: "ต้องการอนุมัติเอกสารนี้หรือไม่?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "อนุมัติ",
                cancelButtonText: "ยกเลิก",
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary mx-3', // DaisyUI Primary Color
                    cancelButton: 'btn btn-ghost mx-3' // DaisyUI Ghost/subtle style
                },
            });
            if (confirm.isConfirmed) {
                await axios.post("{{ route("document.type.approve.request", [$document_type, $document->id]) }}", {
                    "status": 'approve'
                }).then((response) => {
                    if (response.data.status == "success") {
                        Swal.fire({
                            title: "อนุมัติเอกสารสำเร็จ!",
                            text: "เอกสารได้ถูกอนุมัติแล้ว.",
                            icon: "success",
                            timeout: 3000,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary mx-3',
                            },
                        }).then(() => {
                            window.location = "{{ route("document.index") }}";
                        });
                    }
                });
            } else {
                document.querySelector(".btn-primary").disabled = false;
                document.querySelector(".btn-error").disabled = false;
            }
        }

        async function rejectDocument() {
            document.querySelector(".btn-primary").disabled = true;
            document.querySelector(".btn-error").disabled = true;
            const confirm = await Swal.fire({
                title: "ต้องการปฏิเสธเอกสารนี้หรือไม่?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "ปฏิเสธ",
                cancelButtonText: "ยกเลิก",
                buttonsStyling: false,
                input: "textarea",
                inputPlaceholder: "ระบุเหตุผล",
                inputAttributes: {
                    "autocomplete": "off"
                },
                customClass: {
                    confirmButton: 'btn btn-error mx-3',
                    cancelButton: 'btn btn-ghost mx-3'
                },
            });
            if (confirm.isConfirmed && !confirm.value) {
                document.querySelector(".btn-primary").disabled = false;
                document.querySelector(".btn-error").disabled = false;
                Swal.fire({
                    title: "กรุณาระบุเหตุผล!",
                    text: "เอกสารไม่สามารถถูกปฏิเสธได้ โปรดระบุเหตุผล.",
                    icon: "warning",
                    showConfirmButton: false,
                    timerProgressBar: true,
                    timer: 2000,
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary mx-3',
                    },
                });
                return;
            }
            if (confirm.isConfirmed) {
                await axios.post("{{ route("document.type.approve.request", [$document_type, $document->id]) }}", {
                    "status": 'reject',
                    "reason": confirm.value
                }).then((response) => {
                    if (response.data.status == "success") {
                        Swal.fire({
                            title: "ปฏิเสธเอกสารสำเร็จ!",
                            text: "เอกสารได้ถูกปฏิเสธแล้ว.",
                            icon: "success",
                            timeout: 3000,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary mx-3', // DaisyUI Primary Color
                            },
                        }).then(() => {
                            window.location = "{{ route("document.index") }}";
                        });
                    }
                });
            } else {
                document.querySelector(".btn-primary").disabled = false;
                document.querySelector(".btn-error").disabled = false;
            }
        }
    </script>
@endpush
