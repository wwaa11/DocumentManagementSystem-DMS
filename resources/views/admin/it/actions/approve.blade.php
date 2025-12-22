@if ($type == "BORROW")
    <button class="btn btn-success" onclick="approveDocument('{{ $document->status == "borrow_approve" ? "borrow" : "return" }}')">อนุมัติ</button>
    @push("scripts")
        <script>
            function approveDocument(type) {
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
                        axios.post("{{ route("admin.it.borrowlist.approve") }}", {
                            id: '{{ $document->id }}',
                            type: type
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
                                    window.location.href = "{{ route("admin.it.approvelist") }}";
                                });
                            }
                        });
                    }
                });
            }
        </script>
    @endpush
@else
    <button class="btn btn-success" onclick="approveDocument()">อนุมัติ</button>
    <button class="btn btn-danger" onclick="rejectDocument()">ไม่อนุมัติ</button>
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
                        axios.post("{{ route("admin.it.complete") }}", {
                            id: '{{ $document->id }}',
                            type: '{{ $document->document_tag["document_tag"] }}',
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
                                    window.location.href = "{{ route("admin.it.approvelist") }}";
                                });
                            }
                        });
                    }
                });
            }

            function rejectDocument() {
                Swal.fire({
                    title: "ยืนยันการปฏิเสธ?",
                    text: "ใบงานนี้จะถูกส่งกลับไปยังใบงานใหม่ เพื่อแก้ไข และ ดำเนินการอีกครั้ง!",
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
                        axios.post("{{ route("admin.it.complete") }}", {
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
                                    window.location.href = "{{ route("admin.it.approvelist") }}";
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
@endif
