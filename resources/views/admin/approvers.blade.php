@extends("layouts.app")

@php
    $totalCount = $noti["count"] ?? $datas->count();
    $missingCount = $noti["error"] ?? 0;
    $assignedCount = $noti["assigned"] ?? max($totalCount - $missingCount, 0);
    $coverage = $totalCount > 0 ? (int) round(($assignedCount / $totalCount) * 100) : 100;
    $defaultFilter = $missingCount > 0 ? "missing" : "all";
@endphp

@section("content")
    <div class="mx-8 pb-10">
        <section class="page-hero mb-6">
            <div class="pointer-events-none absolute -top-10 -right-8 h-32 w-32 rounded-full bg-accent/10 blur-2xl"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-xl">
                    <p class="text-primary/70 mb-1 text-xs font-semibold tracking-wide uppercase">Admin</p>
                    <h1 class="text-primary text-2xl font-bold tracking-tight sm:text-3xl">Department Approvers</h1>
                    <p class="text-base-content/60 mt-1 text-sm">กำหนดผู้อนุมัติเอกสารระดับแผนก — แผนกที่ยังไม่มีผู้อนุมัติจะแสดงก่อน</p>
                    <div class="mt-4 max-w-md">
                        <div class="mb-1.5 flex items-center justify-between text-xs">
                            <span class="text-base-content/55">ความครบของผู้อนุมัติ</span>
                            <span class="font-semibold">{{ $coverage }}%</span>
                        </div>
                        <progress class="progress h-2 w-full" value="{{ $coverage }}" max="100"></progress>
                    </div>
                </div>

                <div class="stats stats-horizontal bg-base-100/80 border-base-200 w-full overflow-x-auto border shadow-sm lg:w-auto">
                    <div class="stat py-3">
                        <div class="stat-title text-xs">ทั้งหมด</div>
                        <div class="stat-value text-2xl">{{ $totalCount }}</div>
                    </div>
                    <div class="stat py-3">
                        <div class="stat-title text-xs">มีผู้อนุมัติ</div>
                        <div class="stat-value text-success text-2xl">{{ $assignedCount }}</div>
                    </div>
                    <div class="stat py-3">
                        <div class="stat-title text-xs">ยังไม่มี</div>
                        <div class="stat-value text-error text-2xl">{{ $missingCount }}</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <div class="card card-border bg-base-100 lg:sticky lg:top-6">
                    <div class="border-base-200 border-b px-6 py-4">
                        <h2 class="card-title text-base" id="formTitle">กำหนดผู้อนุมัติ</h2>
                        <p class="text-base-content/50 text-xs" id="formHint">เลือกแผนกจากรายการด้านขวา หรือค้นหาชื่อแผนก</p>
                    </div>
                    <form class="card-body gap-4 p-6" id="updateForm" action="{{ route("approvers.update") }}" method="POST">
                        @csrf
                        @if ($errors->any())
                            <div role="alert" class="alert alert-error alert-soft py-2 text-xs">
                                <ul class="list-disc pl-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">แผนก</legend>
                            <input class="input input-bordered w-full font-semibold" id="form_dept" type="text" name="department" value="{{ old("department") }}" placeholder="ยังไม่ได้เลือกแผนก" readonly>
                            <p class="label">ค้นหาหรือคลิกแผนกจากตารางเพื่อแก้ไข</p>
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">User ID</legend>
                            <div class="join w-full">
                                <input class="input input-bordered join-item w-full" id="form_userid" type="text" name="userid" value="{{ old("userid") }}" placeholder="รหัสพนักงาน">
                                <button class="btn join-item" id="lookupUserBtn" type="button" onclick="getUserData(this)" title="ดึงข้อมูลจาก Staff">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <p class="label">กรอก User ID แล้วกดปุ่มเพื่อดึงชื่อ ตำแหน่ง และอีเมล</p>
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">ชื่อ-นามสกุล</legend>
                            <input class="input input-bordered w-full" id="form_name" type="text" name="name" value="{{ old("name") }}">
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">ตำแหน่ง</legend>
                            <input class="input input-bordered w-full" id="form_position" type="text" name="position" value="{{ old("position") }}">
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">อีเมล</legend>
                            <input class="input input-bordered w-full" id="form_email" type="text" name="email" value="{{ old("email") }}">
                        </fieldset>

                        <div class="card-actions mt-2">
                            <button class="btn btn-primary w-full" id="submitBtn" type="submit" @disabled(! old("department"))>
                                <i class="fas fa-save"></i>
                                <span id="submitLabel">บันทึกผู้อนุมัติ</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="card card-border bg-base-100">
                    <div class="border-base-200 flex flex-col gap-3 border-b px-4 py-4 sm:px-6">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div role="tablist" class="tabs tabs-box tabs-sm w-full lg:w-auto">
                                <button class="tab {{ $defaultFilter === "missing" ? "tab-active" : "" }}" type="button" role="tab" data-filter="missing" id="tab-missing">
                                    ยังไม่มีผู้อนุมัติ
                                    <span class="badge badge-error badge-soft badge-xs ml-1">{{ $missingCount }}</span>
                                </button>
                                <button class="tab {{ $defaultFilter === "assigned" ? "tab-active" : "" }}" type="button" role="tab" data-filter="assigned" id="tab-assigned">
                                    มีผู้อนุมัติแล้ว
                                    <span class="badge badge-success badge-soft badge-xs ml-1">{{ $assignedCount }}</span>
                                </button>
                                <button class="tab {{ $defaultFilter === "all" ? "tab-active" : "" }}" type="button" role="tab" data-filter="all" id="tab-all">
                                    ทั้งหมด
                                    <span class="badge badge-ghost badge-xs ml-1">{{ $totalCount }}</span>
                                </button>
                            </div>

                            <label class="input input-bordered input-sm flex w-full items-center gap-2 lg:max-w-xs">
                                <i class="fas fa-search text-base-content/40 text-xs"></i>
                                <input class="grow" id="deptSearch" type="search" list="dept-suggestions" placeholder="ค้นหาแผนก..." autocomplete="off">
                            </label>
                            <datalist id="dept-suggestions">
                                @foreach ($depts as $item)
                                    <option value="{{ $item }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-base-content/50 text-xs">คลิกแถวเพื่อกำหนดหรือแก้ไขผู้อนุมัติ</p>
                            <span class="badge badge-ghost badge-sm" id="visibleCount">{{ $defaultFilter === "missing" ? $missingCount : $totalCount }} แผนก</span>
                        </div>
                    </div>

                    <div class="max-h-[calc(100vh-280px)] min-h-80 overflow-x-auto overflow-y-auto">
                        <table class="table table-pin-rows table-sm">
                            <thead>
                                <tr>
                                    <th>แผนก</th>
                                    <th>ผู้อนุมัติ</th>
                                    <th class="text-right">อัปเดตล่าสุด</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($datas as $item)
                                    @php $hasApprover = (bool) ($item->has_approver ?? false); @endphp
                                    <tr
                                        class="row-item hover:bg-base-200/60 cursor-pointer transition-colors {{ $hasApprover ? "" : "bg-error/5" }}"
                                        data-department="{{ $item->department }}"
                                        data-has-approver="{{ $hasApprover ? "1" : "0" }}"
                                        data-userid="{{ $item->userid ?? "" }}"
                                        data-name="{{ $item->name ?? "" }}"
                                        data-position="{{ $item->position ?? "" }}"
                                        data-email="{{ $item->email ?? "" }}"
                                        onclick="fillUpdateForm(this)"
                                    >
                                        <td class="align-top">
                                            <div class="flex items-start gap-2">
                                                <span class="status mt-1.5 {{ $hasApprover ? "status-success" : "status-error" }}"></span>
                                                <div>
                                                    <div class="font-semibold">{{ $item->department }}</div>
                                                    @if (! $hasApprover)
                                                        <span class="badge badge-error badge-soft badge-xs mt-1">ยังไม่มีผู้อนุมัติ</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($hasApprover)
                                                <div class="flex flex-col gap-0.5">
                                                    <div class="flex flex-wrap items-center gap-1.5 font-medium">
                                                        {{ $item->name }}
                                                        <span class="badge badge-ghost badge-xs">{{ $item->userid }}</span>
                                                    </div>
                                                    <div class="text-base-content/60 text-xs">{{ $item->position }}</div>
                                                    <div class="text-xs">{{ $item->email }}</div>
                                                </div>
                                            @else
                                                <span class="text-error/80 text-xs italic">คลิกเพื่อกำหนดผู้อนุมัติ</span>
                                            @endif
                                        </td>
                                        <td class="align-top text-right">
                                            @if ($item->last_update)
                                                <div class="text-xs font-medium">{{ date("d/m/Y H:i", strtotime($item->last_update)) }}</div>
                                                @if ($item->last_userid || $item->last_username)
                                                    <div class="text-base-content/50 text-[11px]">{{ $item->last_username ?? $item->last_userid }}</div>
                                                @endif
                                            @else
                                                <span class="text-base-content/35 text-xs italic">ยังไม่มีประวัติ</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-base-content/50 py-16 text-center italic" colspan="3">ไม่พบข้อมูลแผนก</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="hidden py-16 text-center" id="emptyFilterState">
                            <i class="fas fa-building mb-2 block text-3xl opacity-30"></i>
                            <p class="text-base-content/50 text-sm italic">ไม่พบแผนกตามเงื่อนไขที่เลือก</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script>
        const allDepartments = @json($depts->values());
        const defaultFilter = @json($defaultFilter);
        let currentFilter = defaultFilter;

        const searchInput = document.getElementById('deptSearch');
        const rows = () => document.querySelectorAll('.row-item');
        const emptyState = document.getElementById('emptyFilterState');
        const visibleCount = document.getElementById('visibleCount');
        const tableEl = document.querySelector('table');

        function departmentName(row) {
            return (row.dataset.department || '').trim();
        }

        function rowHasApprover(row) {
            return row.dataset.hasApprover === '1';
        }

        function applyFilters() {
            const filterText = searchInput.value.toLowerCase().trim();
            let shown = 0;

            rows().forEach(row => {
                const matchesSearch = departmentName(row).toLowerCase().includes(filterText);
                const matchesFilter = currentFilter === 'all' ||
                    (currentFilter === 'missing' && !rowHasApprover(row)) ||
                    (currentFilter === 'assigned' && rowHasApprover(row));

                const isVisible = matchesSearch && (filterText ? true : matchesFilter);
                row.classList.toggle('hidden', !isVisible);
                if (isVisible) {
                    shown += 1;
                }
            });

            const noRows = shown === 0;
            emptyState.classList.toggle('hidden', !noRows);
            if (tableEl) {
                tableEl.classList.toggle('hidden', noRows && rows().length > 0);
            }
            visibleCount.textContent = `${shown} แผนก`;
        }

        function setFilter(filter) {
            currentFilter = filter;
            document.querySelectorAll('[data-filter]').forEach(tab => {
                tab.classList.toggle('tab-active', tab.dataset.filter === filter);
            });
            applyFilters();
        }

        function highlightRow(row) {
            rows().forEach(item => item.classList.remove('bg-primary/10', 'ring-1', 'ring-primary'));
            row.classList.add('bg-primary/10', 'ring-1', 'ring-primary');
        }

        function fillEmptyForm(department) {
            document.getElementById('form_dept').value = department;
            document.getElementById('form_userid').value = '';
            document.getElementById('form_name').value = '';
            document.getElementById('form_position').value = '';
            document.getElementById('form_email').value = '';
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('formTitle').textContent = 'กำหนดผู้อนุมัติ';
            document.getElementById('formHint').textContent = department;
            document.getElementById('submitLabel').textContent = 'บันทึกผู้อนุมัติ';
        }

        function fillUpdateForm(row) {
            highlightRow(row);

            const department = departmentName(row);
            const hasApprover = rowHasApprover(row);
            const userid = (row.dataset.userid || '').trim();
            const name = (row.dataset.name || '').trim();
            const position = (row.dataset.position || '').trim();
            const email = (row.dataset.email || '').trim();

            document.getElementById('form_dept').value = department;
            document.getElementById('form_userid').value = userid === '-' ? '' : userid;
            document.getElementById('form_name').value = name === '-' ? '' : name;
            document.getElementById('form_position').value = position === '-' ? '' : position;
            document.getElementById('form_email').value = email === '-' ? '' : email;
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('formTitle').textContent = hasApprover ? 'แก้ไขผู้อนุมัติ' : 'กำหนดผู้อนุมัติ';
            document.getElementById('formHint').textContent = department;
            document.getElementById('submitLabel').textContent = hasApprover ? 'อัปเดตผู้อนุมัติ' : 'บันทึกผู้อนุมัติ';

            if (window.innerWidth < 1024) {
                document.getElementById('updateForm').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }

        function selectDepartmentByName(name) {
            const needle = name.toLowerCase().trim();
            if (!needle) {
                return false;
            }

            const exact = Array.from(rows()).find(row => departmentName(row).toLowerCase() === needle);
            const partial = Array.from(rows()).find(row => departmentName(row).toLowerCase().includes(needle) && !row.classList.contains('hidden'));
            const match = exact || partial;

            if (match) {
                if (match.classList.contains('hidden')) {
                    currentFilter = 'all';
                    document.querySelectorAll('[data-filter]').forEach(tab => {
                        tab.classList.toggle('tab-active', tab.dataset.filter === 'all');
                    });
                    applyFilters();
                }
                fillUpdateForm(match);
                match.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return true;
            }

            const known = allDepartments.find(dept => String(dept).toLowerCase() === needle);
            if (known) {
                fillEmptyForm(known);
                return true;
            }

            return false;
        }

        document.querySelectorAll('[data-filter]').forEach(tab => {
            tab.addEventListener('click', () => setFilter(tab.dataset.filter));
        });

        searchInput.addEventListener('input', function() {
            applyFilters();
        });

        searchInput.addEventListener('change', function() {
            selectDepartmentByName(this.value);
        });

        searchInput.addEventListener('keypress', function(e) {
            if (e.key !== 'Enter') {
                return;
            }
            e.preventDefault();
            if (!selectDepartmentByName(this.value)) {
                const firstVisible = Array.from(rows()).find(row => !row.classList.contains('hidden'));
                if (firstVisible) {
                    fillUpdateForm(firstVisible);
                }
            }
        });

        function getUserData(btn) {
            const userid = document.getElementById('form_userid').value.trim();
            const original = btn.innerHTML;

            if (!userid) {
                Swal.fire({
                    title: "กรุณาระบุ User ID",
                    icon: "warning",
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

            axios.post('{{ route("approvers.getuser") }}', {
                    userid
                })
                .then(response => {
                    if (response.data.success) {
                        document.getElementById('form_name').value = response.data.user.name;
                        document.getElementById('form_position').value = response.data.user.position;
                        document.getElementById('form_email').value = response.data.user.email;

                        Swal.fire({
                            title: "พบข้อมูลผู้ใช้งาน",
                            icon: "success",
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        throw new Error("User not found");
                    }
                })
                .catch(() => {
                    Swal.fire({
                        title: "ไม่พบข้อมูล!",
                        text: "รหัสพนักงานไม่ถูกต้อง หรือไม่มีในระบบ",
                        icon: "error",
                        confirmButtonText: 'ตกลง',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-error'
                        }
                    });
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
        }

        applyFilters();

        const oldDepartment = document.getElementById('form_dept').value.trim();
        if (oldDepartment) {
            selectDepartmentByName(oldDepartment);
        }
    </script>
@endpush
