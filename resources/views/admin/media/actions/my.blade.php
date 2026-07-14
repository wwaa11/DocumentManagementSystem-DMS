<button class="btn btn-success w-full" type="button" onclick="markFinish()">ทำเครื่องหมายเสร็จสิ้น</button>
<div class="divider"></div>
<form id="process-form" action="{{ route('admin.media.process') }}" method="post" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ $document->id }}">
    <fieldset class="fieldset">
        <legend class="fieldset-legend">รายละเอียดการดำเนินงาน / ส่งต่องาน</legend>
        <textarea class="textarea textarea-primary w-full" id="detail" name="detail" rows="6" placeholder="รายละเอียดการทำงาน...."></textarea>
        <p class="label">แนบไฟล์ (ถ้ามี)</p>
        <x-form.file-dropzone />
        <p class="label">ส่งต่องาน <span class="text-error">*กรณีมีการระบุ ใบงานจะถูกส่งต่อไปยังผู้ใช้งานนี้</span></p>
        <select class="select select-primary mb-2 w-full" name="transfer_userid">
            <option value="" selected>ใบงานนี้ดำเนินการเรียบร้อย (ส่งหัวหน้าอนุมัติ)</option>
            <option class="text-warning" value="new">ส่งต่อไปยังใบงานใหม่</option>
            @foreach ($userList as $user)
                <option value="{{ $user->userid }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </fieldset>
    <button class="btn btn-soft btn-success w-full" type="button" onclick="submitForm()">บันทึกการดำเนินการ</button>
</form>
<button class="btn btn-ghost" onclick="cancelJob()" type="button">ยกเลิกการรับงาน</button>
<div class="divider"></div>
<button class="btn btn-dash btn-error" onclick="cancelDocument()" type="button">ยกเลิกเอกสารนี้</button>
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

        function submitForm() {
            const detail = $('#detail').val();
            const sendTo = $('select[name="transfer_userid"]').val();

            if (!detail && sendTo === null) {
                Swal.fire({
                    title: 'ไม่สามารถดำเนินการได้!',
                    text: 'กรุณากรอกรายละเอียด หรือ ผู้รับงาน',
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                return;
            }

            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ไม่ยืนยัน',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-warning me-2',
                    cancelButton: 'btn btn-ghost'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#process-form').submit();
                }
            });
        }

        async function cancelJob() {
            const swal = await Swal.fire({
                title: 'ยืนยันการยกเลิกการรับงานนี้?',
                text: 'งานนี้จะถูกส่งไปยังใบงานใหม่',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยกเลิกรับงานนี้',
                cancelButtonText: 'ไม่ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-ghost me-2',
                    cancelButton: 'btn btn-warning'
                }
            });
            if (swal.isConfirmed) {
                axios.post('{{ route('admin.media.canceljob') }}', {
                    id: {{ $document->id }},
                }).then(function(response) {
                    if (response.data.status == 'success') {
                        Swal.fire({
                            title: 'ยกเลิกการรับงานสำเร็จ!',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true,
                        }).then(function() {
                            window.location.href = '{{ route('admin.media.mylist') }}';
                        });
                    } else {
                        Swal.fire({
                            title: response.data.message,
                            icon: 'error',
                            showConfirmButton: false,
                            timer: 1000,
                        });
                    }
                });
            }
        }

        async function cancelDocument() {
            const swal = await Swal.fire({
                title: 'ยืนยันการยกเลิกเอกสารนี้?',
                icon: 'warning',
                input: 'textarea',
                inputPlaceholder: 'กรุณาใส่เหตุผลการยกเลิก',
                showCancelButton: true,
                confirmButtonText: 'ยกเลิกเอกสารนี้',
                cancelButtonText: 'ไม่ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-ghost me-2',
                    cancelButton: 'btn btn-error'
                }
            });
            if (swal.isConfirmed && !swal.value) {
                Swal.fire({
                    title: 'กรุณาใส่เหตุผลการยกเลิก',
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 1000,
                });
                return;
            }
            if (swal.isConfirmed && swal.value) {
                axios.post('{{ route('admin.media.cancel') }}', {
                    id: {{ $document->id }},
                    reason: swal.value,
                }).then(function(response) {
                    if (response.data.status == 'success') {
                        Swal.fire({
                            title: 'ยกเลิกเอกสารสำเร็จ!',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1000,
                        }).then(function() {
                            window.location.href = '{{ route('admin.media.mylist') }}';
                        });
                    }
                });
            }
        }
    </script>
@endpush
