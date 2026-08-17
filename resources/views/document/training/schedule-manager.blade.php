<x-document.section-card
    title="กำหนดการและผู้เข้าร่วม"
    subtitle="แก้ไขวันอบรม ช่วงเวลา และรายชื่อผู้เข้าร่วมแบบเรียลไทม์"
    icon="fas fa-calendar-check"
    tone="info"
    flush
>
    <x-slot:actions>
        <button class="btn btn-ghost btn-sm btn-circle" type="button" title="โหลดข้อมูลใหม่" onclick="TrainingSchedule.load()">
            <i class="fas fa-rotate-right"></i>
        </button>
        <button class="btn btn-info btn-sm gap-2 rounded-full px-4" type="button" onclick="TrainingSchedule.openDateModal()">
            <i class="fas fa-calendar-plus"></i>
            เพิ่มวันอบรม
        </button>
    </x-slot:actions>

    <div class="bg-base-200 border-base-200 grid grid-cols-2 gap-px border-b md:grid-cols-4" id="schedule-stats">
        @foreach ([['วันอบรม', 'fa-calendar-day'], ['ช่วงเวลา', 'fa-clock'], ['ผู้เข้าร่วม', 'fa-users'], ['เช็คอินแล้ว', 'fa-user-check']] as [$label, $icon])
            <div class="bg-base-100 flex items-center gap-3 px-4 py-3">
                <i class="fas {{ $icon }} text-info/40"></i>
                <div>
                    <div class="text-base-content/40 text-[10px] font-bold uppercase tracking-wider">{{ $label }}</div>
                    <div class="text-base font-black leading-tight" data-stat="{{ $loop->index }}">—</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="space-y-3 p-4 md:p-5" id="schedule-body">
        <div class="text-base-content/30 flex flex-col items-center justify-center gap-3 py-12">
            <span class="loading loading-spinner loading-lg"></span>
            <p class="text-xs font-bold">กำลังโหลดกำหนดการ...</p>
        </div>
    </div>
</x-document.section-card>

