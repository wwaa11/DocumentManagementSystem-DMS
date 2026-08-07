@extends('layouts.app')

@section('content')
    <div class="mx-8 pb-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-primary text-3xl font-bold">Course Create Permissions</h1>
                <p class="text-base-content/60 text-sm">กำหนดผู้ที่สามารถสร้างหลักสูตรการฝึกอบรม และแผนกที่สร้างได้</p>
            </div>
            <div class="bg-base-100 border-base-200 rounded-lg border p-3 shadow-sm">
                <form class="flex items-center gap-2" action="{{ route('admin.course-permissions') }}" method="GET">
                    <div class="join">
                        <input
                            class="input input-bordered input-sm join-item w-64 md:w-80"
                            type="text"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="ค้นหา User ID / ชื่อ เพื่อเพิ่มสิทธิ์..."
                        >
                        <button class="btn btn-primary btn-sm join-item" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    @if ($search)
                        <a class="btn btn-ghost btn-sm btn-circle" href="{{ route('admin.course-permissions') }}">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
                <p class="text-base-content/50 mt-2 text-xs">ค่าเริ่มต้นแสดงเฉพาะผู้ที่มีสิทธิ์สร้างหลักสูตรแล้ว</p>
            </div>
        </div>

        <div class="divider my-6"></div>

        @if (session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error mb-4">
                <ul class="list-disc pl-4 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($apiNotice['message'] ?? null))
            @php
                $noticeClass = match ($apiNotice['status'] ?? '') {
                    'imported' => 'alert-success',
                    default => 'alert-error',
                };
            @endphp
            <div class="alert {{ $noticeClass }} mb-4">
                <span>{{ $apiNotice['message'] }}</span>
            </div>
        @endif

        <div class="space-y-4" id="course-permission-list">
            @forelse ($users as $user)
                @php
                    $selectedDepartments = $user->courseDepartments();
                @endphp
                <form
                    class="course-permission-form card bg-base-100 border-base-200 border shadow-xl"
                    action="{{ route('admin.course-permissions.update') }}"
                    method="POST"
                    data-userid="{{ $user->userid }}"
                >
                    @csrf
                    <input type="hidden" name="userid" value="{{ $user->userid }}">
                    <input type="hidden" name="can_create_course" value="0">

                    <div class="card-body gap-5 p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="text-lg font-bold">{{ $user->name }}</div>
                                <div class="text-base-content/60 text-sm">
                                    {{ $user->userid }} · {{ $user->position }} · {{ $user->department }}
                                </div>
                                <span class="badge badge-outline mt-2">{{ $user->role }}</span>
                            </div>

                            <label class="label cursor-pointer justify-start gap-3 rounded-xl border border-base-200 px-4 py-3">
                                <input
                                    class="toggle toggle-primary course-can-create"
                                    type="checkbox"
                                    name="can_create_course"
                                    value="1"
                                    {{ $user->can_create_course ? 'checked' : '' }}
                                >
                                <span class="label-text font-medium">สร้างหลักสูตรได้</span>
                            </label>
                        </div>

                        <div class="department-picker {{ $user->can_create_course ? '' : 'pointer-events-none opacity-50' }}">
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                <div class="text-sm font-bold">แผนกที่สร้างหลักสูตรได้</div>
                                <div class="text-xs opacity-50">
                                    เลือกแล้ว <span class="selected-count font-semibold">{{ count($selectedDepartments) }}</span> แผนก
                                </div>
                            </div>

                            <div class="selected-tags mb-3 flex min-h-10 flex-wrap gap-2">
                                @forelse ($selectedDepartments as $department)
                                    <span class="badge badge-primary gap-2 py-3" data-department="{{ $department }}">
                                        {{ $department }}
                                        <button class="remove-tag" type="button" aria-label="ลบ {{ $department }}">&times;</button>
                                    </span>
                                @empty
                                    <span class="empty-selected text-xs italic opacity-40">ยังไม่ได้เลือกแผนก</span>
                                @endforelse
                            </div>

                            <div class="join mb-3 w-full max-w-xl">
                                <input
                                    class="input input-bordered input-sm join-item department-filter w-full"
                                    type="search"
                                    placeholder="พิมพ์เพื่อค้นหาแผนก..."
                                    {{ $user->can_create_course ? '' : 'disabled' }}
                                >
                                <button class="btn btn-sm btn-ghost join-item select-filtered" type="button" title="เลือกผลค้นหาทั้งหมด">
                                    เลือกที่ค้นหา
                                </button>
                                <button class="btn btn-sm btn-ghost join-item clear-all" type="button">ล้าง</button>
                            </div>

                            <div class="department-options max-h-56 overflow-y-auto rounded-xl border border-base-200 p-3">
                                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($departments as $department)
                                        <label class="department-option label cursor-pointer justify-start gap-2 rounded-lg border border-transparent px-2 py-1.5 hover:bg-base-200/60" data-label="{{ mb_strtolower($department) }}">
                                            <input
                                                class="checkbox checkbox-sm checkbox-primary department-checkbox"
                                                type="checkbox"
                                                name="course_departments[]"
                                                value="{{ $department }}"
                                                {{ in_array($department, $selectedDepartments, true) ? 'checked' : '' }}
                                                {{ $user->can_create_course ? '' : 'disabled' }}
                                            >
                                            <span class="label-text text-sm leading-snug">{{ $department }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="no-department-match hidden py-6 text-center text-sm italic opacity-40">ไม่พบแผนกตามคำค้นหา</div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-save mr-1"></i> บันทึกสิทธิ์
                            </button>
                        </div>
                    </div>
                </form>
            @empty
                <div class="card bg-base-100 border-base-200 border shadow-xl">
                    <div class="py-12 text-center italic opacity-50">
                        <i class="fas fa-user-slash mb-2 block text-4xl"></i>
                        ไม่พบผู้ใช้ — ค้นหา User ID เพื่อเพิ่มสิทธิ์
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.course-permission-form').forEach((form) => {
                const canCreate = form.querySelector('.course-can-create');
                const picker = form.querySelector('.department-picker');
                const filterInput = form.querySelector('.department-filter');
                const options = form.querySelectorAll('.department-option');
                const checkboxes = form.querySelectorAll('.department-checkbox');
                const selectedTags = form.querySelector('.selected-tags');
                const selectedCount = form.querySelector('.selected-count');
                const noMatch = form.querySelector('.no-department-match');

                const syncEnabled = () => {
                    const enabled = canCreate.checked;
                    picker.classList.toggle('pointer-events-none', !enabled);
                    picker.classList.toggle('opacity-50', !enabled);
                    filterInput.disabled = !enabled;
                    checkboxes.forEach((checkbox) => {
                        checkbox.disabled = !enabled;
                    });
                };

                const renderTags = () => {
                    const selected = Array.from(checkboxes)
                        .filter((checkbox) => checkbox.checked)
                        .map((checkbox) => checkbox.value);

                    selectedCount.textContent = String(selected.length);
                    selectedTags.innerHTML = '';

                    if (selected.length === 0) {
                        selectedTags.innerHTML = '<span class="empty-selected text-xs italic opacity-40">ยังไม่ได้เลือกแผนก</span>';
                        return;
                    }

                    selected.forEach((department) => {
                        const badge = document.createElement('span');
                        badge.className = 'badge badge-primary gap-2 py-3';
                        badge.dataset.department = department;
                        badge.innerHTML = `${department}<button class="remove-tag" type="button" aria-label="ลบ ${department}">&times;</button>`;
                        selectedTags.appendChild(badge);
                    });
                };

                const applyFilter = () => {
                    const keyword = filterInput.value.trim().toLowerCase();
                    let visible = 0;

                    options.forEach((option) => {
                        const match = !keyword || option.dataset.label.includes(keyword);
                        option.classList.toggle('hidden', !match);
                        if (match) {
                            visible += 1;
                        }
                    });

                    noMatch.classList.toggle('hidden', visible > 0);
                };

                canCreate.addEventListener('change', syncEnabled);

                filterInput.addEventListener('input', applyFilter);

                form.querySelector('.select-filtered').addEventListener('click', () => {
                    options.forEach((option) => {
                        if (!option.classList.contains('hidden')) {
                            const checkbox = option.querySelector('.department-checkbox');
                            if (!checkbox.disabled) {
                                checkbox.checked = true;
                            }
                        }
                    });
                    renderTags();
                });

                form.querySelector('.clear-all').addEventListener('click', () => {
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = false;
                    });
                    renderTags();
                });

                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', renderTags);
                });

                selectedTags.addEventListener('click', (event) => {
                    const button = event.target.closest('.remove-tag');
                    if (!button) {
                        return;
                    }

                    const badge = button.closest('[data-department]');
                    const department = badge?.dataset.department;
                    const checkbox = Array.from(checkboxes).find((item) => item.value === department);
                    if (checkbox) {
                        checkbox.checked = false;
                        renderTags();
                    }
                });

                syncEnabled();
                renderTags();
            });
        });
    </script>
@endpush
