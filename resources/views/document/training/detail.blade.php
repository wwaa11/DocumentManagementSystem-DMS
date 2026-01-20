<div class="card-body">
    <button class="text-accent w-24 cursor-pointer" onclick="window.history.back()"> <i class="fas fa-arrow-left"></i> ย้อนกลับ</button>
    <div class="flex items-center">
        <img class="mr-4 h-auto w-36" src="{{ asset("images/Side Logo.png") }}" alt="Side Logo">
        <div class="flex-1 text-end">
            <h2 class="text-2xl font-bold">ใบบันทึกการฝึกอบรมภาคอิสระ</h2>
        </div>
    </div>
    <div class="divider"></div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <p><strong>ผู้ขอ:</strong> {{ $document->creator->name }}</p>
        <p><strong>แผนก:</strong> {{ $document->creator->department }}</p>
        <p class="text-end"><strong>วันที่:</strong> {{ $document->created_at->format("d/M/Y") }}</p>
    </div>
    <div class="divider"></div>
    <p><strong>ชื่อหลักสูตร:</strong>{{ $document->title }}</p>
    <p><strong>ที่มา:</strong> {{ $document->detail }}</p>
    <p><strong>วันที่ฝึกอบรม :</strong>{{ $document->start_date->format("d M Y") }} - {{ $document->end_date->format("d M Y") }}</p>
    @if ($document->files->count() > 0)
        @include("document.files", ["files" => $document->files])
    @else
        <div>ไม่มีไฟล์แนบ</div>
    @endif
    <strong>รายชื่อวิทยากร</strong>
    @if (count($document->mentors) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>รหัสพนักงาน</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th>ตำแหน่ง</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document->mentors as $mentor)
                    <tr>
                        <td>{{ $mentor->mentor }}</td>
                        <td>{{ $mentor->mentor_name }}</td>
                        <td>{{ $mentor->mentor_position }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div>
            ไม่มีวิทยากร
        </div>
    @endif
    <strong>รายชื่อผู้เข้าร่วม</strong>
    @if ($document->training_id != null)
        <table class="table w-full">
            <thead>
                <tr>
                    <th>รหัสพนักงาน</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>เวลาเข้าร่วม</th>
                    <th>เวลาอนุมัติ</th>
                </tr>
            </thead>
            <tbody id="attendance-table">
                <tr>
                    <td colspan="4">Loading...</td>
                </tr>
            </tbody>
        </table>
    @elseif (count($document->participants) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>รหัสพนักงาน</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th>ตำแหน่ง</th>
                    <th>แผนก</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document->participants as $participant)
                    <tr>
                        <td>{{ $participant->participant }}</td>
                        <td>{{ $participant->participant_name }}</td>
                        <td>{{ $participant->participant_position }}</td>
                        <td>{{ $participant->participant_department }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div>
            ไม่มีผู้เข้าร่วม
        </div>
    @endif
    @include("document.tasks", ["tasks" => $document->tasks])
</div>
@push("scripts")
    @if ($document->training_id != null)
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                axios.post("{{ route("document.training.getAttendance") }}", {
                    project_id: '{{ $document->id }}',
                }).then((response) => {
                    if (response.data.success) {
                        let html = "";
                        for (const date in response.data.transaction) {
                            html += `
                                <tr class="bg-base-200">
                                    <td colspan="4">${date}</td>
                                </tr>
                            `;
                            for (const time in response.data.transaction[date]) {
                                for (const user of response.data.transaction[date][time]) {
                                    var canApprove = user.attend_datetime != null && user.approve_datetime == null;
                                    console.log(canApprove, user.attend_datetime, user.approve_datetime);
                                    html += `
                                    <tr>
                                        <td>${user.userid}</td>
                                        <td>${user.name}</td>
                                        <td>${user.attend_datetime ? user.attend_datetime : "-"}</td>
                                        <td>${canApprove ? "<button class='btn btn-primary' onclick='approveAttendance(\"" + user.id + "\", \"" + user.userid + "\")'>อนุมัติ</button>" : user.approve_datetime ? user.approve_datetime : "-"}</td>
                                    </tr>
                                    `;
                                }
                            }
                        }
                        document.querySelector("#attendance-table").innerHTML = html;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            });

            function approveAttendance(id, userid) {
                axios.post("{{ route("document.training.approveAttendance") }}", {
                    id: id,
                    userid: userid,
                    project_id: '{{ $document->id }}',
                }).then((response) => {
                    if (response.data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'อนุมัติสำเร็จ',
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
        </script>
    @endif
@endpush
