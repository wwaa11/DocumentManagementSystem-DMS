<button class="btn btn-success" onclick="approveDocument()">อนุมัติ</button>
<button class="btn btn-error" onclick="rejectDocument()">ปฏิเสธ</button>

@push("scripts")
    <script>
        function approveDocument() {
            Swal.fire({
                title: "ยืนยันการอนุมัติ?",
                text: "คุณไม่สามารถกลับไปยังสถานะเดิมได้!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "อนุมัติ",
                cancelButtonText: "ยกเลิก",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-ghost"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post("{{ route("admin.it.hardware.approve") }}", {
                        id: '{{ $document->id }}',
                        status: "approve",
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
                                window.location.href = "{{ route("admin.it.hardwarelist") }}";
                            });
                        }
                    });
                }
            });
        }

        function rejectDocument() {
            Swal.fire({
                title: "ยืนยันการปฏิเสธ?",
                text: "คุณไม่สามารถกลับไปยังสถานะเดิมได้!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "ปฏิเสธ",
                cancelButtonText: "ยกเลิก",
                buttonsStyling: false,
                input: "textarea",
                inputPlaceholder: "กรุณากรอกเหตุผลการปฏิเสธ...",
                customClass: {
                    confirmButton: "btn btn-error me-2",
                    cancelButton: "btn btn-ghost"
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    axios.post("{{ route("admin.it.hardware.approve") }}", {
                        id: '{{ $document->id }}',
                        status: "reject",
                        reason: result.value
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
                                window.location.href = "{{ route("admin.it.hardwarelist") }}";
                            });
                        }
                    });
                } else if (result.isConfirmed && !result.value) {
                    Swal.fire({
                        icon: "error",
                        text: "กรุณากรอกเหตุผลการปฏิเสธ!",
                        timer: 1000,
                        timerProgressBar: true,
                    });
                }
            });
        }
    </script>
@endpush
