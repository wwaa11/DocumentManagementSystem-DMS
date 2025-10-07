    <h5 class="card-title">ประวัติการดำเนินงาน</h5>
    <table class="table-zebra table-sm table w-full">
        <thead>
            <tr>
                <th>วันที่</th>
                <th>รายละเอียด</th>
                <th>ผู้ใช้</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format("d/m/Y H:i") }}</td>
                    <td>{{ $log->details }}</td>
                    <td>{{ $log->user->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
