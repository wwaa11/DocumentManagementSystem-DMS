@if ($document->status === 'pending')
    <button class="btn btn-success w-full" type="button" onclick="markFinish()">ทำเครื่องหมายเสร็จสิ้น</button>
@endif
@push('scripts')
    <script>
        function markFinish() {
            Swal.fire({
                title: 'ทำเครื่องหมายเสร็จสิ้น?',
                input: 'textarea',
                inputPlaceholder: 'รายละเอียด (ถ้ามี)',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton: 'btn btn-ghost'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('{{ route('admin.media.markfinish') }}', {
                        id: {{ $document->id }},
                        detail: result.value || 'ทำเครื่องหมายเสร็จสิ้น',
                    }).then((response) => {
                        if (response.data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                text: response.data.message,
                                timer: 1000,
                                showConfirmButton: false,
                                timerProgressBar: true,
                            }).then(() => {
                                window.location.href = '{{ route('admin.media.queuelist') }}';
                            });
                        } else {
                            Swal.fire({ icon: 'error', text: response.data.message });
                        }
                    });
                }
            });
        }
    </script>
@endpush
