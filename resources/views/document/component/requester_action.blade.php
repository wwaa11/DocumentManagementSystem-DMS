<div class="flex flex-col gap-3">
    @if ($document->status == "wait_approval")
        <button class="btn btn-error w-full" onclick="cancelDocument()">ยกเลิกใบงาน</button>
    @elseif($document->status == "pending")
        <button class="btn btn-neutral w-full">ไม่สามาถยกเลิกใบงานได้</button>
    @endif
</div>
<div class="divider"></div>
@push("scripts")
    <script>
        async function cancelDocument() {
            const result = await Swal.fire({
                title: 'ยืนยันการยกเลิกใบงาน?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                input: 'textarea',
                inputPlaceholder: 'กรุณาใส่เหตุผลการยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-error mx-3',
                    cancelButton: 'btn btn-ghost mx-3'
                },
            });
            if (result.isConfirmed && !result.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'กรุณาใส่เหตุผลการยกเลิก',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                });
            }
            if (result.isConfirmed && result.value) {
                axios.post("{{ route("document.type.cancel", [$document_type, $document->id]) }}", {
                    reason: result.value
                }).then((response) => {
                    if (response.data.status == "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'ยกเลิกใบงานสำเร็จ',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ไม่สามารถยกเลิกใบงานได้',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            }
        }
    </script>
@endpush
