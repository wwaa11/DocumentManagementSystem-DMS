@props([
    'departments',
    'selectedDepartments' => [],
    'checkboxName',
    'enabled' => true,
    'title',
    'compact' => false,
])

<div @class(['department-picker', 'pointer-events-none opacity-50' => ! $enabled])>
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
        <div @class(['font-bold', 'text-sm' => ! $compact, 'text-xs' => $compact])>{{ $title }}</div>
        <div class="text-base-content/50 text-xs">
            เลือกแล้ว <span class="selected-count font-semibold">{{ count($selectedDepartments) }}</span> แผนก
        </div>
    </div>

    <div @class(['selected-tags flex flex-wrap gap-1', 'mb-2 min-h-6' => $compact, 'mb-3 min-h-10 gap-2' => ! $compact])>
        @forelse ($selectedDepartments as $department)
            <span @class(['badge badge-primary gap-1', 'badge-xs py-2' => $compact, 'gap-2 py-3' => ! $compact]) data-department="{{ $department }}">
                {{ $department }}
                <button class="remove-tag" type="button" aria-label="ลบ {{ $department }}">&times;</button>
            </span>
        @empty
            <span class="empty-selected text-base-content/40 text-xs italic">ยังไม่ได้เลือกแผนก</span>
        @endforelse
    </div>

    <div class="join mb-2 w-full">
        <input
            class="input input-bordered input-xs join-item department-filter w-full"
            type="search"
            placeholder="ค้นหาแผนก..."
            @disabled(! $enabled)
        >
        <button class="btn btn-xs btn-ghost join-item select-filtered" type="button">เลือก</button>
        <button class="btn btn-xs btn-ghost join-item clear-all" type="button">ล้าง</button>
    </div>

    <div @class(['department-options border-base-200 overflow-y-auto rounded-lg border p-2', 'max-h-36' => $compact, 'max-h-56 p-3 rounded-xl' => ! $compact])>
        <div @class(['grid gap-1', 'grid-cols-1 sm:grid-cols-2' => $compact, 'gap-2 sm:grid-cols-2 lg:grid-cols-3' => ! $compact])>
            @foreach ($departments as $department)
                <label
                    class="department-option label hover:bg-base-200/60 cursor-pointer justify-start gap-2 rounded-md border border-transparent px-1.5 py-1"
                    data-label="{{ mb_strtolower($department) }}"
                >
                    <input
                        class="checkbox checkbox-xs checkbox-primary department-checkbox"
                        type="checkbox"
                        name="{{ $checkboxName }}[]"
                        value="{{ $department }}"
                        data-department-value="{{ $department }}"
                        @checked(in_array($department, $selectedDepartments, true))
                        @disabled(! $enabled)
                    >
                    <span class="label-text text-xs leading-snug">{{ $department }}</span>
                </label>
            @endforeach
        </div>
        <div class="no-department-match hidden py-4 text-center text-xs italic opacity-40">ไม่พบแผนก</div>
    </div>
</div>
