@if ($document->status == "pending")
    <button class="btn btn-warning" onclick="addHardware()" type="button">เพิ่มอุปกรณ์</button>
    <button class="btn btn-primary" onclick="summaryHardware()" type="button">ขออนุมัติการยืมอุปกรณ์</button>
@elseif($document->status == "borrow")
    @if ($document->allHardwareRetrieve())
        <button class="btn btn-primary" onclick="summaryHardware()" type="button">ขออนุมัติการคืนอุปกรณ์</button>
    @else
        <button class="btn btn-error btn-soft" type="button">อุปกรณ์ทั้งหมดยังไม่ได้รับคืน</button>
    @endif
@endif
@push("scripts")
    <script>
        function addHardware() {
            Swal.fire({
                title: "เพิ่มอุปกรณ์",
                text: "ใส่รายละเอียดอุปกรณ์!",
                html: `
                    <fieldset class="fieldset text-start">
                        <legend class="fieldset-legend">Serial number<i class="text-red-600">*</i></legend>
                        <input type="text" id="swal-input-serial" class="input w-full" placeholder="serialnumber" />
                    </fieldset>
                    <fieldset class="fieldset text-start">
                        <legend class="fieldset-legend">วันที่ยืม<i class="text-red-600">*</i></legend>
                        <input type="date" id="swal-input-date" class="input w-full" value="{{ date("Y-m-d") }}" />
                    </fieldset>
                    <fieldset class="fieldset text-start">
                        <legend class="fieldset-legend">รายละเอียด</legend>
                        <input type="text" id="swal-input-desc" class="input w-full" placeholder="กรอกรายละเอียด..." />
                        <p class="label">Optional</p>
                    </fieldset>
                `,
                icon: "info",
                showCancelButton: true,
                confirmButtonText: "ยืนยัน",
                cancelButtonText: "ยกเลิก",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-ghost"
                },
                preConfirm: () => {
                    const serial = document.getElementById('swal-input-serial').value;
                    const date = document.getElementById('swal-input-date').value;
                    const description = document.getElementById('swal-input-desc').value;
                    if (!serial || !date) {
                        Swal.showValidationMessage('กรุณากรอก serial number / วันที่ยืม');
                        return false;
                    }
                    return {
                        serial: serial,
                        date: date,
                        description: description
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post("{{ route("admin.it.borrowlist.add") }}", {
                        id: '{{ $document->id }}',
                        serial: result.value.serial,
                        date: result.value.date,
                        detail: result.value.description,
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
                }
            });
        }

        function removeHardware(id) {
            Swal.fire({
                title: "ยืนยันการลบอุปกรณ์?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "ยืนยัน",
                cancelButtonText: "ยกเลิก",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-error me-2",
                    cancelButton: "btn btn-ghost"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post("{{ route("admin.it.borrowlist.remove") }}", {
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

        function summaryHardware() {
            Swal.fire({
                title: "ยืนยันการขออนุมัติ?",
                text: "คุณไม่สามารถกลับไปยังสถานะเดิมได้!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "ขออนุมัติ",
                cancelButtonText: "ยกเลิก",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-ghost"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post("{{ route("admin.it.borrowlist.summary") }}", {
                        id: '{{ $document->id }}',
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
                                window.location.href = "{{ route("admin.it.borrowlist") }}";
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                text: response.data.message,
                                timer: 1000,
                                timerProgressBar: true,
                                allowOutsideClick: false,
                                showConfirmButton: false,
                            })
                        }
                    });
                }
            });
        }

        function retrieveHardware(id) {
            Swal.fire({
                title: "ยืนยันการรับอุปกรณ์?",
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
                    axios.post("{{ route("admin.it.borrowlist.retrieve") }}", {
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
