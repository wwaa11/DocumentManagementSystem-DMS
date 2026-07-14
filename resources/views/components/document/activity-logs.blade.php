@props([
    'logs',
    'title' => 'ประวัติการดำเนินงาน',
])

<div {{ $attributes }}>
    <h5 class="card-title">{{ $title }}</h5>
    <table class="table-zebra table-sm table w-full">
        <thead>
            <tr>
                <th>วันที่</th>
                <th>รายละเอียด</th>
                <th>ผู้ใช้</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $log->details }}</td>
                    <td>{{ $log->user->name ?? $log->userid }}</td>
                </tr>
            @empty
                <tr>
                    <td class="opacity-40" colspan="3">ไม่มีประวัติ</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
