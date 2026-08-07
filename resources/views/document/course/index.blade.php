@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-6xl pb-10">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-primary text-3xl font-bold">แผนการฝึกในหน่วยงาน ประจำปี</h1>
                <p class="text-base-content/60 text-sm">ปี {{ $year }} · แผนกที่คุณดูได้: {{ implode(', ', $departments) }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form class="join" method="GET" action="{{ route('document.course') }}">
                    <input class="input input-bordered input-sm join-item w-28" type="number" name="year" value="{{ $year }}" min="2000" max="2100">
                    <button class="btn btn-sm btn-ghost join-item" type="submit">ดูปี</button>
                </form>
                @if ($canCreate)
                    <a class="btn btn-outline btn-primary btn-sm gap-2" href="{{ route('document.create.type', ['document_type' => 'training', 'year' => $year]) }}">
                        <i class="fas fa-chalkboard-teacher"></i> สร้างฝึกอบรมนอกแผน
                    </a>
                    <a class="btn btn-primary btn-sm gap-2" href="{{ route('document.course.create', ['year' => $year]) }}">
                        <i class="fas fa-plus"></i> สร้างแบบฟอร์ม
                    </a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error mb-4">{{ session('error') }}</div>
        @endif

        @if ($forms->isEmpty())
            <div class="card bg-base-100 border-base-200 border shadow-xl">
                <div class="card-body items-center py-16 text-center">
                    <i class="fas fa-book-open text-base-content/30 mb-4 text-5xl"></i>
                    <h2 class="text-xl font-bold">ยังไม่มีแบบฟอร์มในปี {{ $year }}</h2>
                    <p class="text-base-content/60 mb-6 text-sm">
                        ยังไม่มีแบบฟอร์มสำหรับแผนกที่คุณมีสิทธิ์:
                        {{ implode(', ', $departments) }}
                    </p>
                    @if ($canCreate)
                        <a class="btn btn-primary gap-2" href="{{ route('document.course.create', ['year' => $year]) }}">
                            <i class="fas fa-plus"></i> สร้างแบบฟอร์ม
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($forms->groupBy('department') as $department => $departmentForms)
                    @foreach ($departmentForms as $form)
                        @php
                            $statusClass = match ($form->status) {
                                'complete' => 'badge-success',
                                'not_approval' => 'badge-error',
                                'cancel' => 'badge-error',
                                default => 'badge-warning',
                            };
                        @endphp
                        <section class="card bg-base-100 border-base-200 border shadow-xl">
                            <div class="card-body gap-4">
                                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-base-200 pb-4">
                                    <div>
                                        <h2 class="text-lg font-bold">{{ $department }}</h2>
                                        <p class="text-base-content/60 text-sm">
                                            ปี {{ $form->year }}
                                            · {{ $form->items->count() }} หลักสูตร
                                            · สร้างโดย {{ $form->creator->name ?? $form->created_by }}
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="badge badge-soft {{ $statusClass }}">{{ $form->status }}</span>
                                        <a class="btn btn-ghost btn-sm" href="{{ route('document.course.show', $form) }}">ดูแบบฟอร์ม</a>
                                        @if (auth()->user()->canCreateCourseForDepartment($form->department))
                                            <a class="btn btn-outline btn-primary btn-sm gap-1"
                                                href="{{ route('document.create.type', ['document_type' => 'training', 'department' => $form->department, 'year' => $form->year]) }}">
                                                <i class="fas fa-chalkboard-teacher"></i> สร้างฝึกอบรมนอกแผน
                                            </a>
                                            <a class="btn btn-primary btn-sm" href="{{ route('document.course.edit', $form) }}">แก้ไข</a>
                                        @endif
                                    </div>
                                </div>

                                @php
                                    $plannedItems = $form->items->reject->isOutOfPlan()->values();
                                    $outOfPlanItems = $form->items->filter->isOutOfPlan()->values();
                                @endphp

                                @if ($form->items->isEmpty())
                                    <p class="text-base-content/50 text-sm">ยังไม่มีหลักสูตรในแบบฟอร์มนี้</p>
                                @else
                                    <div class="space-y-5">
                                        <div>
                                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                                <h3 class="text-sm font-bold">หลักสูตรในแผนประจำปี</h3>
                                                <span class="badge badge-ghost badge-sm">{{ $plannedItems->count() }} รายการ</span>
                                            </div>
                                            @include('document.course.partials.item-table', [
                                                'items' => $plannedItems,
                                                'form' => $form,
                                                'emptyMessage' => 'ยังไม่มีหลักสูตรในแผนประจำปี',
                                            ])
                                        </div>

                                        <div class="border-warning/30 rounded-2xl border bg-warning/5 p-4">
                                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                                <h3 class="text-sm font-bold">หลักสูตรนอกแผน</h3>
                                                <span class="badge badge-warning badge-sm">จัดฝึกอบรมนอกแผน</span>
                                                <span class="badge badge-ghost badge-sm">{{ $outOfPlanItems->count() }} รายการ</span>
                                            </div>
                                            @include('document.course.partials.item-table', [
                                                'items' => $outOfPlanItems,
                                                'form' => $form,
                                                'emptyMessage' => 'ยังไม่มีหลักสูตรนอกแผน',
                                            ])
                                        </div>
                                    </div>
                                @endif

                                <div class="border-base-200 mt-2 border-t pt-4">
                                    <div class="mb-3 text-xs font-bold uppercase tracking-wide opacity-50">ผู้อนุมัติ</div>
                                    <div class="grid gap-3 sm:grid-cols-3">
                                        @forelse ($form->approvers->sortBy('level') as $approver)
                                            @php
                                                $approverStatusClass = match ($approver->status) {
                                                    'approve' => 'badge-success',
                                                    'reject' => 'badge-error',
                                                    default => 'badge-warning',
                                                };
                                            @endphp
                                            <div class="bg-base-200/40 rounded-box flex flex-col gap-1 p-3">
                                                <div class="text-sm font-semibold">{{ $approver->name ?: $approver->userid }} <span class="badge badge-soft badge-xs {{ $approverStatusClass }}">{{ $approver->status }}</span></div>
                                                <div class="text-base-content/60 text-xs">{{ $approver->position ?: '-' }}</div>
                                                @if ($approver->approved_at)
                                                    <div class="text-base-content/40 text-xs">{{ $approver->approved_at->format('d/m/Y H:i') }}</div>
                                                @endif
                                            </div>
                                        @empty
                                            <p class="text-base-content/50 text-sm sm:col-span-3">ยังไม่มีผู้อนุมัติ</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </section>
                    @endforeach
                @endforeach
            </div>
        @endif
    </div>
@endsection
