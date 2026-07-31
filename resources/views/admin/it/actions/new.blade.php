@if ($document->assigned_user_id === null)
    <button class="btn btn-accent w-full" type="button" onclick="acceptDocument()">รับงาน</button>
    @push('scripts')
        <script>
            function acceptDocument() {
                Swal.fire({
                    title: 'ยืนยันการรับงาน?',
                    text: 'ต้องการรับงานเอกสารนี้หรือไม่?',
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
                        axios.post('{{ route('admin.it.accept') }}', {
                            id: '{{ $document->id }}',
                            type: '{{ $document->document_tag['document_tag'] }}'
                        }).then((response) => {
                            if (response.data.status == 'success') {
                                Swal.fire({
                                    title: 'สำเร็จ',
                                    text: response.data.message,
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                    timer: 1000
                                }).then(() => {
                                    window.location.href = '{{ route('admin.it.view', ['document_id' => $document->id, 'action' => 'my', 'type' => $document->document_tag['document_tag']]) }}';
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
    @endpush
@endif
