@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <section class="page-hero">
            <div class="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-primary/10 blur-2xl"></div>
            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-primary/70 mb-1 text-xs font-semibold tracking-wide uppercase">Documents</p>
                    <h1 class="text-primary text-3xl font-bold tracking-tight">เอกสารทั้งหมด</h1>
                    <p class="text-base-content/55 mt-2 flex flex-wrap items-center gap-2 text-sm">
                        <span class="inline-flex items-center gap-2">
                            <i class="fas fa-sync-alt text-xs"></i>
                            รีเฟรชใน
                            <span class="countdown font-mono">
                                <span class="bg-base-100/80 border-base-200 rounded-md border px-2 py-0.5" id="countdown" style="--value:30;"></span>
                            </span>
                            วินาที
                        </span>
                    </p>
                </div>
                <a class="btn btn-primary gap-2 self-start" href="{{ route('document.create') }}">
                    <i class="fas fa-plus"></i>
                    สร้างเอกสารใหม่
                </a>
            </div>
        </section>

        <form class="space-y-4" action="{{ route('document.index') }}" method="GET">
            <div class="page-surface flex flex-wrap gap-2 p-2">
                @php
                    $flags = [
                        '' => 'เอกสารทั้งหมด',
                        'my' => 'เอกสารของฉัน',
                        'approve' => 'รออนุมัติ',
                    ];
                @endphp
                @foreach ($flags as $value => $label)
                    <label class="cursor-pointer">
                        <input
                            class="peer hidden"
                            onchange="this.form.submit()"
                            type="radio"
                            name="flag"
                            value="{{ $value }}"
                            {{ request('flag', '') == $value ? 'checked' : '' }}
                        >
                        <span class="peer-checked:bg-primary peer-checked:text-primary-content hover:bg-base-200 inline-flex rounded-xl px-4 py-2 text-sm font-medium transition">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>

            <div class="filter-panel">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="grid flex-1 gap-2 md:grid-cols-2">
                        <input class="input input-bordered w-full" type="text" name="document_number" placeholder="ค้นหาเลขที่เอกสาร" value="{{ request('document_number') }}">
                        <input class="input input-bordered w-full" type="text" name="detail" placeholder="ค้นหารายละเอียด / คำสำคัญ" value="{{ request('detail') }}">
                    </div>
                    <div class="flex gap-2">
                        <button class="btn btn-primary gap-2" type="submit">
                            <i class="fas fa-search"></i>
                            ค้นหา
                        </button>
                        <label class="btn btn-outline gap-2" for="filter_toggle">
                            <i class="fas fa-sliders-h"></i>
                            ตัวกรอง
                        </label>
                    </div>
                </div>

                <input class="peer hidden" id="filter_toggle" type="checkbox" />
                <div class="border-base-200 mt-4 hidden flex-col gap-4 border-t pt-4 peer-checked:flex">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-xs font-semibold">ประเภทเอกสาร</span></label>
                            <select class="select select-bordered w-full" name="document_tag">
                                <option value="">ทั้งหมด</option>
                                <option value="IT" @selected(request('document_tag') == 'IT')>IT</option>
                                <option value="USER" @selected(request('document_tag') == 'USER')>USER</option>
                                <option value="Training" @selected(request('document_tag') == 'Training')>Training</option>
                                <option value="PURCHASE" @selected(request('document_tag') == 'PURCHASE')>PURCHASE</option>
                                <option value="MEDIA" @selected(request('document_tag') == 'MEDIA')>MEDIA</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-xs font-semibold">สถานะเอกสาร</span></label>
                            <select class="select select-bordered w-full" name="status">
                                <option value="">ทั้งหมด</option>
                                <option value="wait_approval" @selected(request('status') == 'wait_approval')>รออนุมัติจากหัวหน้าแผนก</option>
                                <option value="not_approval" @selected(request('status') == 'not_approval')>เอกสารที่ไม่อนุมัติ</option>
                                <option value="cancel" @selected(request('status') == 'cancel')>เอกสารที่ถูกยกเลิก</option>
                                <option value="pending" @selected(request('status') == 'pending')>รอดำเนินการจากหน่วยงาน</option>
                                <option value="borrow_approve" @selected(request('status') == 'borrow_approve')>รออนุมัติการยืมอุปกรณ์</option>
                                <option value="borrow" @selected(request('status') == 'borrow')>อุปกรณ์อยู่ระหว่างการยืม</option>
                                <option value="reject" @selected(request('status') == 'reject')>เอกสารที่ถูกปฏิเสธจากหน่วยงาน</option>
                                <option value="process" @selected(request('status') == 'process')>เอกสารที่กำลังดำเนินการ</option>
                                <option value="done" @selected(request('status') == 'done')>เอกสารที่รออนุมัติ</option>
                                <option value="complete" @selected(request('status') == 'complete')>เอกสารที่เสร็จสมบูรณ์</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-xs font-semibold">ช่วงวันที่สร้าง</span></label>
                            <div class="join w-full">
                                <input class="join-item input input-bordered w-1/2" type="date" name="created_at_start" value="{{ request('created_at_start') }}">
                                <input class="join-item input input-bordered w-1/2" type="date" name="created_at_end" value="{{ request('created_at_end') }}">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <a class="btn btn-ghost btn-sm" href="{{ route('document.index') }}">ล้างตัวกรอง</a>
                        <button class="btn btn-primary btn-sm px-6" type="submit">ใช้ตัวกรอง</button>
                    </div>
                </div>
            </div>
        </form>

        @if ($pendingApprovals->isNotEmpty())
            <section class="space-y-3">
                <div class="flex items-center gap-2">
                    <h2 class="text-warning text-lg font-bold">
                        <i class="fas fa-clipboard-check mr-1"></i>เอกสารที่ต้องอนุมัติ
                    </h2>
                    <span class="badge badge-warning badge-sm">{{ $pendingApprovals->count() }}</span>
                </div>
                <div class="data-table-wrap border-warning/25">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>หมายเลขเอกสาร</th>
                                <th>ประเภทเอกสาร</th>
                                <th>รายละเอียด</th>
                                <th>สถานะ</th>
                                <th>วันที่สร้าง</th>
                                <th class="text-center">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingApprovals as $document)
                                @include('document.partials.index-row', ['document' => $document])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if (request('flag') !== 'approve')
            <section class="space-y-3">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <h2 class="text-primary text-lg font-bold">
                        <i class="fas fa-folder-open mr-1"></i>
                        {{ request('flag') === 'my' ? 'เอกสารของฉัน' : 'เอกสารของฉัน / เอกสารทั้งหมด' }}
                    </h2>
                    <p class="text-base-content/50 text-sm">
                        แสดง {{ $documents->firstItem() ?? 0 }} ถึง {{ $documents->lastItem() ?? 0 }} จาก {{ number_format($documents->total()) }} รายการ
                    </p>
                </div>
                <div class="data-table-wrap">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>หมายเลขเอกสาร</th>
                                <th>ประเภทเอกสาร</th>
                                <th>รายละเอียด</th>
                                <th>สถานะ</th>
                                <th>วันที่สร้าง</th>
                                <th class="text-center">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documents as $document)
                                @include('document.partials.index-row', ['document' => $document])
                            @empty
                                <tr>
                                    <td class="text-base-content/50 py-12 text-center" colspan="6">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-inbox text-2xl opacity-40"></i>
                                            <span>ไม่พบเอกสาร</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($documents->hasPages())
                    <div class="flex justify-end">
                        {{ $documents->links() }}
                    </div>
                @endif
            </section>
        @elseif ($pendingApprovals->isEmpty())
            <div class="page-surface p-10 text-center">
                <i class="fas fa-check-circle text-success mb-3 text-3xl"></i>
                <p class="font-semibold">ไม่มีเอกสารที่ต้องอนุมัติ</p>
                <p class="text-base-content/50 mt-1 text-sm">คุณอัปเดตครบแล้วในขณะนี้</p>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        let seconds = 30;

        function countdown() {
            document.getElementById('countdown').style.setProperty('--value', seconds);
            if (seconds === 0) {
                location.reload();
            } else {
                seconds--;
                setTimeout(countdown, 1000);
            }
        }
        countdown();
    </script>
@endpush
