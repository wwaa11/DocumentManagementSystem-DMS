@extends('layouts.app')

@php
    $openCount = ($stats['status_counts']['Open'] ?? 0) + ($stats['status_counts']['In Progress'] ?? 0);
    $hasDateFilter = filled($start_date) || filled($end_date);
    $periodLabel = $hasDateFilter
        ? collect([$start_date, $end_date])->filter()->implode(' → ')
        : 'ทั้งหมด';
@endphp

@section('content')
    <div class="relative overflow-hidden pb-8">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-64 bg-gradient-to-br from-primary/10 via-transparent to-transparent"></div>

        {{-- Header --}}
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
                    <h1 class="text-primary text-3xl font-bold tracking-tight sm:text-4xl">HIS Log Dashboard</h1>
                    <p class="text-base-content/65 mt-2 text-sm leading-relaxed sm:text-base">
                        ภาพรวมเคสปัญหา HIS — กรองช่วงเวลา ดูสถานะและสถิติ
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a class="btn btn-primary btn-sm gap-2" href="{{ route('admin.it.hislogs.create') }}">
                            <i class="fas fa-plus"></i> สร้าง HIS Log
                        </a>
                        <a class="btn btn-ghost btn-sm border-base-content/15 gap-2" href="{{ route('admin.it.hislogs.index') }}">
                            <i class="fas fa-list"></i> All Logs
                        </a>
                    </div>
                </div>

                <div class="w-full xl:max-w-sm">
                    <form class="bg-base-100/90 border-base-200 rounded-xl border p-4 shadow-sm backdrop-blur" action="{{ route('admin.it.hislogs.dashboard') }}" method="GET">
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
                            <a class="btn btn-ghost btn-sm border-base-content/15" href="{{ route('admin.it.hislogs.dashboard') }}" aria-label="ล้างตัวกรอง">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- KPI --}}
        <section class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="group card bg-base-100 border-base-200/80 hislog-kpi border shadow-md transition duration-200 hover:-translate-y-0.5 hover:shadow-xl">
                <div class="card-body gap-3 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-base-content/55 text-xs font-semibold tracking-wide uppercase">Total Cases</p>
                            <p class="mt-1 text-3xl font-bold tracking-tight">{{ number_format($stats['total']) }}</p>
                        </div>
                        <span class="bg-primary/10 text-primary flex h-11 w-11 items-center justify-center rounded-xl transition group-hover:scale-105">
                            <i class="fas fa-layer-group"></i>
                        </span>
                    </div>
                    <p class="text-base-content/45 text-xs">เคสทั้งหมดในช่วงที่เลือก</p>
                </div>
            </article>

            <article class="group card bg-base-100 border-base-200/80 hislog-kpi border shadow-md transition duration-200 hover:-translate-y-0.5 hover:shadow-xl">
                <div class="card-body gap-3 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-base-content/55 text-xs font-semibold tracking-wide uppercase">Closed</p>
                            <p class="mt-1 text-3xl font-bold tracking-tight text-success">{{ number_format($stats['closed']) }}</p>
                        </div>
                        <span class="bg-success/10 text-success flex h-11 w-11 items-center justify-center rounded-xl transition group-hover:scale-105">
                            <i class="fas fa-check-circle"></i>
                        </span>
                    </div>
                    <p class="text-base-content/45 text-xs">ปิดงานแล้วสำเร็จ</p>
                </div>
            </article>

            <article class="group card bg-base-100 border-base-200/80 hislog-kpi border shadow-md transition duration-200 hover:-translate-y-0.5 hover:shadow-xl">
                <div class="card-body gap-3 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-base-content/55 text-xs font-semibold tracking-wide uppercase">Close Rate</p>
                            <p class="mt-1 text-3xl font-bold tracking-tight text-info">{{ $stats['close_rate'] }}%</p>
                        </div>
                        <span class="bg-info/10 text-info flex h-11 w-11 items-center justify-center rounded-xl transition group-hover:scale-105">
                            <i class="fas fa-percentage"></i>
                        </span>
                    </div>
                    <progress class="progress progress-info h-2 w-full" value="{{ $stats['close_rate'] }}" max="100"></progress>
                </div>
            </article>

            <article class="group card bg-base-100 border-base-200/80 hislog-kpi border shadow-md transition duration-200 hover:-translate-y-0.5 hover:shadow-xl">
                <div class="card-body gap-3 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-base-content/55 text-xs font-semibold tracking-wide uppercase">Active Cases</p>
                            <p class="mt-1 text-3xl font-bold tracking-tight text-warning">{{ number_format($openCount) }}</p>
                        </div>
                        <span class="bg-warning/10 text-warning flex h-11 w-11 items-center justify-center rounded-xl transition group-hover:scale-105">
                            <i class="fas fa-bolt"></i>
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="badge badge-info badge-soft badge-sm">Open {{ $stats['status_counts']['Open'] ?? 0 }}</span>
                        <span class="badge badge-warning badge-soft badge-sm">In Progress {{ $stats['status_counts']['In Progress'] ?? 0 }}</span>
                    </div>
                </div>
            </article>
        </section>

        {{-- Charts --}}
        <section class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <article class="card bg-base-100 border-base-200/80 border shadow-md">
                <div class="card-body p-5 sm:p-6">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold">Case Status</h2>
                            <p class="text-base-content/50 text-xs">สัดส่วนสถานะเคส</p>
                        </div>
                        <span class="bg-info/10 text-info rounded-lg px-2.5 py-1 text-xs font-medium">
                            <i class="fas fa-chart-pie mr-1"></i> สถานะ
                        </span>
                    </div>
                    <div class="relative mx-auto h-64 w-full max-w-xs">
                        <canvas id="statusChart"></canvas>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-base-content/45 text-[11px] tracking-wide uppercase">Total</span>
                            <span class="text-2xl font-bold leading-none">{{ number_format($stats['total']) }}</span>
                        </div>
                    </div>
                </div>
            </article>

            <article class="card bg-base-100 border-base-200/80 border shadow-md">
                <div class="card-body p-5 sm:p-6">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold">Case by Shift</h2>
                            <p class="text-base-content/50 text-xs">เช้า · บ่าย · ดึก</p>
                        </div>
                        <span class="bg-secondary/10 text-secondary rounded-lg px-2.5 py-1 text-xs font-medium">
                            <i class="fas fa-clock mr-1"></i> Shift
                        </span>
                    </div>
                    <div class="relative mx-auto h-64 w-full max-w-xs">
                        <canvas id="shiftChart"></canvas>
                    </div>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        @foreach ($stats['shift_counts'] as $shift => $count)
                            @php
                                $shiftTone = match ($shift) {
                                    'เช้า' => 'bg-sky-500/10 text-sky-700',
                                    'บ่าย' => 'bg-amber-500/10 text-amber-700',
                                    default => 'bg-indigo-500/10 text-indigo-700',
                                };
                                $shiftIcon = match ($shift) {
                                    'เช้า' => 'fa-sun',
                                    'บ่าย' => 'fa-cloud-sun',
                                    default => 'fa-moon',
                                };
                            @endphp
                            <div class="rounded-xl {{ $shiftTone }} px-3 py-2 text-center">
                                <p class="text-[11px] font-medium"><i class="fas {{ $shiftIcon }} mr-1"></i>{{ $shift }}</p>
                                <p class="text-lg font-bold">{{ number_format($count) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="card bg-base-100 border-base-200/80 border shadow-md lg:col-span-2">
                <div class="card-body p-5 sm:p-6">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold">Top 10 Modules</h2>
                            <p class="text-base-content/50 text-xs">โมดูลที่มีเคสสูงสุด</p>
                        </div>
                        <span class="bg-primary/10 text-primary rounded-lg px-2.5 py-1 text-xs font-medium">
                            <i class="fas fa-cubes mr-1"></i> Modules
                        </span>
                    </div>
                    <div class="mt-1 h-72 w-full">
                        <canvas id="moduleChart"></canvas>
                    </div>
                </div>
            </article>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.register(ChartDataLabels);

            const statusCounts = @json($stats['status_counts']);
            const shiftCounts = @json($stats['shift_counts']);
            const moduleCounts = @json($stats['top_modules']);
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const chartDefaults = {
                responsive: true,
                maintainAspectRatio: false,
                animation: prefersReducedMotion ? false : { duration: 700, easing: 'easeOutQuart' },
            };

            const softGrid = {
                color: 'rgba(148, 163, 184, 0.18)',
                drawBorder: false,
            };

            const barValueLabels = {
                color: '#334155',
                font: { weight: '600', size: 11 },
                formatter: (value) => (value > 0 ? value : ''),
                display: (context) => (context.dataset.data[context.dataIndex] || 0) > 0,
                clamp: true,
                clip: false,
            };

            const verticalBarLabels = {
                ...barValueLabels,
                anchor: 'end',
                align: 'top',
                offset: 2,
            };

            const doughnutValueLabels = {
                color: '#0f172a',
                font: { weight: '600', size: 11 },
                formatter: (value) => (value > 0 ? value : ''),
                display: (context) => (context.dataset.data[context.dataIndex] || 0) > 0,
            };

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusCounts),
                    datasets: [{
                        data: Object.values(statusCounts),
                        backgroundColor: ['#38bdf8', '#fbbf24', '#34d399'],
                        hoverOffset: 6,
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        spacing: 2,
                    }],
                },
                options: {
                    ...chartDefaults,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 16,
                                boxWidth: 8,
                            },
                        },
                        datalabels: doughnutValueLabels,
                    },
                },
            });

            new Chart(document.getElementById('shiftChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(shiftCounts),
                    datasets: [{
                        data: Object.values(shiftCounts),
                        backgroundColor: ['#0ea5e9', '#f59e0b', '#6366f1'],
                        hoverOffset: 6,
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        spacing: 2,
                    }],
                },
                options: {
                    ...chartDefaults,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            display: false,
                        },
                        datalabels: doughnutValueLabels,
                    },
                },
            });

            const moduleLabels = Object.keys(moduleCounts);
            const moduleValues = Object.values(moduleCounts);

            new Chart(document.getElementById('moduleChart'), {
                type: 'bar',
                data: {
                    labels: moduleLabels,
                    datasets: [{
                        label: 'จำนวนเคส',
                        data: moduleValues,
                        backgroundColor: moduleLabels.map((_, index) => {
                            const alpha = 0.95 - (index * 0.06);
                            return `rgba(16, 185, 129, ${Math.max(alpha, 0.35)})`;
                        }),
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 36,
                    }],
                },
                options: {
                    ...chartDefaults,
                    layout: { padding: { top: 18 } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grace: '10%',
                            ticks: { precision: 0 },
                            grid: softGrid,
                        },
                        x: {
                            ticks: { maxRotation: 40, minRotation: 0, autoSkip: true },
                            grid: { display: false },
                        },
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => ` ${context.parsed.y} เคส`,
                            },
                        },
                        datalabels: verticalBarLabels,
                    },
                },
            });
        });
    </script>
@endpush
