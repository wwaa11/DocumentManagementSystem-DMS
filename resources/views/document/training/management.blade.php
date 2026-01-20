@if ($document->tasks->where("task_user", auth()->user()->userid)->count() > 0 && $document->status == "pending")
    @if ($document->training_id == null)
        <div class="flex flex-col gap-3">
            <button class="btn btn-accent w-full" onclick="createTraining()">สร้างการฝึกอบรม</button>
            <div class="divider"></div>
        </div>
    @else
        <div class="flex flex-col gap-3">
            <button class="btn btn-warning w-full" onclick="closeProject()">เสร็จสิ้นการฝึกอบรม</button>
            <div class="divider"></div>
        </div>
    @endif
@endif
@push("scripts")
    <script>
        async function createTraining() {
            const result = await Swal.fire({
                title: 'สร้างการฝึกอบรม?',
                icon: 'warning',
                description: 'หลังจากสร้างการฝึกอบรมแล้ว กรณีที่ต้องการยกเลิก กรุณาติดต่อ HR เพื่อยกเลิกการฝึกอบรม',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-accent mx-3',
                    cancelButton: 'btn btn-ghost mx-3'
                },
            });

            if (result.isConfirmed) {
                axios.post("{{ route("document.training.createTraining") }}", {
                    project_id: '{{ $document->id }}',
                }).then((response) => {
                    if (response.data.status == "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'สร้างการฝึกอบรมสำเร็จ',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            }
        }

        async function closeProject() {
            const result = await Swal.fire({
                title: 'เสร็จสิ้นการฝึกอบรม?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-accent mx-3',
                    cancelButton: 'btn btn-ghost mx-3'
                },
            });

            if (result.isConfirmed) {
                axios.post("{{ route("document.training.closeProject") }}", {
                    project_id: '{{ $document->id }}',
                }).then((response) => {
                    if (response.data.status == "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'เสร็จสิ้นการฝึกอบรมสำเร็จ',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            }
        }
    </script>
@endpush
