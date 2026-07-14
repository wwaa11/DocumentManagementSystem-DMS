<form id="process-form" action="{{ route("admin.it.process") }}" method="post" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ $document->id }}">
    <input type="hidden" name="type" value="{{ $document->document_tag["document_tag"] }}">
    <fieldset class="fieldset">
        <legend class="fieldset-legend">รายละเอียดการดำเนินงาน</legend>
        <textarea class="textarea textarea-primary w-full" id="detail" name="detail" rows="8" placeholder="รายละเอียดการทำงาน...."></textarea>
        <p class="label">แนบไฟล์ (ถ้ามี)</p>
        <x-form.file-dropzone />
        <p class="label">ส่งต่องาน <span class="text-error">*กรณีมีการระบุ ใบงานจะถูกส่งต่อไปยังผู้ใช้งานนี้</span></p>
        <select class="select select-primary mb-2 w-full" name="transfer_userid">
            <option value="" selected>ใบงานนี้ดำเนินการเรียบร้อย</option>
            <option class="text-warning" value="new">ใบงานนี้ดำเนินการเรียบร้อย ส่งต่อไปยังใบงานใหม่</option>
            @foreach ($userList as $user)
                <option value="{{ $user->userid }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </fieldset>
    <button class="btn btn-soft btn-success w-full" type="button" onclick="submitForm()">ดำเนินการเสร็จสิ้น</button>
</form>
<button class="btn btn-ghost" onclick="cancelJob()" type="button">ยกเลิกการรับงาน</button>
<div class="divider"></div>
<button class="btn btn-dash btn-error" onclick="cancelDocument()" type="button">ยกเลิกการเอกสารนี้</button>
@push("scripts")
    <script>
        function submitForm() {
            const detail = $('#detail').val();
            const sendTo = $('select[name="transfer_userid"]').val();

            if (!detail && !sendTo) {
                Swal.fire({
                    title: "ไม่สามารถดำเนินการได้!",
                    text: "กรุณากรอกรายละเอียด หรือ ผู้รับงาน",
                    icon: "error",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                return;
            }

            var sendDetail = '';
            if (detail && !sendTo) {
                sendDetail = 'ยินยันการดำเนินการ และปิดใบงานนี้';
            } else if (detail && sendTo) {
                sendDetail = 'ยินยันการดำเนินการ และส่งงานไปยังผู้รับที่ระบุ';
            } else {
                sendDetail = 'ไม่มีการดำเนินการ ส่งงานไปยังผู้รับที่ระบุ';
            }

            Swal.fire({
                title: "ยืนยันการดำเนินการ?",
                text: sendDetail,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "ยืนยัน",
                cancelButtonText: "ไม่ยืนยัน",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-warning me-2",
                    cancelButton: "btn btn-ghost"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#process-form").submit();
                }
            });
        }

        async function cancelJob() {
            const swal = await Swal.fire({
                title: "ยืนยันการยกเลิกงานการรับงานนี้?",
                text: "งานนี้จะถูกส่งไปยังใบงานใหม่ เพื่อให้ผู้ใช้งานอื่นสามารถดำเนินการได้",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "ยกเลิกรับงานนี้",
                cancelButtonText: "ไม่ยกเลิก",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-ghost me-2",
                    cancelButton: "btn btn-warning"
                }
            });
            if (swal.isConfirmed) {
                axios.post("{{ route("admin.it.canceljob") }}", {
                    id: {{ $document->id }},
                    type: '{{ $document->document_tag["document_tag"] }}',
                }).then(function(response) {
                    if (response.data.status == "success") {
                        Swal.fire({
                            title: "ยกเลิกการรับงานสำเร็จ!",
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true,
                        }).then(function() {
                            window.location.href = "{{ route("admin.it.mylist") }}";
                        });
                    } else {
                        Swal.fire({
                            title: response.data.message,
                            icon: "error",
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true,
                        });
                    }
                });
            }
        }

        async function cancelDocument() {
            const swal = await Swal.fire({
                title: "ยืนยันการยกเลิกเอกสารนี้?",
                text: "เอกสารนี้จะถูกยกเลิกและไม่สามารถแก้ไขได้",
                icon: "warning",
                input: "textarea",
                inputPlaceholder: "กรุณาใส่เหตุผลการยกเลิก",
                showCancelButton: true,
                confirmButtonText: "ยกเลิกเอกสารนี้",
                cancelButtonText: "ไม่ยกเลิก",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-ghost me-2",
                    cancelButton: "btn btn-error"
                }
            });
            if (swal.isConfirmed && !swal.value) {
                Swal.fire({
                    title: "กรุณาใส่เหตุผลการยกเลิก",
                    icon: "warning",
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: true,
                });
            }
            if (swal.isConfirmed && swal.value) {
                axios.post("{{ route("admin.it.cancel") }}", {
                    id: {{ $document->id }},
                    type: '{{ $document->document_tag["document_tag"] }}',
                    reason: swal.value,
                }).then(function(response) {
                    if (response.data.status == "success") {
                        Swal.fire({
                            title: "ยกเลิกเอกสารสำเร็จ!",
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true,
                        }).then(function() {
                            window.location.href = "{{ route("admin.it.mylist") }}";
                        });
                    }
                });
            }

        }
    </script>
@endpush