@push('scripts')
    <script>
        window.TrainingSchedule = (function() {
            const projectId = '{{ $document->id }}';
            const routes = {
                detail: "{{ route('document.training.projectDetail') }}",
                dateAdd: "{{ route('document.training.date.add') }}",
                dateEdit: "{{ route('document.training.date.edit') }}",
                dateRemove: "{{ route('document.training.date.remove') }}",
                timeAdd: "{{ route('document.training.time.add') }}",
                timeEdit: "{{ route('document.training.time.edit') }}",
                timeRemove: "{{ route('document.training.time.remove') }}",
                participantAdd: "{{ route('document.training.participant.add') }}",
                participantRemove: "{{ route('document.training.participant.remove') }}",
                lecturerAdd: "{{ route('document.training.lecturer.add') }}",
                lecturerRemove: "{{ route('document.training.lecturer.remove') }}",
                userSearch: "{{ route('user.search') }}",
                departments: "{{ route('user.departments') }}",
                departmentUsers: "{{ route('user.formDepartment') }}",
            };

            const modalClass = {
                confirmButton: 'btn btn-primary px-8 mx-2',
                denyButton: 'btn btn-outline btn-primary px-8 mx-2',
                cancelButton: 'btn btn-ghost px-8 mx-2',
                popup: 'rounded-3xl shadow-2xl border border-base-200',
            };

            const state = {
                dates: {},
                times: {},
            };

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';
                return String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#39;');
            }

            function toast(icon, title, text = '') {
                return Swal.fire({
                    icon,
                    title,
                    text,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: icon === 'error' ? 4000 : 2200,
                    timerProgressBar: true,
                });
            }

            function errorMessage(error) {
                const data = error?.response?.data;
                if (data?.errors) {
                    const first = Object.values(data.errors)[0];
                    return Array.isArray(first) ? first[0] : String(first);
                }
                return data?.message || 'ไม่สามารถเชื่อมต่อระบบฝึกอบรมได้';
            }

            async function submit(url, payload, loadingTitle, {
                reloadPage = false
            } = {}) {
                Swal.fire({
                    title: loadingTitle,
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                try {
                    const {
                        data
                    } = await axios.post(url, payload);

                    if (!data.success) {
                        await toast('error', 'ไม่สำเร็จ', data.message || '');
                        return false;
                    }

                    await toast('success', 'ดำเนินการสำเร็จ', data.message || '');

                    if (reloadPage) {
                        window.location.reload();
                        return true;
                    }

                    await load();
                    return true;
                } catch (error) {
                    await toast('error', 'เกิดข้อผิดพลาด', errorMessage(error));
                    return false;
                }
            }

            async function load() {
                const body = document.getElementById('schedule-body');
                if (!body) return;

                try {
                    const {
                        data
                    } = await axios.post(routes.detail, {
                        project_id: projectId
                    });

                    if (!data.success) {
                        renderMessage(data.message || 'ไม่พบข้อมูลโครงการ', 'text-error');
                        return;
                    }

                    render(data.project || {});
                } catch (error) {
                    renderMessage(errorMessage(error), 'text-error');
                }
            }

            function renderMessage(message, tone = 'opacity-40') {
                document.getElementById('schedule-body').innerHTML = `
                    <div class="flex flex-col items-center justify-center gap-2 py-12 ${tone}">
                        <i class="fas fa-circle-exclamation text-2xl"></i>
                        <p class="text-xs font-bold">${escapeHtml(message)}</p>
                    </div>`;
            }

            function render(project) {
                state.dates = {};
                state.times = {};

                const dates = (project.dates || []).filter((date) => date.date_active !== false);
                renderStats(dates);

                if (dates.length === 0) {
                    renderMessage('ยังไม่มีวันอบรมในโครงการ กดปุ่ม "เพิ่มวันอบรม" เพื่อเริ่มต้น');
                    return;
                }

                document.getElementById('schedule-body').innerHTML = dates.map(dateCard).join('');
            }

            function renderStats(dates) {
                const times = dates.flatMap((date) => (date.times || []).filter((time) => time.time_active !== false));
                const participants = times.flatMap((time) => time.participants || []);
                const uniqueUsers = new Set(participants.map((participant) => participant.userid));
                const checkedIn = participants.filter((participant) => participant.attend_datetime).length;

                [dates.length, times.length, uniqueUsers.size, checkedIn].forEach((value, index) => {
                    const node = document.querySelector(`[data-stat="${index}"]`);
                    if (node) node.textContent = value;
                });
            }

            function dateCard(date) {
                state.dates[date.date_id] = date;
                const times = (date.times || []).filter((time) => time.time_active !== false);

                return `
                    <article class="border-base-200 bg-base-200/20 overflow-hidden rounded-2xl border">
                        <header class="bg-base-100 border-base-200 flex flex-wrap items-start justify-between gap-3 border-b px-5 py-4">
                            <div class="flex items-start gap-3">
                                <div class="bg-info/10 text-info flex size-11 flex-col items-center justify-center rounded-xl leading-none">
                                    <i class="fas fa-calendar-day text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black">${escapeHtml(date.date_title || date.date_datetime)}</div>
                                    <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] opacity-60">
                                        <span class="font-mono">${escapeHtml(date.date_datetime)}</span>
                                        ${date.date_location ? `<span><i class="fas fa-location-dot mr-1 opacity-50"></i>${escapeHtml(date.date_location)}</span>` : ''}
                                        ${date.date_detail ? `<span><i class="fas fa-note-sticky mr-1 opacity-50"></i>${escapeHtml(date.date_detail)}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button class="btn btn-ghost btn-xs gap-1 rounded-full" type="button" onclick="TrainingSchedule.openTimeModal(${date.date_id})">
                                    <i class="fas fa-plus"></i> ช่วงเวลา
                                </button>
                                <button class="btn btn-ghost btn-xs gap-1 rounded-full" type="button" onclick="TrainingSchedule.openLecturerModal(${date.date_id})">
                                    <i class="fas fa-chalkboard-user"></i> วิทยากร
                                </button>
                                <button class="btn btn-ghost btn-xs btn-circle" type="button" title="แก้ไขวันอบรม" onclick="TrainingSchedule.openDateModal(${date.date_id})">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-ghost btn-xs btn-circle text-error" type="button" title="ลบวันอบรม" onclick="TrainingSchedule.removeDate(${date.date_id})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </header>
                        <div class="space-y-3 p-4">
                            ${times.length === 0
                                ? '<p class="py-4 text-center text-xs italic opacity-30">ยังไม่มีช่วงเวลาในวันนี้</p>'
                                : times.map((time) => timeCard(time, date.date_id)).join('')}
                            ${lecturerRow(date.lecturers || [], date.date_id)}
                        </div>
                    </article>`;
            }

            function timeCard(time, dateId) {
                state.times[time.time_id] = {
                    ...time,
                    date_id: dateId
                };
                const participants = time.participants || [];
                const seats = time.time_limit ?
                    `${participants.length}/${time.time_max}` :
                    `${participants.length}`;

                return `
                    <div class="bg-base-100 border-base-200 overflow-hidden rounded-xl border">
                        <div class="border-base-200 flex flex-wrap items-center justify-between gap-2 border-b px-4 py-3">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-clock text-primary/50"></i>
                                <div>
                                    <div class="font-mono text-sm font-bold">${escapeHtml(time.time_start)} - ${escapeHtml(time.time_end)}</div>
                                    ${time.time_detail ? `<div class="text-[11px] opacity-50">${escapeHtml(time.time_detail)}</div>` : ''}
                                </div>
                                <span class="badge badge-sm ${time.time_limit ? 'badge-warning' : 'badge-ghost'} font-mono font-bold">
                                    <i class="fas fa-users mr-1 text-[9px]"></i>${seats}
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button class="btn btn-ghost btn-xs gap-1 rounded-full" type="button" onclick="TrainingSchedule.openParticipantModal(${time.time_id})">
                                    <i class="fas fa-user-plus"></i> ผู้เข้าร่วม
                                </button>
                                <button class="btn btn-ghost btn-xs btn-circle" type="button" title="แก้ไขช่วงเวลา" onclick="TrainingSchedule.openTimeModal(${dateId}, ${time.time_id})">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-ghost btn-xs btn-circle text-error" type="button" title="ลบช่วงเวลา" onclick="TrainingSchedule.removeTime(${time.time_id})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        ${participantList(participants, time.time_id)}
                    </div>`;
            }

            /** Lists stay expanded while small and collapse with a search box once they grow. */
            const INLINE_LIST_LIMIT = 10;

            function searchBoxHtml(containerId, placeholder) {
                return `
                    <label class="input input-bordered input-xs flex w-full items-center gap-2">
                        <i class="fas fa-magnifying-glass text-[10px] opacity-40"></i>
                        <input type="search" class="grow" placeholder="${placeholder}" oninput="TrainingSchedule.filterList(this, '${containerId}')">
                    </label>`;
            }

            function collapsibleList({
                containerId,
                title,
                count,
                summaryBadges,
                emptyText,
                addButton,
                itemsHtml,
                searchPlaceholder,
            }) {
                if (count === 0) {
                    return `
                        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
                            <span class="text-xs italic opacity-30">${emptyText}</span>
                            ${addButton}
                        </div>`;
                }

                return `
                    <details class="group/list" ${count <= INLINE_LIST_LIMIT ? 'open' : ''}>
                        <summary class="hover:bg-base-200/40 flex cursor-pointer list-none flex-wrap items-center justify-between gap-2 px-4 py-2.5 transition-colors">
                            <span class="flex items-center gap-2 text-xs font-bold">
                                <i class="fas fa-chevron-right text-[10px] opacity-40 transition-transform group-open/list:rotate-90"></i>
                                ${title}
                                <span class="badge badge-xs badge-ghost font-mono font-bold">${count}</span>
                            </span>
                            <span class="flex flex-wrap items-center gap-1">${summaryBadges}</span>
                        </summary>
                        <div class="border-base-200 space-y-2 border-t p-3">
                            ${count > INLINE_LIST_LIMIT ? searchBoxHtml(containerId, searchPlaceholder) : ''}
                            <ul id="${containerId}" class="max-h-64 gap-1.5 overflow-y-auto pr-1 sm:grid sm:grid-cols-2 xl:grid-cols-3">
                                ${itemsHtml}
                            </ul>
                            <p class="hidden py-2 text-center text-xs italic opacity-30" data-empty>ไม่พบรายชื่อที่ค้นหา</p>
                        </div>
                    </details>`;
            }

            function participantList(participants, timeId) {
                const approved = participants.filter((participant) => participant.approve_datetime).length;
                const checkedIn = participants.filter((participant) => participant.attend_datetime && !participant.approve_datetime).length;
                const waiting = participants.length - approved - checkedIn;

                const addButton = `
                    <button class="btn btn-ghost btn-xs gap-1 rounded-full" type="button" onclick="TrainingSchedule.openParticipantModal(${timeId})">
                        <i class="fas fa-user-plus"></i> เพิ่มผู้เข้าร่วม
                    </button>`;

                return collapsibleList({
                    containerId: `participants-${timeId}`,
                    title: 'ผู้เข้าร่วม',
                    count: participants.length,
                    summaryBadges: `
                        ${approved > 0 ? `<span class="badge badge-xs badge-success gap-1 font-bold">อนุมัติ ${approved}</span>` : ''}
                        ${checkedIn > 0 ? `<span class="badge badge-xs badge-info gap-1 font-bold">เช็คอิน ${checkedIn}</span>` : ''}
                        ${waiting > 0 ? `<span class="badge badge-xs badge-ghost gap-1 font-bold">รอ ${waiting}</span>` : ''}`,
                    emptyText: 'ยังไม่มีผู้เข้าร่วมในช่วงเวลานี้',
                    addButton,
                    itemsHtml: participants.map(participantItem).join(''),
                    searchPlaceholder: 'ค้นหาชื่อหรือรหัสพนักงาน',
                });
            }

            function participantItem(participant) {
                let dot = 'bg-base-300';
                let status = 'ยังไม่เช็คอิน';

                if (participant.approve_datetime) {
                    dot = 'bg-success';
                    status = 'อนุมัติแล้ว';
                } else if (participant.attend_datetime) {
                    dot = 'bg-info';
                    status = 'เช็คอินแล้ว';
                }

                const name = escapeHtml(participant.name);
                const userid = escapeHtml(participant.userid);

                return `
                    <li class="border-base-200 bg-base-100 flex items-center gap-2 rounded-lg border px-2.5 py-1.5" data-search="${`${name} ${userid}`.toLowerCase()}">
                        <span class="${dot} size-2 shrink-0 rounded-full"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-xs font-bold">${name}</span>
                            <span class="block truncate text-[10px] opacity-50"><span class="font-mono">${userid}</span> · ${status}</span>
                        </span>
                        <button class="btn btn-circle btn-ghost btn-xs" type="button" title="นำออก"
                            onclick="TrainingSchedule.removeParticipant(${participant.attend_id}, '${name}')">
                            <i class="fas fa-xmark text-[10px]"></i>
                        </button>
                    </li>`;
            }

            function lecturerRow(lecturers, dateId) {
                const addButton = `
                    <button class="btn btn-ghost btn-xs gap-1 rounded-full" type="button" onclick="TrainingSchedule.openLecturerModal(${dateId})">
                        <i class="fas fa-user-plus"></i> เพิ่มวิทยากร
                    </button>`;

                return `
                    <div class="border-base-200 bg-base-100 overflow-hidden rounded-xl border">
                        ${collapsibleList({
                            containerId: `lecturers-${dateId}`,
                            title: 'วิทยากร',
                            count: lecturers.length,
                            summaryBadges: '',
                            emptyText: 'ยังไม่มีวิทยากรในวันนี้',
                            addButton,
                            itemsHtml: lecturers.map((lecturer) => lecturerItem(lecturer, dateId)).join(''),
                            searchPlaceholder: 'ค้นหาวิทยากร',
                        })}
                    </div>`;
            }

            function lecturerItem(lecturer, dateId) {
                const lectureId = lecturer.lecture_id ?? lecturer.lecturer_id ?? 'null';
                const name = escapeHtml(lecturer.name);
                const userid = escapeHtml(lecturer.userid || '');

                return `
                    <li class="border-secondary/20 bg-secondary/5 flex items-center gap-2 rounded-lg border px-2.5 py-1.5" data-search="${`${name} ${userid}`.toLowerCase()}">
                        <i class="fas fa-chalkboard-user text-secondary shrink-0 text-[10px]"></i>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-xs font-bold">${name}</span>
                            <span class="block truncate font-mono text-[10px] opacity-50">${userid}</span>
                        </span>
                        <button class="btn btn-circle btn-ghost btn-xs" type="button" title="นำวิทยากรออก"
                            onclick="TrainingSchedule.removeLecturer(${dateId}, ${lectureId}, '${userid}', '${name}')">
                            <i class="fas fa-xmark text-[10px]"></i>
                        </button>
                    </li>`;
            }

            function filterList(input, containerId) {
                const container = document.getElementById(containerId);
                if (!container) return;

                const term = input.value.trim().toLowerCase();
                let visible = 0;

                container.querySelectorAll('[data-search]').forEach((item) => {
                    const matches = term === '' || item.dataset.search.includes(term);
                    item.classList.toggle('hidden', !matches);
                    if (matches) visible += 1;
                });

                const empty = container.parentElement.querySelector('[data-empty]');
                if (empty) empty.classList.toggle('hidden', visible > 0);
            }

            function fieldHtml(id, label, type, value, extra = '') {
                return `
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-bold">${label}</span></label>
                        <input id="${id}" type="${type}" value="${escapeHtml(value ?? '')}" class="input input-bordered input-sm w-full" ${extra}>
                    </div>`;
            }

            function timeFieldsHtml(time) {
                return `
                    <div class="grid grid-cols-2 gap-3">
                        ${fieldHtml('swal-time-start', 'เวลาเริ่ม', 'time', time?.time_start ?? '09:00')}
                        ${fieldHtml('swal-time-end', 'เวลาสิ้นสุด', 'time', time?.time_end ?? '16:00')}
                    </div>
                    ${fieldHtml('swal-time-detail', 'รายละเอียดช่วงเวลา', 'text', time?.time_detail, 'placeholder="เช่น ภาคบรรยาย"')}
                    <div class="bg-base-200/50 flex items-center justify-between gap-3 rounded-xl p-3">
                        <label class="flex cursor-pointer items-center gap-2 text-xs font-bold" for="swal-time-limit">
                            <input id="swal-time-limit" type="checkbox" class="checkbox checkbox-sm checkbox-primary" ${time?.time_limit ? 'checked' : ''}>
                            จำกัดจำนวนที่นั่ง
                        </label>
                        <input id="swal-time-max" type="number" min="0" value="${time?.time_max ?? 0}" class="input input-bordered input-sm w-24 text-center" ${time?.time_limit ? '' : 'disabled'}>
                    </div>`;
            }

            function bindLimitToggle() {
                const toggle = document.getElementById('swal-time-limit');
                const max = document.getElementById('swal-time-max');
                if (!toggle || !max) return;
                toggle.addEventListener('change', () => {
                    max.disabled = !toggle.checked;
                });
            }

            function collectTimeFields() {
                const start = document.getElementById('swal-time-start').value;
                const end = document.getElementById('swal-time-end').value;

                if (!start || !end) {
                    Swal.showValidationMessage('กรุณาระบุเวลาเริ่มและเวลาสิ้นสุด');
                    return null;
                }

                if (end <= start) {
                    Swal.showValidationMessage('เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม');
                    return null;
                }

                const limited = document.getElementById('swal-time-limit').checked;

                return {
                    time_start: start,
                    time_end: end,
                    time_detail: document.getElementById('swal-time-detail').value || null,
                    time_limit: limited,
                    time_max: limited ? Number(document.getElementById('swal-time-max').value || 0) : 0,
                };
            }

            async function openDateModal(dateId = null) {
                const date = dateId ? state.dates[dateId] : null;
                const isEdit = Boolean(date);

                const {
                    value
                } = await Swal.fire({
                    title: isEdit ? 'แก้ไขวันอบรม' : 'เพิ่มวันอบรม',
                    width: '34rem',
                    html: `
                        <div class="space-y-2 py-2 text-left">
                            ${fieldHtml('swal-date', 'วันที่อบรม', 'date', date?.date_datetime ?? '')}
                            ${fieldHtml('swal-date-location', 'สถานที่', 'text', date?.date_location, 'placeholder="เช่น ห้องประชุม A"')}
                            ${fieldHtml('swal-date-detail', 'รายละเอียด', 'text', date?.date_detail, 'placeholder="รายละเอียดเพิ่มเติม"')}
                            ${isEdit ? '' : `<div class="divider my-2 text-[10px] uppercase opacity-40">ช่วงเวลาแรกของวันนี้</div>${timeFieldsHtml(null)}`}
                        </div>`,
                    showCancelButton: true,
                    confirmButtonText: isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มวันอบรม',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: modalClass,
                    didOpen: bindLimitToggle,
                    preConfirm: () => {
                        const dateValue = document.getElementById('swal-date').value;
                        if (!dateValue) {
                            Swal.showValidationMessage('กรุณาระบุวันที่อบรม');
                            return null;
                        }

                        const payload = {
                            date_datetime: dateValue,
                            date_location: document.getElementById('swal-date-location').value || null,
                            date_detail: document.getElementById('swal-date-detail').value || null,
                        };

                        if (isEdit) return payload;

                        const time = collectTimeFields();
                        if (!time) return null;

                        return {
                            ...payload,
                            times: [time]
                        };
                    },
                });

                if (!value) return;

                await submit(
                    isEdit ? routes.dateEdit : routes.dateAdd, {
                        project_id: projectId,
                        ...(isEdit ? {
                            date_id: dateId
                        } : {}),
                        ...value
                    },
                    isEdit ? 'กำลังบันทึกการแก้ไข...' : 'กำลังเพิ่มวันอบรม...',
                );
            }

            async function openTimeModal(dateId, timeId = null) {
                const time = timeId ? state.times[timeId] : null;
                const isEdit = Boolean(time);

                const {
                    value
                } = await Swal.fire({
                    title: isEdit ? 'แก้ไขช่วงเวลา' : 'เพิ่มช่วงเวลา',
                    width: '32rem',
                    html: `<div class="space-y-2 py-2 text-left">${timeFieldsHtml(time)}</div>`,
                    showCancelButton: true,
                    confirmButtonText: isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มช่วงเวลา',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: modalClass,
                    didOpen: bindLimitToggle,
                    preConfirm: collectTimeFields,
                });

                if (!value) return;

                await submit(
                    isEdit ? routes.timeEdit : routes.timeAdd, {
                        project_id: projectId,
                        ...(isEdit ? {
                            time_id: timeId
                        } : {
                            date_id: dateId
                        }),
                        ...value
                    },
                    isEdit ? 'กำลังบันทึกการแก้ไข...' : 'กำลังเพิ่มช่วงเวลา...',
                );
            }

            async function removeDate(dateId) {
                const date = state.dates[dateId];
                const confirmed = await confirmDelete(
                    'ลบวันอบรมนี้?',
                    `${escapeHtml(date?.date_title ?? '')}<br><b class="text-error">ช่วงเวลาและผู้เข้าร่วมในวันนี้จะถูกนำออกทั้งหมด</b>`,
                );

                if (confirmed) {
                    await submit(routes.dateRemove, {
                        project_id: projectId,
                        date_id: dateId
                    }, 'กำลังลบวันอบรม...', {
                        reloadPage: true
                    });
                }
            }

            async function removeTime(timeId) {
                const time = state.times[timeId];
                const confirmed = await confirmDelete(
                    'ลบช่วงเวลานี้?',
                    `${escapeHtml(time?.time_start ?? '')} - ${escapeHtml(time?.time_end ?? '')}<br><b class="text-error">ผู้เข้าร่วมในช่วงเวลานี้จะถูกนำออก</b>`,
                );

                if (confirmed) {
                    await submit(routes.timeRemove, {
                        project_id: projectId,
                        time_id: timeId
                    }, 'กำลังลบช่วงเวลา...', {
                        reloadPage: true
                    });
                }
            }

            async function removeParticipant(attendId, name) {
                const confirmed = await confirmDelete('นำผู้เข้าร่วมออก?', `ต้องการนำ <b>${escapeHtml(name)}</b> ออกจากช่วงเวลานี้`);

                if (confirmed) {
                    await submit(routes.participantRemove, {
                        project_id: projectId,
                        attend_id: attendId
                    }, 'กำลังนำผู้เข้าร่วมออก...', {
                        reloadPage: true
                    });
                }
            }

            async function removeLecturer(dateId, lectureId, userid, name) {
                const confirmed = await confirmDelete(
                    'นำวิทยากรออก?',
                    `ต้องการนำ <b>${escapeHtml(name)}</b> ออกจากวันอบรมนี้`,
                );

                if (!confirmed) {
                    return;
                }

                const payload = {
                    project_id: projectId,
                };

                if (lectureId) {
                    payload.lecture_id = lectureId;
                } else {
                    payload.date_id = dateId;
                    payload.userid = userid;
                }

                await submit(routes.lecturerRemove, payload, 'กำลังนำวิทยากรออก...', {
                    reloadPage: true,
                });
            }

            async function openLecturerModal(dateId) {
                const result = await Swal.fire({
                    title: 'เพิ่มวิทยากร',
                    text: 'เลือกวิธีการเพิ่มรายชื่อ',
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'ค้นหารายบุคคล',
                    denyButtonText: 'เลือกจากแผนก',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: modalClass,
                });

                if (result.isConfirmed) {
                    await addLecturersById(dateId);
                    return;
                }

                if (result.isDenied) {
                    await addLecturersByDepartment(dateId);
                }
            }

            async function addLecturersById(dateId) {
                const staged = [];

                const {
                    value: users
                } = await Swal.fire({
                    title: 'เพิ่มวิทยากร',
                    width: '32rem',
                    html: `
                        <div class="space-y-3 py-2 text-left">
                            <div class="join w-full">
                                <input id="swal-userid" class="input input-bordered join-item w-full" placeholder="รหัสพนักงาน เช่น 650001">
                                <button type="button" id="swal-search" class="btn btn-secondary join-item px-6"><i class="fas fa-search"></i></button>
                            </div>
                            <div id="swal-staged" class="border-base-200 flex max-h-52 flex-wrap gap-2 overflow-y-auto rounded-xl border p-3">
                                <span class="text-xs italic opacity-30">ยังไม่ได้เลือกรายชื่อ</span>
                            </div>
                        </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'เพิ่มวิทยากร',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: modalClass,
                    didOpen: () => bindUserSearch(staged),
                    preConfirm: () => {
                        if (staged.length === 0) {
                            Swal.showValidationMessage('กรุณาเลือกรายชื่ออย่างน้อย 1 คน');
                            return null;
                        }
                        return staged.map((user) => user.userid);
                    },
                });

                if (users) {
                    await submitLecturers(dateId, users);
                }
            }

            async function addLecturersByDepartment(dateId) {
                const departments = await fetchDepartments();
                if (departments.length === 0) return;

                const {
                    value: department
                } = await Swal.fire({
                    title: 'เลือกแผนก',
                    input: 'select',
                    inputOptions: Object.fromEntries(departments.map((item) => [item, item])),
                    inputPlaceholder: 'กรุณาเลือกแผนก',
                    showCancelButton: true,
                    confirmButtonText: 'ค้นหารายชื่อ',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: modalClass,
                    preConfirm: (value) => value || Swal.showValidationMessage('กรุณาเลือกแผนก'),
                });

                if (!department) return;

                const users = await fetchDepartmentUsers(department);
                if (users.length === 0) return;

                const {
                    value: selected
                } = await Swal.fire({
                    title: `วิทยากรแผนก ${department}`,
                    width: '34rem',
                    html: departmentUsersHtml(users),
                    showCancelButton: true,
                    confirmButtonText: 'เพิ่มที่เลือก',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: modalClass,
                    didOpen: () => {
                        const selectAll = document.getElementById('swal-select-all');
                        selectAll.addEventListener('change', (event) => {
                            document.querySelectorAll('input[name="swal-users"]').forEach((checkbox) => {
                                checkbox.checked = event.target.checked;
                            });
                        });
                    },
                    preConfirm: () => {
                        const checked = Array.from(document.querySelectorAll('input[name="swal-users"]:checked'));
                        if (checked.length === 0) {
                            Swal.showValidationMessage('กรุณาเลือกอย่างน้อย 1 รายชื่อ');
                            return null;
                        }
                        return checked.map((checkbox) => checkbox.value);
                    },
                });

                if (selected) {
                    await submitLecturers(dateId, selected);
                }
            }

            async function submitLecturers(dateId, users) {
                await submit(routes.lecturerAdd, {
                    project_id: projectId,
                    date_id: dateId,
                    users,
                }, 'กำลังเพิ่มวิทยากร...', {
                    reloadPage: true,
                });
            }

            async function confirmDelete(title, html) {
                const result = await Swal.fire({
                    title,
                    html,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันลบ',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: {
                        ...modalClass,
                        confirmButton: 'btn btn-error px-8 mx-2'
                    },
                });

                return result.isConfirmed;
            }

            async function openParticipantModal(timeId) {
                const result = await Swal.fire({
                    title: 'เพิ่มผู้เข้าร่วม',
                    text: 'เลือกวิธีการเพิ่มรายชื่อ',
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'ค้นหารายบุคคล',
                    denyButtonText: 'เลือกจากแผนก',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: modalClass,
                });

                if (result.isConfirmed) {
                    await addParticipantsById(timeId);
                    return;
                }

                if (result.isDenied) {
                    await addParticipantsByDepartment(timeId);
                }
            }

            async function addParticipantsById(timeId) {
                const staged = [];

                const {
                    value: users
                } = await Swal.fire({
                    title: 'ค้นหารายบุคคล',
                    width: '32rem',
                    html: `
                        <div class="space-y-3 py-2 text-left">
                            <div class="join w-full">
                                <input id="swal-userid" class="input input-bordered join-item w-full" placeholder="รหัสพนักงาน เช่น 650001">
                                <button type="button" id="swal-search" class="btn btn-primary join-item px-6"><i class="fas fa-search"></i></button>
                            </div>
                            <div id="swal-staged" class="border-base-200 flex max-h-52 flex-wrap gap-2 overflow-y-auto rounded-xl border p-3">
                                <span class="text-xs italic opacity-30">ยังไม่ได้เลือกรายชื่อ</span>
                            </div>
                        </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'เพิ่มผู้เข้าร่วม',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: modalClass,
                    didOpen: () => bindUserSearch(staged),
                    preConfirm: () => {
                        if (staged.length === 0) {
                            Swal.showValidationMessage('กรุณาเลือกรายชื่ออย่างน้อย 1 คน');
                            return null;
                        }
                        return staged.map((user) => user.userid);
                    },
                });

                if (users) {
                    await submitParticipants(timeId, users);
                }
            }

            function bindUserSearch(staged) {
                const button = document.getElementById('swal-search');
                const input = document.getElementById('swal-userid');

                const runSearch = async () => {
                    const userid = input.value.trim();
                    if (!userid) return;

                    button.classList.add('loading');
                    try {
                        const {
                            data
                        } = await axios.post(routes.userSearch, {
                            userid
                        });

                        if (!data.status || !data.user) {
                            toast('error', 'ไม่พบพนักงานรหัสนี้');
                            return;
                        }

                        if (!staged.some((user) => user.userid === data.user.userid)) {
                            staged.push(data.user);
                            renderStaged(staged);
                        }

                        input.value = '';
                    } catch (error) {
                        toast('error', 'ไม่พบพนักงานรหัสนี้', errorMessage(error));
                    } finally {
                        button.classList.remove('loading');
                    }
                };

                button.addEventListener('click', runSearch);
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        runSearch();
                    }
                });
            }

            function renderStaged(staged) {
                const container = document.getElementById('swal-staged');

                if (staged.length === 0) {
                    container.innerHTML = '<span class="text-xs italic opacity-30">ยังไม่ได้เลือกรายชื่อ</span>';
                    return;
                }

                container.innerHTML = staged.map((user, index) => `
                    <span class="badge badge-primary badge-lg h-auto gap-2 rounded-full py-1.5 pl-3 pr-1">
                        <span class="text-xs font-bold">${escapeHtml(user.name)}</span>
                        <span class="font-mono text-[10px] opacity-70">${escapeHtml(user.userid)}</span>
                        <button type="button" class="btn btn-circle btn-ghost btn-xs" data-staged-index="${index}">
                            <i class="fas fa-xmark text-[10px]"></i>
                        </button>
                    </span>`).join('');

                container.querySelectorAll('[data-staged-index]').forEach((button) => {
                    button.addEventListener('click', () => {
                        staged.splice(Number(button.dataset.stagedIndex), 1);
                        renderStaged(staged);
                    });
                });
            }

            async function addParticipantsByDepartment(timeId) {
                const departments = await fetchDepartments();
                if (departments.length === 0) return;

                const {
                    value: department
                } = await Swal.fire({
                    title: 'เลือกแผนก',
                    input: 'select',
                    inputOptions: Object.fromEntries(departments.map((item) => [item, item])),
                    inputPlaceholder: 'กรุณาเลือกแผนก',
                    showCancelButton: true,
                    confirmButtonText: 'ค้นหารายชื่อ',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: modalClass,
                    preConfirm: (value) => value || Swal.showValidationMessage('กรุณาเลือกแผนก'),
                });

                if (!department) return;

                const users = await fetchDepartmentUsers(department);
                if (users.length === 0) return;

                const {
                    value: selected
                } = await Swal.fire({
                    title: `รายชื่อแผนก ${department}`,
                    width: '34rem',
                    html: departmentUsersHtml(users),
                    showCancelButton: true,
                    confirmButtonText: 'เพิ่มที่เลือก',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: modalClass,
                    didOpen: () => {
                        const selectAll = document.getElementById('swal-select-all');
                        selectAll.addEventListener('change', (event) => {
                            document.querySelectorAll('input[name="swal-users"]').forEach((checkbox) => {
                                checkbox.checked = event.target.checked;
                            });
                        });
                    },
                    preConfirm: () => {
                        const checked = Array.from(document.querySelectorAll('input[name="swal-users"]:checked'));
                        if (checked.length === 0) {
                            Swal.showValidationMessage('กรุณาเลือกอย่างน้อย 1 รายชื่อ');
                            return null;
                        }
                        return checked.map((checkbox) => checkbox.value);
                    },
                });

                if (selected) {
                    await submitParticipants(timeId, selected);
                }
            }

            function departmentUsersHtml(users) {
                return `
                    <div class="space-y-3 py-2 text-left">
                        <label class="bg-base-200 flex cursor-pointer items-center justify-between rounded-xl p-3">
                            <span class="text-xs font-bold uppercase opacity-50">เลือกทั้งหมด (${users.length})</span>
                            <input type="checkbox" id="swal-select-all" class="checkbox checkbox-primary checkbox-sm">
                        </label>
                        <div class="max-h-72 space-y-2 overflow-y-auto pr-1">
                            ${users.map((user) => `
                                <label class="border-base-200 hover:bg-base-200/50 flex cursor-pointer items-center gap-3 rounded-xl border p-3">
                                    <input type="checkbox" name="swal-users" value="${escapeHtml(user.userid)}" class="checkbox checkbox-primary checkbox-sm">
                                    <span class="flex flex-col overflow-hidden">
                                        <span class="truncate text-sm font-bold">${escapeHtml(user.name)}</span>
                                        <span class="truncate text-[10px] uppercase opacity-40">${escapeHtml(user.userid)} | ${escapeHtml(user.position)}</span>
                                    </span>
                                </label>`).join('')}
                        </div>
                    </div>`;
            }

            async function fetchDepartments() {
                Swal.fire({
                    title: 'กำลังดึงข้อมูลแผนก...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                try {
                    const {
                        data
                    } = await axios.post(routes.departments);
                    Swal.close();

                    if (!data.status || (data.departments || []).length === 0) {
                        await toast('error', 'ไม่พบข้อมูลแผนก');
                        return [];
                    }

                    return data.departments;
                } catch (error) {
                    await toast('error', 'ไม่สามารถดึงข้อมูลแผนกได้', errorMessage(error));
                    return [];
                }
            }

            async function fetchDepartmentUsers(department) {
                Swal.fire({
                    title: 'กำลังค้นหารายชื่อ...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                try {
                    const {
                        data
                    } = await axios.post(routes.departmentUsers, {
                        department
                    });
                    Swal.close();

                    if (data.status != 1 || (data.users || []).length === 0) {
                        await toast('info', 'ไม่พบพนักงานในแผนกนี้');
                        return [];
                    }

                    return data.users;
                } catch (error) {
                    await toast('error', 'ไม่สามารถดึงรายชื่อพนักงานได้', errorMessage(error));
                    return [];
                }
            }

            async function submitParticipants(timeId, users) {
                await submit(routes.participantAdd, {
                    project_id: projectId,
                    time_id: timeId,
                    users,
                }, 'กำลังเพิ่มผู้เข้าร่วม...', {
                    reloadPage: true
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', load);
            } else {
                load();
            }

            return {
                load,
                openDateModal,
                openTimeModal,
                openParticipantModal,
                openLecturerModal,
                removeDate,
                removeTime,
                removeParticipant,
                removeLecturer,
                filterList,
            };
        })();
    </script>
@endpush
