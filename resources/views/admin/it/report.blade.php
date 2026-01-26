@extends("layouts.app")

@section("content")
    <div class="mx-8 pb-10">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-primary text-3xl font-bold">IT Service Report</h1>
                <p class="text-base-content/60 text-sm">Dashboard สรุปข้อมูลการให้บริการ IT</p>
            </div>
            <div class="bg-base-100 rounded-lg p-4 shadow-md">
                <form class="flex items-end gap-3" action="{{ route("admin.it.reportlist") }}" method="GET">
                    <div class="form-control">
                        <label class="label pt-0"><span class="label-text text-xs font-semibold">เริ่มจากวันที่</span></label>
                        <input class="input input-bordered input-sm" type="date" name="start_date" value="{{ $start_date }}">
                    </div>
                    <div class="form-control">
                        <label class="label pt-0"><span class="label-text text-xs font-semibold">ถึงวันที่</span></label>
                        <input class="input input-bordered input-sm" type="date" name="end_date" value="{{ $end_date }}">
                    </div>
                    <button class="btn btn-primary btn-sm" type="submit">
                        <i class="fas fa-filter mr-1"></i> กรองข้อมูล
                    </button>
                    <a class="btn btn-ghost btn-sm border-base-content/20" href="{{ route("admin.it.reportlist") }}">
                        <i class="fas fa-sync-alt mr-1"></i> ล้างค่า
                    </a>
                </form>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            <div class="card bg-base-100 border-l-info border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Wait Approval</p>
                    <h2 class="text-2xl font-bold">{{ $allStats["wait_approval"] }}</h2>
                </div>
            </div>
            <div class="card bg-base-100 border-l-warning border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Pending</p>
                    <h2 class="text-2xl font-bold">{{ $allStats["pending"] }}</h2>
                </div>
            </div>
            <div class="card bg-base-100 border-l-primary border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Processing</p>
                    <h2 class="text-2xl font-bold">{{ $allStats["process"] }}</h2>
                </div>
            </div>
            <div class="card bg-base-100 border-l-secondary border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Done (Waiting)</p>
                    <h2 class="text-2xl font-bold">{{ $allStats["done"] }}</h2>
                </div>
            </div>
            <div class="card bg-base-100 border-l-success border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Completed</p>
                    <h2 class="text-2xl font-bold">{{ $allStats["complete"] }}</h2>
                </div>
            </div>
            <div class="card bg-base-100 border-l-error border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Rejected</p>
                    <h2 class="text-2xl font-bold">{{ $allStats["reject"] }}</h2>
                </div>
            </div>
        </div>

        <!-- Breakdown by Type -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="card bg-base-100 border-base-200 border shadow-sm">
                <div class="card-body p-4">
                    <h3 class="flex items-center text-sm font-bold"><i class="fas fa-tools text-primary mr-2"></i> งานแจ้งซ่อม/สนับสนุน IT</h3>
                    <div class="divider my-1"></div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        <div class="flex justify-between"><span>Wait Approval:</span> <span class="text-info font-bold">{{ $itStats["wait_approval"] }}</span></div>
                        <div class="flex justify-between"><span>Pending:</span> <span class="text-warning font-bold">{{ $itStats["pending"] }}</span></div>
                        <div class="flex justify-between"><span>Processing:</span> <span class="text-primary font-bold">{{ $itStats["process"] }}</span></div>
                        <div class="flex justify-between"><span>Done:</span> <span class="text-secondary font-bold">{{ $itStats["done"] }}</span></div>
                        <div class="flex justify-between"><span>Completed:</span> <span class="text-success font-bold">{{ $itStats["complete"] }}</span></div>
                        <div class="flex justify-between"><span>Rejected:</span> <span class="text-error font-bold">{{ $itStats["reject"] }}</span></div>
                        <div class="col-span-2 mt-1 flex justify-between border-t pt-1 font-bold"><span>Total support:</span> <span>{{ $itStats["total"] }}</span></div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border-base-200 border shadow-sm">
                <div class="card-body p-4">
                    <h3 class="flex items-center text-sm font-bold"><i class="fas fa-user-plus text-warning mr-2"></i> ขอสิทธิใช้งานโปรแกรม</h3>
                    <div class="divider my-1"></div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        <div class="flex justify-between"><span>Wait Approval:</span> <span class="text-info font-bold">{{ $userStats["wait_approval"] }}</span></div>
                        <div class="flex justify-between"><span>Pending:</span> <span class="text-warning font-bold">{{ $userStats["pending"] }}</span></div>
                        <div class="flex justify-between"><span>Processing:</span> <span class="text-primary font-bold">{{ $userStats["process"] }}</span></div>
                        <div class="flex justify-between"><span>Done:</span> <span class="text-secondary font-bold">{{ $userStats["done"] }}</span></div>
                        <div class="flex justify-between"><span>Completed:</span> <span class="text-success font-bold">{{ $userStats["complete"] }}</span></div>
                        <div class="flex justify-between"><span>Rejected:</span> <span class="text-error font-bold">{{ $userStats["reject"] }}</span></div>
                        <div class="col-span-2 mt-1 flex justify-between border-t pt-1 font-bold"><span>Total user:</span> <span>{{ $userStats["total"] }}</span></div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border-base-200 border shadow-sm">
                <div class="card-body p-4">
                    <h3 class="flex items-center text-sm font-bold"><i class="fas fa-exchange-alt text-secondary mr-2"></i> ยืม/คืน อุปกรณ์</h3>
                    <div class="divider my-1"></div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        <div class="flex justify-between"><span>Wait Approval:</span> <span class="text-info font-bold">{{ $borrowStats["wait_approval"] }}</span></div>
                        <div class="flex justify-between"><span>Pending:</span> <span class="text-warning font-bold">{{ $borrowStats["pending"] }}</span></div>
                        <div class="flex justify-between"><span>Processing:</span> <span class="text-primary font-bold">{{ $borrowStats["process"] }}</span></div>
                        <div class="flex justify-between"><span>Done:</span> <span class="text-secondary font-bold">{{ $borrowStats["done"] }}</span></div>
                        <div class="flex justify-between"><span>Completed:</span> <span class="text-success font-bold">{{ $borrowStats["complete"] }}</span></div>
                        <div class="flex justify-between"><span>Rejected:</span> <span class="text-error font-bold">{{ $borrowStats["reject"] }}</span></div>
                        <div class="col-span-2 mt-1 flex justify-between border-t pt-1 font-bold"><span>Total borrow:</span> <span>{{ $borrowStats["total"] }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2">
            <!-- 1. Department request document and how many -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title mb-4 flex items-center">
                        <i class="fas fa-building text-primary mr-2"></i>
                        จำนวนการแจ้งงานแยกตามแผนก
                    </h2>
                    <div class="h-80 overflow-y-auto">
                        <table class="table-zebra table">
                            <thead class="bg-base-100 sticky top-0 italic">
                                <tr>
                                    <th>แผนก</th>
                                    <th class="text-center">จำนวนเอกสาร</th>
                                    <th>ความร้อนแรง</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $maxCount = count($deptStats) > 0 ? max($deptStats) : 1; @endphp
                                @foreach ($deptStats as $dept => $count)
                                    <tr>
                                        <td class="font-medium">{{ $dept }}</td>
                                        <td class="text-center font-bold">{{ $count }}</td>
                                        <td>
                                            <progress class="progress progress-primary w-full" value="{{ $count }}" max="{{ $maxCount }}"></progress>
                                        </td>
                                    </tr>
                                @endforeach
                                @if (count($deptStats) == 0)
                                    <tr>
                                        <td class="text-center" colspan="3">ไม่พบข้อมูลในส่วงเวลาที่เลือก</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. Stat by user take close transfer -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title mb-4 flex items-center">
                        <i class="fas fa-user-shield text-secondary mr-2"></i>
                        ประสิทธิภาพเจ้าหน้าที่ IT
                    </h2>
                    <div class="h-80 overflow-y-auto">
                        <table class="table-zebra table">
                            <thead class="bg-base-100 sticky top-0 italic">
                                <tr class="text-center">
                                    <th class="text-left">ชื่อเจ้าหน้าที่</th>
                                    <th class="text-blue-600">รับงาน (Take)</th>
                                    <th class="text-green-600">เสร็จสิ้น (Close)</th>
                                    <th class="text-orange-600">ส่งต่อ (Transfer)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($adminStats as $admin => $stats)
                                    <tr class="text-center">
                                        <td class="text-left font-medium">{{ $admin }}</td>
                                        <td class="font-bold">{{ $stats["take"] }}</td>
                                        <td class="font-bold">{{ $stats["close"] }}</td>
                                        <td class="font-bold">{{ $stats["transfer"] }}</td>
                                    </tr>
                                @endforeach
                                @if (count($adminStats) == 0)
                                    <tr>
                                        <td class="text-center" colspan="4">ไม่พบข้อมูลในส่วงเวลาที่เลือก</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Overall Document Visualization -->
        <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-3">
            <div class="card bg-base-100 shadow-xl md:col-span-1">
                <div class="card-body text-center">
                    <h2 class="card-title justify-center">สถานะเอกสารรวม</h2>
                    <div class="mt-4 flex flex-col items-center justify-center">
                        <canvas id="statusChart" width="250" height="250"></canvas>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl md:col-span-2">
                <div class="card-body">
                    <h2 class="card-title">สรุปภาพรวมเปรียบเทียบเเผนก</h2>
                    <div class="mt-4 h-64 w-full">
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Wait Approval', 'Pending', 'Processing', 'Done', 'Completed', 'Rejected'],
                    datasets: [{
                        data: [
                            {{ $allStats["wait_approval"] }},
                            {{ $allStats["pending"] }},
                            {{ $allStats["process"] }},
                            {{ $allStats["done"] }},
                            {{ $allStats["complete"] }},
                            {{ $allStats["reject"] }}
                        ],
                        backgroundColor: [
                            '#00d1ff', // info (Wait Approval)
                            '#fbbd23', // warning (Pending)
                            '#570df8', // primary (Processing)
                            '#f000b8', // secondary (Done)
                            '#22c55e', // success (Completed)
                            '#ef4444' // error (Rejected)
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });

            // Department Bar Chart
            const deptCtx = document.getElementById('deptChart').getContext('2d');
            const deptLabels = {!! json_encode(array_keys($deptStats)) !!};
            const deptData = {!! json_encode(array_values($deptStats)) !!};

            new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: deptLabels.slice(0, 10), // Show top 10
                    datasets: [{
                        label: 'จำนวนการแจ้งเอกสาร (10 อันดับแรก)',
                        data: deptData.slice(0, 10),
                        backgroundColor: '#570df8',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
@endpush
