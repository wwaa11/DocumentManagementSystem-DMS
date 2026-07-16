@extends('layouts.app')

@php
    $hasDateFilter = filled($start_date) || filled($end_date);
    $hasOtherFilters = filled($module) || filled($shift) || filled($status) || filled($problem_detail);
    $periodLabel = $hasDateFilter
        ? collect([$start_date, $end_date])->filter()->implode(' → ')
        : 'ทั้งหมด';
    $activeFilters = collect([
        filled($start_date) || filled($end_date)
            ? ['key' => 'date', 'label' => 'วันที่: '.collect([$start_date, $end_date])->filter()->implode(' → ')]
            : null,
        filled($module) ? ['key' => 'module', 'label' => 'Module: '.$module] : null,
        filled($shift) ? ['key' => 'shift', 'label' => 'Shift: '.$shift] : null,
        filled($status) ? ['key' => 'status', 'label' => 'สถานะ: '.$status] : null,
        filled($problem_detail) ? ['key' => 'problem_detail', 'label' => 'ปัญหา: '.$problem_detail] : null,
    ])->filter()->values();
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
                        @if ($activeFilters->isNotEmpty())
                            <span class="badge badge-primary badge-soft gap-1">
                                <i class="fas fa-filter text-xs"></i> {{ $activeFilters->count() }} ตัวกรอง
                            </span>
                        @endif
                    </div>
                    <h1 class="text-primary text-3xl font-bold tracking-tight sm:text-4xl">All Logs</h1>
                    <p class="text-base-content/65 mt-2 text-sm leading-relaxed sm:text-base">
                        รายการ HIS Logs ทั้งหมด — กรอง แก้ไข และนำเข้าจาก Excel
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

                <div class="w-full xl:max-w-sm">
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

        <section class="border-base-200/80 from-base-100 to-base-200/40 mt-6 overflow-hidden rounded-2xl border bg-gradient-to-br shadow-md">
            <form action="{{ route('admin.it.hislogs.index') }}" method="GET">
                <div class="border-base-200/70 flex flex-col gap-4 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-start gap-3">
                        <span class="bg-primary/10 text-primary ring-primary/10 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1">
                            <i class="fas fa-sliders-h"></i>
                        </span>
                        <div>
                            <h2 class="text-base font-bold tracking-tight">ตัวกรอง</h2>
                            <p class="text-base-content/55 text-xs">ค้นหาและกรองรายการตามเงื่อนไขที่ต้องการ</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="btn btn-primary btn-sm gap-2" type="submit">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                        <a class="btn btn-ghost btn-sm border-base-content/10 gap-2" href="{{ route('admin.it.hislogs.index') }}">
                            <i class="fas fa-undo"></i> ล้างทั้งหมด
                        </a>
                    </div>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <div class="form-control">
                        <label class="label py-1" for="problem_detail">
                            <span class="label-text text-xs font-semibold tracking-wide uppercase">รายละเอียดปัญหา</span>
                        </label>
                        <label class="input input-bordered flex w-full items-center gap-3">
                            <i class="fas fa-search text-base-content/40"></i>
                            <input
                                class="grow"
                                id="problem_detail"
                                type="search"
                                name="problem_detail"
                                value="{{ $problem_detail }}"
                                placeholder="พิมพ์คำค้นหาในรายละเอียดปัญหา..."
                                autocomplete="off"
                            >
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="bg-base-100/80 border-base-200/80 rounded-xl border p-4 lg:col-span-4">
                            <p class="text-base-content/50 mb-3 text-[11px] font-semibold tracking-wider uppercase">
                                <i class="fas fa-calendar-day mr-1"></i> ช่วงวันที่
                            </p>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="form-control">
                                    <label class="label py-0.5" for="start_date">
                                        <span class="label-text text-xs">เริ่มต้น</span>
                                    </label>
                                    <input class="input input-bordered input-sm w-full" id="start_date" type="date" name="start_date" value="{{ $start_date }}">
                                </div>
                                <div class="form-control">
                                    <label class="label py-0.5" for="end_date">
                                        <span class="label-text text-xs">สิ้นสุด</span>
                                    </label>
                                    <input class="input input-bordered input-sm w-full" id="end_date" type="date" name="end_date" value="{{ $end_date }}">
                                </div>
                            </div>
                        </div>

                        <div class="bg-base-100/80 border-base-200/80 rounded-xl border p-4 lg:col-span-8">
                            <p class="text-base-content/50 mb-3 text-[11px] font-semibold tracking-wider uppercase">
                                <i class="fas fa-tags mr-1"></i> เงื่อนไขเคส
                            </p>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="form-control">
                                    <label class="label py-0.5" for="module">
                                        <span class="label-text text-xs">Module</span>
                                    </label>
                                    <select class="select select-bordered select-sm w-full" id="module" name="module">
                                        <option value="">ทั้งหมด</option>
                                        @foreach ($modules as $moduleOption)
                                            <option value="{{ $moduleOption }}" @selected($module === $moduleOption)>{{ $moduleOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-control">
                                    <label class="label py-0.5" for="shift">
                                        <span class="label-text text-xs">Shift</span>
                                    </label>
                                    <select class="select select-bordered select-sm w-full" id="shift" name="shift">
                                        <option value="">ทั้งหมด</option>
                                        @foreach ($shifts as $shiftOption)
                                            <option value="{{ $shiftOption }}" @selected($shift === $shiftOption)>{{ $shiftOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-control">
                                    <label class="label py-0.5" for="status">
                                        <span class="label-text text-xs">สถานะ</span>
                                    </label>
                                    <select class="select select-bordered select-sm w-full" id="status" name="status">
                                        <option value="">ทั้งหมด</option>
                                        @foreach ($statuses as $statusOption)
                                            <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ $statusOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($activeFilters->isNotEmpty())
                        <div class="border-base-200/70 bg-base-100/60 flex flex-wrap items-center gap-2 rounded-xl border border-dashed px-3 py-3">
                            <span class="text-base-content/50 text-xs font-semibold">กำลังใช้:</span>
                            @foreach ($activeFilters as $filter)
                                <span class="badge badge-soft badge-primary gap-1">
                                    {{ $filter['label'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </form>
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
                                <th>รายละเอียดปัญหา</th>
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
                                    $problemPreview = filled($log->problem_detail)
                                        ? \Illuminate\Support\Str::limit($log->problem_detail, 250)
                                        : null;
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
                                    <td class="max-w-xs">
                                        @if ($problemPreview)
                                            <p class="text-sm leading-snug whitespace-normal" title="{{ $log->problem_detail }}">
                                                {{ $problemPreview }}
                                            </p>
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
