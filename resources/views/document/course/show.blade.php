@extends('layouts.app')

@section('content')
    @php
        $planStatus = match ($plan->status) {
            'complete' => ['label' => 'อนุมัติครบแล้ว', 'class' => 'badge-success', 'icon' => 'fas fa-check-circle'],
            'not_approval' => ['label' => 'ไม่อนุมัติ', 'class' => 'badge-error', 'icon' => 'fas fa-times-circle'],
            'cancel' => ['label' => 'ยกเลิก', 'class' => 'badge-error', 'icon' => 'fas fa-ban'],
            default => ['label' => 'อยู่ระหว่างอนุมัติ', 'class' => 'badge-warning', 'icon' => 'fas fa-clock'],
        };
        $approvedCount = $plan->approvers->where('status', 'approve')->count();
        $totalCost = $plan->items->sum(fn ($item) => (float) ($item->estimated_cost ?? 0));
    @endphp

    <div class="mx-auto max-w-6xl pb-12">
        <header class="bg-primary text-primary-content mb-6 overflow-hidden rounded-3xl shadow-xl">
            <div class="relative p-6 md:p-8">
                <i class="fas fa-graduation-cap absolute -right-6 -bottom-8 text-9xl opacity-10"></i>
                <div class="relative flex flex-wrap items-start justify-between gap-5">
                    <div>
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="badge border-primary-content/30 bg-primary-content/10 text-primary-content">แผนประจำปี {{ $plan->year }}</span>
                            <span class="badge {{ $planStatus['class'] }}">
                                <i class="{{ $planStatus['icon'] }} mr-1"></i>{{ $planStatus['label'] }}
                            </span>
                        </div>
                        <h1 class="text-2xl font-bold md:text-3xl">แผนหลักสูตรการฝึกอบรม</h1>
                        <p class="mt-2 text-sm opacity-80">{{ $plan->department }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a class="btn min-h-11 border-primary-content/20 bg-primary-content/10 text-primary-content hover:bg-primary-content/20"
                            href="{{ route('document.course', ['year' => $plan->year]) }}">
                            <i class="fas fa-arrow-left"></i> กลับรายการ
                        </a>
                        @if ($canEdit)
                            <a class="btn min-h-11 border-primary-content/20 bg-primary-content/10 text-primary-content hover:bg-primary-content/20"
                                href="{{ route('document.create.type', ['document_type' => 'training', 'department' => $plan->department, 'year' => $plan->year]) }}">
                                <i class="fas fa-chalkboard-teacher"></i> สร้างฝึกอบรมนอกแผน
                            </a>
                            <a class="btn min-h-11 border-0 bg-primary-content text-primary hover:bg-primary-content/90"
                                href="{{ route('document.course.edit', $plan) }}">
                                <i class="fas fa-pen"></i> แก้ไขแบบฟอร์ม
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error mb-4">{{ session('error') }}</div>
        @endif

        <section class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="สรุปแบบฟอร์ม">
            <div class="card bg-base-100 border-base-200 border shadow-sm">
                <div class="card-body gap-1 p-4">
                    <span class="text-base-content/50 text-xs font-bold uppercase">จำนวนหลักสูตร</span>
                    <span class="text-2xl font-bold">{{ $plan->items->count() }}</span>
                    <span class="text-base-content/50 text-xs">รายการในแบบฟอร์ม</span>
                </div>
            </div>
            <div class="card bg-base-100 border-base-200 border shadow-sm">
                <div class="card-body gap-1 p-4">
                    <span class="text-base-content/50 text-xs font-bold uppercase">ความคืบหน้าอนุมัติ</span>
                    <span class="text-2xl font-bold">{{ $approvedCount }}/{{ $plan->approvers->count() }}</span>
                    <progress class="progress progress-primary mt-1" value="{{ $approvedCount }}" max="{{ max($plan->approvers->count(), 1) }}"></progress>
                </div>
            </div>
            <div class="card bg-base-100 border-base-200 border shadow-sm">
                <div class="card-body gap-1 p-4">
                    <span class="text-base-content/50 text-xs font-bold uppercase">งบประมาณรวม</span>
                    <span class="text-2xl font-bold">{{ number_format($totalCost, 2) }}</span>
                    <span class="text-base-content/50 text-xs">บาท</span>
                </div>
            </div>
            <div class="card bg-base-100 border-base-200 border shadow-sm">
                <div class="card-body gap-1 p-4">
                    <span class="text-base-content/50 text-xs font-bold uppercase">ผู้จัดทำ</span>
                    <span class="truncate font-bold">{{ $plan->creator->name ?? $plan->created_by }}</span>
                    <span class="text-base-content/50 text-xs">{{ $plan->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </section>

        @if ($pendingLevels->isNotEmpty())
            <section class="mb-6 overflow-hidden rounded-2xl border border-warning/40 bg-warning/5 shadow-xl">
                <div class="border-warning/30 flex items-start gap-4 border-b bg-warning/10 p-5 md:p-6">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-warning text-warning-content">
                        <i class="fas fa-clipboard-check"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold">เอกสารนี้รอการตัดสินใจจากคุณ</h2>
                        <p class="text-base-content/70 mt-1 text-sm">
                            @if ($pendingLevels->count() > 1)
                                คุณรับผิดชอบระดับ {{ $pendingLevels->pluck('level')->join(', ') }} การอนุมัติครั้งเดียวจะบันทึกครบทุกระดับนี้
                            @else
                                คุณเป็นผู้อนุมัติระดับ {{ $pendingLevels->first()->level }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="grid gap-5 p-5 md:grid-cols-[auto_1fr] md:p-6">
                    <form method="POST" action="{{ route('document.course.approve', $plan) }}">
                        @csrf
                        <input type="hidden" name="decision" value="approve">
                        <button class="btn btn-success min-h-12 w-full min-w-44 cursor-pointer md:w-auto" type="submit">
                            <i class="fas fa-check"></i> อนุมัติแบบฟอร์ม
                        </button>
                    </form>
                    <form class="grid gap-2 sm:grid-cols-[1fr_auto]" method="POST" action="{{ route('document.course.approve', $plan) }}">
                        @csrf
                        <input type="hidden" name="decision" value="reject">
                        <label class="form-control">
                            <span class="label pt-0"><span class="label-text text-xs font-bold">เหตุผลที่ไม่อนุมัติ</span></span>
                            <input class="input input-bordered focus:input-error min-h-12 w-full" type="text" name="reason"
                                placeholder="ระบุเหตุผลก่อนกดไม่อนุมัติ">
                        </label>
                        <button class="btn btn-error min-h-12 cursor-pointer self-end" type="submit">
                            <i class="fas fa-times"></i> ไม่อนุมัติ
                        </button>
                    </form>
                </div>
            </section>
        @endif

        <section class="mb-8 card bg-base-100 border-base-200 border shadow-xl">
            <div class="card-body">
                <div class="mb-5">
                    <h2 class="text-lg font-bold">ลำดับการอนุมัติ</h2>
                    <p class="text-base-content/60 mt-1 text-sm">แบบฟอร์มจะส่งต่อจากระดับ 1 ไปยังระดับ 3 ตามลำดับ</p>
                </div>
                <div class="grid gap-4 lg:grid-cols-3">
                    @foreach ($plan->approvers->sortBy('level') as $approver)
                        @php
                            $status = match ($approver->status) {
                                'approve' => ['badge' => 'badge-success', 'border' => 'border-success/40', 'icon' => 'fas fa-check', 'label' => 'อนุมัติแล้ว'],
                                'reject' => ['badge' => 'badge-error', 'border' => 'border-error/40', 'icon' => 'fas fa-times', 'label' => 'ไม่อนุมัติ'],
                                default => ['badge' => 'badge-warning', 'border' => 'border-warning/40', 'icon' => 'fas fa-clock', 'label' => 'รออนุมัติ'],
                            };
                        @endphp
                        <article class="relative rounded-2xl border {{ $status['border'] }} p-5">
                            @if (! $loop->last)
                                <i class="fas fa-chevron-right text-base-content/20 absolute -right-3 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-base-100 p-1 lg:block"></i>
                            @endif
                            <div class="mb-4 flex items-center justify-between gap-2">
                                <span class="flex size-9 items-center justify-center rounded-full bg-primary font-bold text-primary-content">{{ $approver->level }}</span>
                                <span class="badge badge-soft {{ $status['badge'] }}">
                                    <i class="{{ $status['icon'] }} mr-1"></i>{{ $status['label'] }}
                                </span>
                            </div>
                            <div class="font-bold">{{ $approver->name ?: $approver->userid }}</div>
                            <div class="text-base-content/60 mt-1 text-sm">{{ $approver->position ?: '-' }}</div>
                            <div class="text-base-content/50 mt-3 flex items-center gap-2 text-xs">
                                <i class="fas fa-envelope w-4"></i><span class="truncate">{{ $approver->email ?: '-' }}</span>
                            </div>
                            <div class="text-base-content/50 mt-2 flex items-center gap-2 text-xs">
                                <i class="fas fa-calendar-check w-4"></i>
                                <span>{{ $approver->approved_at?->format('d/m/Y H:i') ?? 'ยังไม่มีเวลาบันทึก' }}</span>
                            </div>
                            @if ($approver->reason)
                                <div class="alert alert-error mt-4 py-2 text-xs">{{ $approver->reason }}</div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section>
            @php
                $plannedItems = $plan->items->reject->isOutOfPlan()->values();
                $outOfPlanItems = $plan->items->filter->isOutOfPlan()->values();
            @endphp
            <div class="mb-5 flex items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold">รายละเอียดหลักสูตร</h2>
                    <p class="text-base-content/60 mt-1 text-sm">หลักสูตรทั้งหมดภายใต้แผนของ {{ $plan->department }}</p>
                </div>
                <span class="badge badge-primary badge-lg">{{ $plan->items->count() }} รายการ</span>
            </div>

            <div class="mb-8 space-y-5">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-lg font-bold">หลักสูตรในแผนประจำปี</h3>
                    <span class="badge badge-ghost">{{ $plannedItems->count() }} รายการ</span>
                </div>
                @forelse ($plannedItems as $item)
                    @include('document.course.partials.item-card', [
                        'item' => $item,
                        'canEdit' => $canEdit,
                        'index' => $loop->iteration,
                    ])
                @empty
                    <p class="text-base-content/50 text-sm">ยังไม่มีหลักสูตรในแผนประจำปี</p>
                @endforelse
            </div>

            <div class="border-warning/30 space-y-5 rounded-3xl border bg-warning/5 p-5 md:p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-lg font-bold">หลักสูตรนอกแผน</h3>
                    <span class="badge badge-warning">จัดฝึกอบรมนอกแผนหลักสูตรประจำปี</span>
                    <span class="badge badge-ghost">{{ $outOfPlanItems->count() }} รายการ</span>
                </div>
                @forelse ($outOfPlanItems as $item)
                    @include('document.course.partials.item-card', [
                        'item' => $item,
                        'canEdit' => $canEdit,
                        'index' => $loop->iteration,
                    ])
                @empty
                    <p class="text-base-content/50 text-sm">ยังไม่มีหลักสูตรนอกแผน</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
