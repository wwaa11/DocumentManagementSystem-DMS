@extends('layouts.app')

@section('content')
    <div class="mx-8 pb-10">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-primary text-3xl font-bold">Purchase Report</h1>
                <p class="text-base-content/60 text-sm">Dashboard สรุปข้อมูลเอกสารจัดซื้อ</p>
            </div>
            <div class="bg-base-100 rounded-lg p-4 shadow-md">
                <form class="flex items-end gap-3" action="{{ route('admin.purchase.reportlist') }}" method="GET">
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
                    <a class="btn btn-ghost btn-sm border-base-content/20" href="{{ route('admin.purchase.reportlist') }}">
                        <i class="fas fa-sync-alt mr-1"></i> ล้างค่า
                    </a>
                </form>
            </div>
        </div>

        <div class="divider"></div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            <div class="card bg-base-100 border-l-info border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Wait Approval</p>
                    <h2 class="text-2xl font-bold">{{ $allStats['wait_approval'] }}</h2>
                </div>
            </div>
            <div class="card bg-base-100 border-l-warning border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Pending</p>
                    <h2 class="text-2xl font-bold">{{ $allStats['pending'] }}</h2>
                </div>
            </div>
            <div class="card bg-base-100 border-l-primary border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Processing</p>
                    <h2 class="text-2xl font-bold">{{ $allStats['process'] }}</h2>
                </div>
            </div>
            <div class="card bg-base-100 border-l-secondary border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Done (Waiting)</p>
                    <h2 class="text-2xl font-bold">{{ $allStats['done'] }}</h2>
                </div>
            </div>
            <div class="card bg-base-100 border-l-success border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Completed</p>
                    <h2 class="text-2xl font-bold">{{ $allStats['complete'] }}</h2>
                </div>
            </div>
            <div class="card bg-base-100 border-l-error border-l-4 shadow-xl">
                <div class="card-body p-4">
                    <p class="text-base-content/60 truncate text-xs font-medium uppercase">Rejected</p>
                    <h2 class="text-2xl font-bold">{{ $allStats['reject'] }}</h2>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title text-lg">สรุปตามแผนก</h3>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>แผนก</th>
                                    <th class="text-end">จำนวน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($deptStats as $dept => $total)
                                    <tr>
                                        <td>{{ $dept }}</td>
                                        <td class="text-end">{{ $total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="2">ไม่มีข้อมูล</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title text-lg">สรุปการทำงานของผู้ดูแล</h3>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ผู้ดูแล</th>
                                    <th class="text-end">รับงาน</th>
                                    <th class="text-end">ปิดงาน</th>
                                    <th class="text-end">ส่งต่อ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adminStats as $admin => $stats)
                                    <tr>
                                        <td>{{ $admin }}</td>
                                        <td class="text-end">{{ $stats['take'] }}</td>
                                        <td class="text-end">{{ $stats['close'] }}</td>
                                        <td class="text-end">{{ $stats['transfer'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="4">ไม่มีข้อมูล</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
