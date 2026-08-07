<article class="card bg-base-100 overflow-hidden border shadow-xl {{ $item->isOutOfPlan() ? 'border-warning/40' : 'border-base-200' }}">
    <div class="border-base-200 flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4 md:px-6 {{ $item->isOutOfPlan() ? 'bg-warning/10' : 'bg-base-200/60' }}">
        <div class="flex items-center gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl font-bold {{ $item->isOutOfPlan() ? 'bg-warning text-warning-content' : 'bg-primary text-primary-content' }}">{{ $item->number }}</span>
            <div>
                <div class="text-base-content/50 flex flex-wrap items-center gap-2 text-xs">
                    <span>หลักสูตรรายการที่ {{ $index }}</span>
                    @if ($item->isOutOfPlan())
                        <span class="badge badge-warning badge-sm">นอกแผน</span>
                    @endif
                </div>
                <h3 class="font-bold">{{ $item->name }}</h3>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="badge badge-outline">{{ $item->trainingTypeLabel() }}</span>
            @foreach ($item->scheduleMonthLabels() as $month)
                <span class="badge badge-ghost">{{ $month }}</span>
            @endforeach
            @if ($item->hasTrainingDocument())
                @php $existingTraining = $item->trainings->first(); @endphp
                <a class="btn btn-outline btn-sm min-h-10 cursor-pointer"
                    href="{{ route('document.type.view', ['document_type' => 'Training', 'document_id' => $existingTraining->id]) }}">
                    <i class="fas fa-external-link-alt"></i> เปิดใบบันทึก
                </a>
            @elseif ($canEdit)
                <a class="btn btn-primary btn-sm min-h-10 cursor-pointer"
                    href="{{ route('document.create.type', ['document_type' => 'training', 'course_plan_item_id' => $item->id]) }}">
                    <i class="fas fa-plus"></i> สร้างฝึกอบรม
                </a>
            @endif
        </div>
    </div>
    <div class="card-body gap-6 p-5 md:p-6">
        <div class="grid gap-5 lg:grid-cols-2">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm font-bold"><span class="size-2 rounded-full bg-primary"></span>ที่มาหลักสูตร</div>
                <p class="text-base-content/70 whitespace-pre-line text-sm leading-relaxed">{{ $item->origin }}</p>
            </div>
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm font-bold"><span class="size-2 rounded-full bg-primary"></span>วัตถุประสงค์</div>
                <p class="text-base-content/70 whitespace-pre-line text-sm leading-relaxed">{{ $item->objective }}</p>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-base-200/50 rounded-xl p-4">
                <div class="text-base-content/50 text-xs">ประเภท</div>
                <div class="mt-1 text-sm font-bold">{{ $item->trainingTypeLabel() }}</div>
            </div>
            <div class="bg-base-200/50 rounded-xl p-4">
                <div class="text-base-content/50 text-xs">กำหนดการ</div>
                <div class="mt-1 text-sm font-bold">{{ implode(', ', $item->scheduleMonthLabels()) }}</div>
            </div>
            <div class="bg-base-200/50 rounded-xl p-4">
                <div class="text-base-content/50 text-xs">งบประมาณ</div>
                <div class="mt-1 text-sm font-bold">{{ $item->estimated_cost !== null ? number_format((float) $item->estimated_cost, 2).' บาท' : '-' }}</div>
            </div>
            <div class="bg-base-200/50 rounded-xl p-4">
                <div class="text-base-content/50 text-xs">กลุ่มเป้าหมาย</div>
                <div class="mt-1 text-sm font-bold">{{ $item->targetPositions->count() }} ตำแหน่ง</div>
            </div>
        </div>
        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-xl border border-base-200 p-4">
                <div class="mb-3 flex items-center gap-2 text-sm font-bold"><i class="fas fa-chalkboard-teacher text-primary"></i>วิทยากร</div>
                <ul class="space-y-2 text-sm">
                    @foreach ($item->instructors as $instructor)
                        <li>
                            <div class="font-medium">{{ $instructor->name }}</div>
                            <div class="text-base-content/50 text-xs">{{ $instructor->position }} · {{ $instructor->userid }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-xl border border-base-200 p-4">
                <div class="mb-3 flex items-center gap-2 text-sm font-bold"><i class="fas fa-users text-primary"></i>กลุ่มเป้าหมาย</div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($item->targetPositions as $target)
                        <span class="badge badge-ghost">{{ $target->position }}</span>
                    @endforeach
                </div>
            </div>
            <div class="rounded-xl border border-base-200 p-4">
                <div class="mb-3 flex items-center gap-2 text-sm font-bold"><i class="fas fa-user-shield text-primary"></i>ผู้รับผิดชอบ</div>
                <ul class="space-y-2 text-sm">
                    @foreach ($item->responsibles as $responsible)
                        <li>
                            <div class="font-medium">{{ $responsible->name }}</div>
                            <div class="text-base-content/50 text-xs">{{ $responsible->position }} · {{ $responsible->userid }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="border-base-200 rounded-xl border p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2 text-sm font-bold">
                    <i class="fas fa-file-signature text-primary"></i>
                    ใบบันทึกฝึกอบรมที่ผูกกับหลักสูตรนี้
                </div>
                <span class="badge badge-ghost">{{ $item->hasTrainingDocument() ? 'มีแล้ว' : 'ยังไม่มี' }}</span>
            </div>
            @if (! $item->hasTrainingDocument())
                <p class="text-base-content/50 text-sm">ยังไม่มีใบบันทึกฝึกอบรมสำหรับหลักสูตรนี้ (สร้างได้ 1 ใบต่อหลักสูตร)</p>
            @else
                @php $training = $item->trainings->first(); @endphp
                <div class="bg-base-200/40 flex flex-wrap items-center justify-between gap-2 rounded-lg px-3 py-2">
                    <div>
                        <div class="text-sm font-semibold">{{ $training->title }}</div>
                        <div class="text-base-content/50 text-xs">#{{ $training->id }} · {{ $training->status }} · {{ $training->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <a class="btn btn-primary btn-sm min-h-10 cursor-pointer"
                        href="{{ route('document.type.view', ['document_type' => 'Training', 'document_id' => $training->id]) }}">
                        <i class="fas fa-external-link-alt"></i> เปิดใบบันทึก
                    </a>
                </div>
            @endif
        </div>
    </div>
</article>
