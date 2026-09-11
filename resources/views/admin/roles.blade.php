@extends("layouts.app")

@section("content")
    <div class="mx-8 pb-10">
        <section class="page-hero mb-4 p-4 sm:p-5">
            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-primary/70 mb-0.5 text-xs font-semibold tracking-wide uppercase">Admin</p>
                    <h1 class="text-primary text-xl font-bold tracking-tight sm:text-2xl">User Roles & Permissions</h1>
                    @if ($scoped ?? false)
                        <p class="text-warning mt-1 text-xs">จัดการได้เฉพาะกลุ่มงานของคุณ</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="badge badge-ghost badge-sm">{{ $stats["total"] ?? 0 }} users</span>
                    <span class="badge badge-primary badge-soft badge-sm">{{ $stats["roles"] ?? 0 }} roles</span>
                    @if ($canManageExtendedPermissions ?? false)
                        <span class="badge badge-secondary badge-soft badge-sm">{{ $stats["course"] ?? 0 }} course</span>
                        <span class="badge badge-accent badge-soft badge-sm">{{ $stats["document"] ?? 0 }} doc</span>
                    @endif
                </div>
            </div>
        </section>

        <div class="filter-panel mb-4">
            @php
                $activeFilter = $filter ?? "all";
                $listQuery = array_filter([
                    "search" => $search ?? null,
                    "role" => $roleFilter ?? null,
                ], fn ($value): bool => filled($value));
            @endphp
            <form class="flex flex-col gap-3 lg:flex-row lg:items-center" action="{{ route("roles.list") }}" method="GET">
                <input type="hidden" name="filter" value="{{ $activeFilter }}">
                <label class="input input-bordered input-sm flex w-full items-center gap-2 lg:max-w-md">
                    <i class="fas fa-search text-base-content/40 text-xs"></i>
                    <input class="grow" type="search" name="search" value="{{ $search ?? "" }}" placeholder="ค้นหา User ID / ชื่อ...">
                </label>
                <select class="select select-bordered select-sm w-full lg:w-52" name="role">
                    <option value="">ทุกบทบาท</option>
                    @if ($canSetUser ?? true)
                        <option value="user" @selected(($roleFilter ?? "") === "user")>User</option>
                    @endif
                    @foreach ($roles as $roleKey => $roleLabel)
                        <option value="{{ $roleKey }}" @selected(($roleFilter ?? "") === $roleKey)>{{ $roleLabel }}</option>
                    @endforeach
                </select>
                <div role="tablist" class="tabs tabs-box tabs-xs">
                    @foreach (["all" => "ทั้งหมด", "roles" => "บทบาท", "course" => "หลักสูตร", "document" => "เอกสาร"] as $key => $label)
                        @if (($canManageExtendedPermissions ?? false) || in_array($key, ["all", "roles"], true))
                            <a
                                class="tab {{ $activeFilter === $key ? "tab-active" : "" }}"
                                href="{{ route("roles.list", array_merge($listQuery, $key === "all" ? [] : ["filter" => $key])) }}"
                            >{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm" type="submit">ค้นหา</button>
                    @if ($search || ($filter ?? "all") !== "all" || filled($roleFilter ?? null))
                        <a class="btn btn-ghost btn-sm" href="{{ route("roles.list") }}">ล้าง</a>
                    @endif
                </div>
            </form>
        </div>

        @if (! empty($apiNotice["message"] ?? null))
            @php
                $noticeClass = match ($apiNotice["status"] ?? "") {
                    "imported" => "alert-success",
                    "out_of_scope" => "alert-warning",
                    default => "alert-error",
                };
            @endphp
            <div role="alert" class="alert {{ $noticeClass }} alert-soft mb-4 py-2 text-sm">
                <span>{{ $apiNotice["message"] }}</span>
            </div>
        @endif

        <div class="data-table-wrap">
            <div class="border-base-200 flex items-center justify-between border-b px-4 py-2 text-xs opacity-60">
                <span>แสดง {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} จาก {{ $users->total() }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-sm table-pin-rows">
                    <thead>
                        <tr>
                            <th class="pl-4">ผู้ใช้</th>
                            <th>บทบาทระบบ</th>
                            @if ($canManageExtendedPermissions ?? false)
                                <th>หลักสูตร</th>
                                <th class="pr-4">เอกสารแผนก</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $columnCount = ($canManageExtendedPermissions ?? false) ? 4 : 2;
                        @endphp
                        @forelse ($groupedUsers as $roleGroup)
                            @if ((count($groupedUsers) > 1 || in_array($filter ?? 'all', ['all', 'roles'], true)) && ! filled($roleFilter ?? null))
                                <tr class="bg-base-200/70">
                                    <td class="pl-4 py-2 font-semibold" colspan="{{ $columnCount }}">
                                        <div class="flex items-center gap-2 text-xs tracking-wide uppercase">
                                            <i class="fas fa-user-tag text-primary/70"></i>
                                            <span>{{ $roleGroup['label'] }}</span>
                                            <span class="badge badge-ghost badge-xs">{{ count($roleGroup['users']) }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                            @foreach ($roleGroup['users'] as $user)
                                @php
                                    $courseDepartments = $user->courseDepartments();
                                    $viewDepartments = $user->viewDepartments();
                                @endphp
                                <tr class="hover:bg-base-200/40">
                                    <td class="pl-4 align-top">
                                        <div class="font-semibold">{{ $user->name }}</div>
                                        <div class="text-base-content/55 text-xs">{{ $user->userid }} · {{ $user->department }}</div>
                                    </td>
                                    <td class="align-top">
                                        <form class="role-update-form flex min-w-56 flex-col gap-2 sm:flex-row sm:items-center" action="{{ route("roles.update") }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="userid" value="{{ $user->userid }}">
                                            <input type="hidden" name="username" value="{{ $user->name }}">
                                            <select class="select select-bordered select-xs w-full min-w-40" name="role">
                                                @if ($canSetUser ?? true)
                                                    <option value="user" @selected($user->role == "user")>User</option>
                                                @endif
                                                @foreach ($roles as $role => $label)
                                                    <option value="{{ $role }}" @selected($user->role == $role || ($role === "admin" && $user->role === "dev"))>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-primary btn-xs update-btn shrink-0" type="button">บันทึก</button>
                                        </form>
                                    </td>
                                    @if ($canManageExtendedPermissions ?? false)
                                        <td class="align-top">
                                            <div class="flex flex-col gap-1">
                                                <form class="flex items-center gap-2" action="{{ route("admin.course-permissions.update") }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="userid" value="{{ $user->userid }}">
                                                    <input type="hidden" name="can_create_course" value="0">
                                                    @foreach ($courseDepartments as $department)
                                                        <input type="hidden" name="course_departments[]" value="{{ $department }}">
                                                    @endforeach
                                                    <input class="toggle toggle-secondary toggle-xs course-quick-toggle" type="checkbox" name="can_create_course" value="1" @checked($user->can_create_course)>
                                                    <span class="text-xs">{{ $user->can_create_course ? count($courseDepartments)." แผนก" : "ปิด" }}</span>
                                                </form>
                                                <button
                                                    class="btn btn-ghost btn-xs w-fit edit-dept-btn"
                                                    type="button"
                                                    data-type="course"
                                                    data-userid="{{ $user->userid }}"
                                                    data-username="{{ $user->name }}"
                                                    data-enabled="{{ $user->can_create_course ? "1" : "0" }}"
                                                    data-selected='@json($courseDepartments)'
                                                >
                                                    จัดการแผนก
                                                </button>
                                            </div>
                                        </td>
                                        <td class="pr-4 align-top">
                                            <div class="flex flex-col gap-1">
                                                <form class="flex items-center gap-2" action="{{ route("admin.document-view-permissions.update") }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="userid" value="{{ $user->userid }}">
                                                    <input type="hidden" name="can_view_department_documents" value="0">
                                                    @foreach ($viewDepartments as $department)
                                                        <input type="hidden" name="view_departments[]" value="{{ $department }}">
                                                    @endforeach
                                                    <input class="toggle toggle-accent toggle-xs view-quick-toggle" type="checkbox" name="can_view_department_documents" value="1" @checked($user->can_view_department_documents)>
                                                    <span class="text-xs">{{ $user->can_view_department_documents ? count($viewDepartments)." แผนก" : "ปิด" }}</span>
                                                </form>
                                                <button
                                                    class="btn btn-ghost btn-xs w-fit edit-dept-btn"
                                                    type="button"
                                                    data-type="document"
                                                    data-userid="{{ $user->userid }}"
                                                    data-username="{{ $user->name }}"
                                                    data-enabled="{{ $user->can_view_department_documents ? "1" : "0" }}"
                                                    data-selected='@json($viewDepartments)'
                                                >
                                                    จัดการแผนก
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td class="py-12 text-center italic opacity-50" colspan="{{ $columnCount }}">
                                    ไม่พบผู้ใช้ — ค้นหา User ID เพื่อเพิ่มสิทธิ์
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-base-200 border-t px-4 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        @if ($canManageExtendedPermissions ?? false)
            <dialog class="modal" id="deptPermissionModal">
                <div class="modal-box max-w-2xl p-4 sm:p-5">
                    <h3 class="text-base font-bold" id="deptModalTitle">จัดการแผนก</h3>
                    <p class="text-base-content/55 mt-1 text-xs" id="deptModalSubtitle"></p>
                    <form class="mt-4 space-y-3" id="deptModalForm" method="POST">
                        @csrf
                        <input type="hidden" name="userid" id="deptModalUserid">
                        <input type="hidden" name="can_create_course" value="0" id="deptModalCourseOff">
                        <input type="hidden" name="can_view_department_documents" value="0" id="deptModalViewOff">
                        <label class="label border-base-200 cursor-pointer justify-start gap-2 rounded-lg border px-3 py-2">
                            <input class="toggle toggle-sm course-modal-toggle" type="checkbox" name="can_create_course" value="1" id="deptModalCourseOn">
                            <input class="toggle toggle-sm view-modal-toggle hidden" type="checkbox" name="can_view_department_documents" value="1" id="deptModalViewOn">
                            <span class="label-text text-sm" id="deptModalToggleLabel"></span>
                        </label>
                        @include("admin.partials.department-picker", [
                            "departments" => $departments,
                            "selectedDepartments" => [],
                            "checkboxName" => "modal_departments",
                            "enabled" => true,
                            "title" => "เลือกแผนก",
                            "compact" => true,
                        ])
                        <div class="modal-action mt-2">
                            <button class="btn btn-ghost btn-sm" type="button" id="deptModalClose">ยกเลิก</button>
                            <button class="btn btn-primary btn-sm" type="submit">บันทึก</button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        @endif
    </div>
@endsection

@push("scripts")
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.update-btn').forEach((btn) => {
                btn.addEventListener('click', function() {
                    const form = this.closest('form');
                    const username = form.querySelector('input[name="username"]').value;
                    const roleSelect = form.querySelector('select[name="role"]');
                    const roleName = roleSelect.options[roleSelect.selectedIndex].text;

                    Swal.fire({
                        title: 'ยืนยันการเปลี่ยนสิทธิ์?',
                        html: `เปลี่ยนสิทธิ์ <b>${username}</b> เป็น <b>${roleName}</b>`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'ยืนยัน',
                        cancelButtonText: 'ยกเลิก',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-primary btn-sm px-4 mx-1',
                            cancelButton: 'btn btn-ghost btn-sm px-4 mx-1'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            document.querySelectorAll('.course-quick-toggle, .view-quick-toggle').forEach((toggle) => {
                toggle.addEventListener('change', function() {
                    if (this.checked) {
                        this.checked = false;
                        this.closest('td')?.querySelector('.edit-dept-btn')?.click();
                        return;
                    }
                    this.closest('form')?.submit();
                });
            });

            const modal = document.getElementById('deptPermissionModal');
            if (!modal) {
                return;
            }

            const form = document.getElementById('deptModalForm');
            const picker = form.querySelector('.department-picker');
            const checkboxes = picker.querySelectorAll('.department-checkbox');
            const courseOn = document.getElementById('deptModalCourseOn');
            const viewOn = document.getElementById('deptModalViewOn');
            let currentType = 'course';

            const activeToggle = () => currentType === 'course' ? courseOn : viewOn;

            const syncCheckboxNames = () => {
                const name = currentType === 'course' ? 'course_departments[]' : 'view_departments[]';
                checkboxes.forEach((checkbox) => {
                    checkbox.name = name;
                });
            };

            const setSelected = (selected) => {
                const selectedSet = new Set(selected || []);
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = selectedSet.has(checkbox.value);
                });
            };

            const initPicker = () => {
                const filterInput = picker.querySelector('.department-filter');
                const options = picker.querySelectorAll('.department-option');
                const selectedTags = picker.querySelector('.selected-tags');
                const selectedCount = picker.querySelector('.selected-count');
                const noMatch = picker.querySelector('.no-department-match');

                const renderTags = () => {
                    const selected = Array.from(checkboxes).filter((cb) => cb.checked).map((cb) => cb.value);
                    selectedCount.textContent = String(selected.length);
                    selectedTags.innerHTML = selected.length
                        ? selected.map((d) => `<span class="badge badge-primary badge-xs gap-1 py-2" data-department="${d}">${d}<button class="remove-tag" type="button">&times;</button></span>`).join('')
                        : '<span class="empty-selected text-base-content/40 text-xs italic">ยังไม่ได้เลือกแผนก</span>';
                };

                const syncEnabled = () => {
                    const enabled = activeToggle().checked;
                    picker.classList.toggle('pointer-events-none', !enabled);
                    picker.classList.toggle('opacity-50', !enabled);
                    filterInput.disabled = !enabled;
                    checkboxes.forEach((cb) => { cb.disabled = !enabled; });
                };

                courseOn.addEventListener('change', syncEnabled);
                viewOn.addEventListener('change', syncEnabled);
                filterInput.addEventListener('input', () => {
                    const keyword = filterInput.value.trim().toLowerCase();
                    let visible = 0;
                    options.forEach((option) => {
                        const match = !keyword || option.dataset.label.includes(keyword);
                        option.classList.toggle('hidden', !match);
                        if (match) visible += 1;
                    });
                    noMatch.classList.toggle('hidden', visible > 0);
                });
                picker.querySelector('.select-filtered').addEventListener('click', () => {
                    options.forEach((option) => {
                        if (!option.classList.contains('hidden')) {
                            option.querySelector('.department-checkbox').checked = true;
                        }
                    });
                    renderTags();
                });
                picker.querySelector('.clear-all').addEventListener('click', () => {
                    checkboxes.forEach((cb) => { cb.checked = false; });
                    renderTags();
                });
                checkboxes.forEach((cb) => cb.addEventListener('change', renderTags));
                selectedTags.addEventListener('click', (event) => {
                    const button = event.target.closest('.remove-tag');
                    if (!button) return;
                    const department = button.closest('[data-department]')?.dataset.department;
                    const checkbox = Array.from(checkboxes).find((cb) => cb.value === department);
                    if (checkbox) {
                        checkbox.checked = false;
                        renderTags();
                    }
                });

                syncEnabled();
                renderTags();

                return { renderTags, syncEnabled };
            };

            const pickerUi = initPicker();

            document.querySelectorAll('.edit-dept-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    currentType = button.dataset.type;
                    const selected = JSON.parse(button.dataset.selected || '[]');
                    const enabled = button.dataset.enabled === '1';

                    document.getElementById('deptModalTitle').textContent = currentType === 'course'
                        ? 'สิทธิ์สร้างหลักสูตร'
                        : 'สิทธิ์ดูเอกสารแผนก';
                    document.getElementById('deptModalSubtitle').textContent = `${button.dataset.username} (${button.dataset.userid})`;
                    document.getElementById('deptModalUserid').value = button.dataset.userid;
                    document.getElementById('deptModalToggleLabel').textContent = currentType === 'course'
                        ? 'อนุญาตให้สร้างหลักสูตร'
                        : 'อนุญาตให้ดูเอกสารแผนก';

                    form.action = currentType === 'course'
                        ? @json(route('admin.course-permissions.update'))
                        : @json(route('admin.document-view-permissions.update'));

                    courseOn.classList.toggle('hidden', currentType !== 'course');
                    viewOn.classList.toggle('hidden', currentType !== 'document');
                    courseOn.disabled = currentType !== 'course';
                    viewOn.disabled = currentType !== 'document';
                    document.getElementById('deptModalCourseOff').disabled = currentType !== 'course';
                    document.getElementById('deptModalViewOff').disabled = currentType !== 'document';

                    activeToggle().checked = enabled || selected.length > 0;

                    syncCheckboxNames();
                    setSelected(selected);
                    picker.querySelector('.department-filter').value = '';
                    picker.querySelectorAll('.department-option').forEach((o) => o.classList.remove('hidden'));
                    picker.querySelector('.no-department-match')?.classList.add('hidden');
                    pickerUi.renderTags();
                    pickerUi.syncEnabled();
                    modal.showModal();
                });
            });

            document.getElementById('deptModalClose')?.addEventListener('click', () => modal.close());
        });
    </script>
@endpush
