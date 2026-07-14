@extends('layouts.app')

@php
    $hasDateFilter = filled($start_date) || filled($end_date);
    $periodLabel = $hasDateFilter
        ? collect([$start_date, $end_date])->filter()->implode(' → ')
        : 'ทั้งหมด';
@endphp

@section('content')
    <div class="relative overflow-hidden pb-8">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-64 bg-gradient-to-br from-primary/10 via-transparent to-transparent"></div>

        <section class="from-primary/10 via-base-100 to-secondary/5 relative overflow-hidden rounded-2xl border border-base-200/80 bg-gradient-to-br p-6 shadow-lg sm:p-8">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-primary/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-16 right-20 h-36 w-36 rounded-full bg-success/10 blur-2xl"></div>

            <div class="relative flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-2xl">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="badge badge-primary badge-outline gap-1">
                            <i class="fas fa-heartbeat text-xs"></i> HIS / IT
                        </span>
                        <span class="badge badge-ghost gap-1">
                            <i class="fas fa-calendar-alt text-xs"></i> {{ $periodLabel }}
                        </span>
                    </div>
                    <h1 class="text-primary text-3xl font-bold tracking-tight sm:text-4xl">All Logs</h1>
                    <p class="text-base-content/65 mt-2 text-sm leading-relaxed sm:text-base">
                        รายการ HIS Logs ทั้งหมด — กรองช่วงเวลา แก้ไข และนำเข้าจาก Excel
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a class="btn btn-primary btn-sm gap-2" href="{{ route('admin.it.hislogs.create') }}">
                            <i class="fas fa-plus"></i> สร้าง HIS Log
                        </a>
                        <a class="btn btn-ghost btn-sm border-base-content/15 gap-2" href="{{ route('admin.it.hislogs.dashboard') }}">
                            <i class="fas fa-chart-pie"></i> Dashboard
                        </a>
                        <a class="btn btn-ghost btn-sm border-base-content/15 gap-2" href="{{ asset('HIS_Log_Dashboard.xlsx') }}" download>
                            <i class="fas fa-download"></i> ไฟล์ตัวอย่าง
                        </a>
                    </div>
                </div>

                <div class="grid w-full gap-3 sm:grid-cols-2 xl:max-w-xl">
                    <form class="bg-base-100/90 border-base-200 rounded-xl border p-4 shadow-sm backdrop-blur" action="{{ route('admin.it.hislogs.index') }}" method="GET">
                        <div class="mb-3 flex items-center gap-2">
                            <span class="bg-primary/10 text-primary flex h-8 w-8 items-center justify-center rounded-lg">
                                <i class="fas fa-filter text-sm"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold">กรองช่วงเวลา</p>
                                <p class="text-base-content/50 text-xs">แสดงเฉพาะช่วงที่เลือก</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="form-control">
                                <label class="label py-1" for="start_date"><span class="label-text text-xs">เริ่มต้น</span></label>
                                <input class="input input-bordered input-sm" id="start_date" type="date" name="start_date" value="{{ $start_date }}">
                            </div>
                            <div class="form-control">
                                <label class="label py-1" for="end_date"><span class="label-text text-xs">สิ้นสุด</span></label>
                                <input class="input input-bordered input-sm" id="end_date" type="date" name="end_date" value="{{ $end_date }}">
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <button class="btn btn-primary btn-sm flex-1" type="submit">ใช้ตัวกรอง</button>
                            <a class="btn btn-ghost btn-sm border-base-content/15" href="{{ route('admin.it.hislogs.index') }}" aria-label="ล้างตัวกรอง">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>

                    <form class="bg-base-100/90 border-base-200 rounded-xl border p-4 shadow-sm backdrop-blur" action="{{ route('admin.it.hislogs.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3 flex items-center gap-2">
                            <span class="bg-success/10 text-success flex h-8 w-8 items-center justify-center rounded-lg">
                                <i class="fas fa-file-excel text-sm"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold">Import Excel</p>
                                <p class="text-base-content/50 text-xs">ชีต HIS_Log ตามไฟล์ตัวอย่าง</p>
                            </div>
                        </div>
                        <input class="file-input file-input-bordered file-input-sm w-full" type="file" name="excel_file" accept=".xlsx,.xls" required aria-label="เลือกไฟล์ Excel">
                        <button class="btn btn-success btn-sm mt-3 w-full gap-2 text-success-content" type="submit">
                            <i class="fas fa-cloud-upload-alt"></i> นำเข้าข้อมูล
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="card bg-base-100 border-base-200/80 mt-6 border shadow-md">
            <div class="card-body gap-4 p-5 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold">รายการ HIS Logs</h2>
                        <p class="text-base-content/50 text-xs">
                            แสดง {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} จาก {{ number_format($logs->total()) }} รายการ
                        </p>
                    </div>
                    <a class="btn btn-primary btn-sm gap-2 self-start" href="{{ route('admin.it.hislogs.create') }}">
                        <i class="fas fa-plus"></i> สร้างใหม่
                    </a>
                </div>

                <div class="border-base-200 overflow-x-auto rounded-xl border">
                    <table class="table">
                        <thead class="bg-base-200/60 text-base-content/70">
                            <tr class="text-xs uppercase tracking-wide">
                                <th>วันที่ / เวลา</th>
                                <th>Shift</th>
                                <th>ผู้แจ้ง/แผนก</th>
                                <th>Module</th>
                                <th>Issue</th>
                                <th>ผู้รับเรื่อง</th>
                                <th>ผู้แก้ไข</th>
                                <th>สถานะ</th>
                                <th class="w-20 text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                @php
                                    $statusClass = match ($log->status) {
                                        'Closed' => 'badge-success',
                                        'In Progress' => 'badge-warning',
                                        default => 'badge-info',
                                    };
                                    $shiftClass = match ($log->shift) {
                                        'เช้า' => 'badge-info',
                                        'บ่าย' => 'badge-warning',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <tr class="hover:bg-base-200/40 transition-colors">
                                    <td>
                                        <div class="font-medium">{{ $log->reported_at?->format('d/m/Y') }}</div>
                                        <div class="text-base-content/50 text-xs">
                                            {{ $log->time ? \Illuminate\Support\Str::of($log->time)->substr(0, 5) : '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $shiftClass }} badge-soft badge-sm">{{ $log->shift }}</span>
                                    </td>
                                    <td class="max-w-40 truncate font-medium" title="{{ $log->reporter }}">{{ $log->reporter }}</td>
                                    <td>
                                        <span class="badge badge-ghost badge-sm font-medium">{{ $log->module }}</span>
                                    </td>
                                    <td>
                                        @if (! empty($log->issues))
                                            <div class="flex max-w-48 flex-wrap gap-1">
                                                @foreach ($log->issues as $issue)
                                                    <span class="badge badge-outline badge-sm">{{ $issue }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-base-content/40 text-sm">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->receiver }}</td>
                                    <td>{{ $log->fixer ?: '—' }}</td>
                                    <td>
                                        <span class="badge {{ $statusClass }} badge-sm gap-1">
                                            @if ($log->status === 'Closed')
                                                <i class="fas fa-check text-[10px]"></i>
                                            @elseif ($log->status === 'In Progress')
                                                <i class="fas fa-spinner text-[10px]"></i>
                                            @else
                                                <i class="fas fa-circle text-[8px]"></i>
                                            @endif
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a
                                            class="btn btn-ghost btn-sm"
                                            href="{{ route('admin.it.hislogs.edit', $log) }}"
                                            title="แก้ไข"
                                            aria-label="แก้ไข HIS Log"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="py-16" colspan="9">
                                        <div class="flex flex-col items-center justify-center gap-3 text-center">
                                            <span class="bg-base-200 text-base-content/40 flex h-16 w-16 items-center justify-center rounded-2xl">
                                                <i class="fas fa-inbox text-2xl"></i>
                                            </span>
                                            <div>
                                                <p class="font-semibold">ยังไม่มีข้อมูล HIS Log</p>
                                                <p class="text-base-content/50 mt-1 text-sm">สร้างรายการใหม่ หรือนำเข้าจากไฟล์ Excel</p>
                                            </div>
                                            <a class="btn btn-primary btn-sm gap-2" href="{{ route('admin.it.hislogs.create') }}">
                                                <i class="fas fa-plus"></i> สร้าง HIS Log
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="flex justify-end">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
