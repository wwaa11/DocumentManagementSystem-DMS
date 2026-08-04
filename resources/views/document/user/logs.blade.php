<h5 class="card-title">ประวัติการดำเนินงาน</h5>
@foreach ($logs as $key => $doc)
    <div class="card bg-base-100">
        <table class="table-zebra table-sm table w-full">
            <thead>
                <tr class="bg-secondary text-white">
                    <th colspan="3">{{ $doc->document_tag["document_tag"] }}</th>
                </tr>
                <tr>
                    <th>วันที่</th>
                    <th>รายละเอียด</th>
                    <th>ผู้ใช้</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($doc->logs as $log)
                    <tr>
                        <td class="whitespace-nowrap">{{ $log->created_at->format("d/m/Y H:i") }}</td>
                        <td>{{ Str::limit($log->details, 100) }}</td>
                        <td class="whitespace-nowrap">{{ $log->user->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach
