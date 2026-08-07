<tr class="hover {{ $item->isOutOfPlan() ? 'bg-warning/5' : '' }}">
    <td class="font-semibold">{{ $item->number }}</td>
    <td>
        <div class="flex flex-wrap items-center gap-2">
            <div class="font-medium">{{ $item->name }}</div>
            @if ($item->isOutOfPlan())
                <span class="badge badge-warning badge-sm">นอกแผน</span>
            @endif
        </div>
        <div class="text-base-content/50 mt-1 line-clamp-2 text-xs">{{ $item->objective }}</div>
    </td>
    <td>
        <span class="badge badge-outline badge-sm">{{ $item->trainingTypeLabel() }}</span>
    </td>
    <td class="text-sm">{{ implode(', ', $item->scheduleMonthLabels()) }}</td>
    <td class="text-sm">
        {{ $item->estimated_cost !== null ? number_format((float) $item->estimated_cost, 2) : '-' }}
    </td>
    <td class="text-sm">
        {{ $item->instructors->pluck('name')->filter()->implode(', ') ?: '-' }}
    </td>
    <td class="text-sm">
        {{ $item->responsibles->pluck('name')->filter()->implode(', ') ?: '-' }}
    </td>
    <td>
        <div class="flex flex-col items-start gap-1">
            @if ($item->hasTrainingDocument())
                @php $training = $item->trainings->first(); @endphp
                <a class="btn btn-secondary btn-sm"
                    href="{{ route('document.type.view', ['document_type' => 'Training', 'document_id' => $training->id]) }}">
                    จัดการฝึกอบรม
                </a>
            @elseif (auth()->user()->canCreateCourseForDepartment($form->department))
                <a class="btn btn-primary btn-xs"
                    href="{{ route('document.create.type', ['document_type' => 'training', 'course_plan_item_id' => $item->id]) }}">
                    สร้างฝึกอบรม
                </a>
            @else
                <span class="text-base-content/40 text-xs">ยังไม่มีใบ</span>
            @endif
        </div>
    </td>
</tr>
