@php
    $canManageTraining = $document->tasks->where('task_user', auth()->user()->userid)->count() > 0 && $document->status === 'pending';
    $hasTrainingProject = $document->training_id !== null;
    $assessmentMethods = [
        'P' => 'ฝึกปฏิบัติจริง',
        'O' => 'สังเกตการปฏิบัติงาน',
        'I' => 'ถาม-ตอบ',
    ];
@endphp

@if ($canManageTraining)
    <section class="mt-2 space-y-4">
        <div class="border-primary/15 from-primary/8 via-base-100 to-base-100 overflow-hidden rounded-2xl border bg-gradient-to-br shadow-md">
            <!-- Control center header -->
            <div class="border-base-200 flex flex-wrap items-start justify-between gap-4 border-b px-5 py-4 md:px-6">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="bg-primary text-primary-content flex size-11 shrink-0 items-center justify-center rounded-xl shadow-sm">
                        <i class="fas fa-sliders"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-black tracking-tight">ศูนย์ควบคุมโครงการฝึกอบรม</h2>
                            @if ($hasTrainingProject)
                                <span class="badge badge-success badge-sm gap-1 font-bold">
                                    <span class="status status-success animate-pulse"></span>
                                    เปิดใช้งาน
                                </span>
                            @else
                                <span class="badge badge-warning badge-outline badge-sm font-bold">ยังไม่สร้างโครงการ</span>
                            @endif
                        </div>
                        <p class="text-base-content/50 mt-1 max-w-xl text-xs leading-relaxed">
                            {{ $hasTrainingProject ? 'จัดการกำหนดการ วิทยากร ผู้เข้าร่วม และบันทึกผลการประเมินก่อนปิดโครงการ' : 'สร้างโครงการในระบบ HRD เพื่อเปิดเช็คอินและจัดการรายละเอียดการอบรม' }}
                        </p>
                    </div>
                </div>
            </div>

            @if (!$hasTrainingProject)
                <!-- Onboarding -->
                <div class="grid gap-5 p-5 md:grid-cols-[1.2fr_0.8fr] md:items-center md:p-6">
                    <div class="space-y-3">
                        <p class="text-accent text-[10px] font-bold uppercase tracking-[0.2em]">ขั้นตอนที่ 1 · สร้างโครงการ</p>
                        <h3 class="text-xl font-black leading-tight">เริ่มต้นการฝึกอบรม</h3>
                        <p class="text-base-content/60 text-sm leading-relaxed">
                            ระบบจะสร้างโครงการจากวันอบรม วิทยากร และผู้เข้าร่วมในเอกสารนี้ จากนั้นคุณสามารถแก้ไขกำหนดการได้ทันที
                        </p>
                        <ul class="text-base-content/55 space-y-1.5 text-sm">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-success text-xs"></i>ส่งวิทยากรเป็น lecturers</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-success text-xs"></i>เปิดเช็คอินและอนุมัติการเข้าร่วม</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-success text-xs"></i>แก้ไขวัน เวลา วิทยากร และผู้เข้าร่วมภายหลังได้</li>
                        </ul>
                    </div>
                    <div class="border-base-200 bg-base-100/80 space-y-3 rounded-2xl border p-5 text-center shadow-sm">
                        <div class="bg-accent/10 text-accent mx-auto mb-1 flex size-14 items-center justify-center rounded-2xl">
                            <i class="fas fa-rocket text-xl"></i>
                        </div>
                        <button class="btn btn-accent btn-block gap-2 rounded-xl" type="button" onclick="createTraining()">
                            <i class="fas fa-plus-circle"></i>
                            สร้างการฝึกอบรม
                        </button>
                        <button class="btn btn-ghost btn-xs text-base-content/40 hover:text-error gap-1.5 rounded-full font-normal" type="button" onclick="cancelTraining()">
                            <i class="fas fa-ban text-[10px]"></i>
                            ยกเลิกโครงการ
                        </button>
                    </div>
                </div>
            @endif
        </div>

        @if ($hasTrainingProject)
            <!-- Schedule section -->
            <div class="space-y-4">
                @include('document.training.schedule-manager')
            </div>

            <!-- Assessment section -->
            <x-document.section-card
                title="ประเมินผลผู้เข้าร่วม"
                subtitle="โหลดรายชื่อผู้เข้าร่วมล่าสุดจากกำหนดการ · เลือกวิธีประเมินได้มากกว่า 1 แบบ เช่น P+I"
                icon="fas fa-clipboard-check"
                tone="primary"
                flush
            >
                <x-slot:actions>
                    <span class="badge badge-sm badge-ghost hidden font-mono font-bold" id="assessment-count"></span>
                    <button class="btn btn-ghost btn-sm btn-circle hidden" type="button" id="assessment-reload-btn" title="โหลดรายชื่อใหม่" onclick="TrainingAssessment.load()">
                        <i class="fas fa-rotate-right"></i>
                    </button>
                    <button class="btn btn-primary btn-sm gap-2 rounded-full" type="button" id="assessment-start-btn" onclick="TrainingAssessment.load()">
                        <i class="fas fa-play"></i>
                        เริ่มประเมิน
                    </button>
                    <button class="btn btn-primary btn-sm hidden gap-2 rounded-full" type="button" id="assessment-save-btn" onclick="saveAllAssessments()">
                        <i class="fas fa-save"></i>
                        บันทึกผลการประเมิน
                    </button>
                </x-slot:actions>

                <!-- Bulk apply -->
                <div class="border-base-200 bg-base-200/40 hidden flex-wrap items-end gap-3 border-b px-5 py-3" id="assessment-bulk">
                    <div class="w-full md:w-auto">
                        <span class="text-base-content/40 mb-1 block text-[10px] font-bold uppercase tracking-wider">กรอกให้ทุกคนพร้อมกัน</span>
                        <div class="flex flex-wrap items-center gap-2">
                            <input class="input input-bordered input-sm w-36" type="date" id="bulk-assessment-date" value="{{ date('Y-m-d') }}" aria-label="วันที่ประเมินสำหรับทุกคน">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($assessmentMethods as $code => $label)
                                    <label class="border-base-300 bg-base-100 hover:border-primary/40 has-[:checked]:border-primary has-[:checked]:bg-primary/10 has-[:checked]:text-primary flex cursor-pointer items-center gap-1.5 rounded-lg border px-2.5 py-1.5 transition-colors" title="{{ $label }}">
                                        <input class="checkbox checkbox-primary checkbox-xs bulk-assessment-type" type="checkbox" value="{{ $code }}">
                                        <span class="text-xs font-black">{{ $code }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <select class="select select-bordered select-sm w-28" id="bulk-assessment-score" aria-label="คะแนนสำหรับทุกคน">
                                <option value="">คะแนน...</option>
                                <option value="3">3 · ดี</option>
                                <option value="2">2 · พอใช้</option>
                                <option value="1">1 · ปรับปรุง</option>
                            </select>
                            <button class="btn btn-outline btn-primary btn-sm gap-2 rounded-full" type="button" onclick="applyAssessmentToAll()">
                                <i class="fas fa-wand-magic-sparkles"></i>
                                ใช้กับทุกคน
                            </button>
                        </div>
                    </div>
                </div>

                <div id="assessment-list">
                    <div class="text-base-content/40 flex flex-col items-center gap-3 py-14">
                        <i class="fas fa-clipboard-list text-3xl"></i>
                        <p class="max-w-xs text-center text-sm">กด “เริ่มประเมิน” เพื่อโหลดรายชื่อผู้เข้าร่วมล่าสุดจากกำหนดการ</p>
                        <button class="btn btn-primary btn-sm gap-2 rounded-full" type="button" onclick="TrainingAssessment.load()">
                            <i class="fas fa-play"></i>
                            เริ่มประเมิน
                        </button>
                    </div>
                </div>

                <div class="border-base-200 bg-base-200/40 grid gap-3 border-t px-5 py-4 text-[11px] md:grid-cols-2">
                    <div>
                        <div class="text-base-content/60 mb-1 font-bold">วิธีการประเมิน</div>
                        <p class="text-base-content/70 leading-relaxed">P = ฝึกปฏิบัติจริง · O = สังเกตการปฏิบัติงาน · I = ถาม-ตอบ · เลือกได้หลายวิธี เช่น <span class="font-mono font-bold">P+I</span></p>
                    </div>
                    <div>
                        <div class="text-base-content/60 mb-1 font-bold">ระดับคะแนน</div>
                        <p class="text-base-content/70 leading-relaxed">3 = ปฏิบัติงานและแก้ไขปัญหาได้ · 2 = ได้บ้าง · 1 = ยังต้องปรับปรุง</p>
                    </div>
                </div>
            </x-document.section-card>

            <!-- Project actions -->
            <div class="border-base-200 bg-base-100 overflow-hidden rounded-2xl border shadow-sm">
                <div class="border-base-200 flex items-center gap-3 border-b px-5 py-4">
                    <span class="bg-warning/10 text-warning flex size-10 shrink-0 items-center justify-center rounded-xl">
                        <i class="fas fa-flag-checkered"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold">ปิดโครงการ</h3>
                        <p class="text-base-content/50 text-xs">ทำหลังจัดการกำหนดการและบันทึกผลการประเมินแล้ว</p>
                    </div>
                </div>
                <div class="border-warning/25 bg-warning/5 m-4 flex flex-col gap-3 rounded-2xl border p-4 md:m-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="text-sm font-black">จบการฝึกอบรม</div>
                        <p class="text-base-content/55 mt-0.5 text-xs leading-relaxed">บันทึกผลการประเมินให้ครบก่อนปิด — หลังปิดจะแก้ไขการประเมินไม่ได้</p>
                    </div>
                    <button class="btn btn-warning gap-2 rounded-xl md:px-8" type="button" onclick="closeProject()">
                        <i class="fas fa-check-double"></i>
                        จบการฝึกอบรม
                    </button>
                </div>
                <div class="border-base-200 bg-base-200/30 flex flex-wrap items-center justify-between gap-2 border-t px-5 py-3">
                    <p class="text-base-content/40 text-[11px] leading-relaxed">ไม่ต้องการดำเนินการต่อ? การยกเลิกจะยกเลิกเอกสารนี้ทั้งหมด</p>
                    <button class="btn btn-ghost btn-xs text-base-content/50 hover:text-error gap-1.5 rounded-full font-normal" type="button" onclick="cancelTraining()">
                        <i class="fas fa-ban text-[10px]"></i>
                        ยกเลิกโครงการ
                    </button>
                </div>
            </div>
        @endif
    </section>
@endif

@if ($document->status == 'complete')
    <div class="border-success/25 bg-success/5 mt-2 flex flex-col items-center gap-4 rounded-2xl border p-5 md:flex-row md:p-6">
        <span class="bg-success/15 text-success flex size-12 shrink-0 items-center justify-center rounded-xl">
            <i class="fas fa-circle-check text-xl"></i>
        </span>
        <div class="flex-1 text-center md:text-left">
            <h3 class="text-base font-black">การฝึกอบรมเสร็จสมบูรณ์</h3>
            <p class="text-base-content/55 mt-0.5 text-xs leading-relaxed">ดาวน์โหลดเอกสารสรุปผลการฝึกอบรมในรูปแบบ PDF ได้ที่นี่</p>
        </div>
        <a class="btn btn-success w-full gap-2 rounded-xl px-8 md:w-auto" href="{{ route('document.training.downloadPDF', $document->id) }}">
            <i class="fas fa-download"></i>
            ดาวน์โหลด PDF
        </a>
    </div>
@endif

@push('scripts')
    <script>
        function selectedAssessmentTypes(row) {
            return Array.from(row.querySelectorAll('.assessment-type:checked')).map((input) => input.value);
        }

        window.TrainingAssessment = (function() {
            const projectId = '{{ $document->id }}';
            const endpoint = "{{ route('document.training.assessment.participants') }}";
            const methods = @json($assessmentMethods);
            const scoreOptions = [
                ['3', '3 · ดี'],
                ['2', '2 · พอใช้'],
                ['1', '1 · ปรับปรุง'],
            ];
            const today = '{{ date('Y-m-d') }}';

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';
                return String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#39;');
            }

            function methodChips(codes) {
                return Object.entries(methods).map(([code, label]) => `
                    <label class="border-base-300 hover:border-primary/40 has-[:checked]:border-primary has-[:checked]:bg-primary/10 has-[:checked]:text-primary flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-1.5 transition-colors">
                        <input class="checkbox checkbox-primary checkbox-xs assessment-type" type="checkbox" value="${code}" ${codes.includes(code) ? 'checked' : ''}>
                        <span class="text-xs font-black">${code}</span>
                        <span class="text-base-content/60 hidden text-[10px] sm:inline">${escapeHtml(label)}</span>
                    </label>`).join('');
            }

            function scoreSelect(score) {
                const options = scoreOptions
                    .map(([value, label]) => `<option value="${value}" ${score === value ? 'selected' : ''}>${label}</option>`)
                    .join('');

                return `
                    <select class="select select-bordered select-sm focus:select-primary assessment-score w-full">
                        <option value="" ${score ? '' : 'selected'}>เลือก...</option>
                        ${options}
                    </select>`;
            }

            function participantRow(participant) {
                const name = escapeHtml(participant.name);
                const position = participant.position ? `<span>· ${escapeHtml(participant.position)}</span>` : '';

                return `
                    <li class="assessment-row hover:bg-base-200/20 px-5 py-4 transition-colors" data-id="${participant.id}"
                        data-search="${`${name} ${escapeHtml(participant.userid)}`.toLowerCase()}">
                        <div class="flex items-center gap-3">
                            <span class="bg-primary/10 text-primary flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-black">
                                ${name.charAt(0)}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-bold">${name}</div>
                                <div class="text-base-content/45 mt-0.5 flex flex-wrap items-center gap-2 text-[11px]">
                                    <span class="font-mono">${escapeHtml(participant.userid)}</span>
                                    ${position}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 grid gap-3 pl-12 lg:grid-cols-[9rem_minmax(0,1fr)_8rem] lg:items-end">
                            <label class="block">
                                <span class="text-base-content/40 mb-1 block text-[10px] font-bold uppercase tracking-wider">วันที่ประเมิน</span>
                                <input class="input input-bordered input-sm focus:input-primary assessment-date w-full" type="date" value="${participant.assessment_date || today}">
                            </label>

                            <div>
                                <span class="text-base-content/40 mb-1 block text-[10px] font-bold uppercase tracking-wider">วิธีการประเมิน</span>
                                <div class="flex flex-wrap gap-2">${methodChips(participant.assessment_types || [])}</div>
                            </div>

                            <label class="block">
                                <span class="text-base-content/40 mb-1 block text-[10px] font-bold uppercase tracking-wider">คะแนน</span>
                                ${scoreSelect(participant.score || '')}
                            </label>
                        </div>
                    </li>`;
            }

            function toggle(id, visible) {
                const node = document.getElementById(id);
                if (node) node.classList.toggle('hidden', !visible);
            }

            function updateCount() {
                const rows = Array.from(document.querySelectorAll('.assessment-row'));
                const completed = rows.filter((row) =>
                    row.querySelectorAll('.assessment-type:checked').length > 0 && row.querySelector('.assessment-score').value
                ).length;

                const badge = document.getElementById('assessment-count');
                if (!badge) return;

                badge.textContent = `${completed}/${rows.length}`;
                badge.classList.toggle('hidden', rows.length === 0);
            }

            function renderEmpty(message) {
                document.getElementById('assessment-list').innerHTML = `
                    <div class="text-base-content/40 flex flex-col items-center gap-3 py-14">
                        <i class="fas fa-user-slash text-3xl"></i>
                        <p class="max-w-xs text-center text-sm">${escapeHtml(message)}</p>
                        <button class="btn btn-outline btn-primary btn-sm gap-2 rounded-full" type="button" onclick="TrainingAssessment.load()">
                            <i class="fas fa-rotate-right"></i>
                            โหลดใหม่
                        </button>
                    </div>`;

                toggle('assessment-bulk', false);
                toggle('assessment-save-btn', false);
                toggle('assessment-start-btn', false);
                toggle('assessment-reload-btn', false);
                toggle('assessment-count', false);
            }

            function render(participants) {
                if (participants.length === 0) {
                    renderEmpty('ยังไม่มีผู้เข้าร่วมในกำหนดการ กรุณาเพิ่มผู้เข้าร่วมก่อนเริ่มประเมิน');
                    return;
                }

                const searchBar = participants.length > 10 ? `
                    <div class="border-base-200 border-b px-5 py-3">
                        <label class="input input-bordered input-sm flex items-center gap-2">
                            <i class="fas fa-magnifying-glass text-xs opacity-40"></i>
                            <input class="grow" type="search" placeholder="ค้นหาชื่อหรือรหัสพนักงาน" oninput="TrainingAssessment.filter(this)">
                        </label>
                    </div>` : '';

                document.getElementById('assessment-list').innerHTML = `
                    ${searchBar}
                    <ul class="divide-base-200 max-h-[38rem] divide-y overflow-y-auto" id="assessment-rows">${participants.map(participantRow).join('')}</ul>
                    <p class="hidden py-10 text-center text-xs italic opacity-30" id="assessment-no-result">ไม่พบรายชื่อที่ค้นหา</p>`;

                toggle('assessment-bulk', true);
                toggle('assessment-save-btn', true);
                toggle('assessment-reload-btn', true);
                toggle('assessment-start-btn', false);
                updateCount();
            }

            async function load() {
                const list = document.getElementById('assessment-list');
                if (!list) return;

                list.innerHTML = `
                    <div class="text-base-content/30 flex flex-col items-center gap-3 py-14">
                        <span class="loading loading-spinner loading-lg"></span>
                        <p class="text-xs font-bold">กำลังโหลดรายชื่อผู้เข้าร่วม...</p>
                    </div>`;

                try {
                    const {
                        data
                    } = await axios.post(endpoint, {
                        project_id: projectId
                    });

                    if (!data.success) {
                        renderEmpty(data.message || 'ไม่สามารถโหลดรายชื่อผู้เข้าร่วมได้');
                        return;
                    }

                    render(data.participants || []);
                } catch (error) {
                    console.error('Assessment load error:', error);
                    renderEmpty(error.response?.data?.message || 'ไม่สามารถเชื่อมต่อระบบฝึกอบรมได้');
                }
            }

            function filter(input) {
                const term = input.value.trim().toLowerCase();
                let visible = 0;

                document.querySelectorAll('#assessment-rows .assessment-row').forEach((row) => {
                    const matches = term === '' || row.dataset.search.includes(term);
                    row.classList.toggle('hidden', !matches);
                    if (matches) visible += 1;
                });

                const empty = document.getElementById('assessment-no-result');
                if (empty) empty.classList.toggle('hidden', visible > 0);
            }

            return {
                load,
                updateCount,
                filter
            };
        })();

        function applyAssessmentToAll() {
            const rows = document.querySelectorAll('.assessment-row');

            if (rows.length === 0) {
                return;
            }

            const date = document.getElementById('bulk-assessment-date').value;
            const score = document.getElementById('bulk-assessment-score').value;
            const types = Array.from(document.querySelectorAll('.bulk-assessment-type:checked')).map((input) => input.value);

            if (!date && !score && types.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'ยังไม่ได้เลือกค่าที่จะใช้',
                    text: 'กรุณาระบุวันที่ วิธีการประเมิน หรือคะแนน อย่างน้อย 1 อย่าง',
                    showConfirmButton: false,
                    timer: 2200
                });
                return;
            }

            rows.forEach((row) => {
                if (date) {
                    row.querySelector('.assessment-date').value = date;
                }

                if (types.length > 0) {
                    row.querySelectorAll('.assessment-type').forEach((checkbox) => {
                        checkbox.checked = types.includes(checkbox.value);
                    });
                }

                if (score) {
                    row.querySelector('.assessment-score').value = score;
                }
            });

            TrainingAssessment.updateCount();

            Swal.fire({
                icon: 'success',
                title: `ใช้กับผู้เข้าร่วม ${rows.length} คนแล้ว`,
                text: 'อย่าลืมกดบันทึกผลการประเมิน',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true
            });
        }

        async function saveAllAssessments() {
            const rows = document.querySelectorAll('.assessment-row');
            let assessments = {};
            let isValid = true;

            rows.forEach((row) => {
                const id = row.getAttribute('data-id');
                const date = row.querySelector('.assessment-date').value;
                const type = selectedAssessmentTypes(row);
                const score = row.querySelector('.assessment-score').value;

                if (type.length === 0 || !score) {
                    isValid = false;
                }

                assessments[id] = {
                    date,
                    type,
                    score
                };
            });

            if (rows.length === 0) {
                return Swal.fire({
                    icon: 'info',
                    title: 'ยังไม่ได้โหลดรายชื่อ',
                    text: 'กรุณากด "เริ่มประเมิน" เพื่อโหลดรายชื่อผู้เข้าร่วมก่อนบันทึก'
                });
            }

            if (!isValid) {
                const confirmEmpty = await Swal.fire({
                    title: 'ข้อมูลไม่ครบถ้วน',
                    text: 'มีบางรายการยังไม่ได้เลือกวิธีการประเมินหรือคะแนน ต้องการบันทึกส่วนที่เหลือหรือไม่?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'บันทึกเท่าที่มี',
                    cancelButtonText: 'กลับไปแก้ไข',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary px-8 mx-2',
                        cancelButton: 'btn btn-ghost px-8 mx-2'
                    }
                });
                if (!confirmEmpty.isConfirmed) return;
            }

            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            axios.post("{{ route('document.training.saveAssessment') }}", {
                project_id: '{{ $document->id }}',
                assessments: assessments
            }).then((response) => {
                if (response.data.status == "success") {
                    TrainingAssessment.updateCount();
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกผลการประเมินสำเร็จ',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: response.data.message
                    });
                }
            }).catch((err) => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาดในการเชื่อมต่อ'
                });
            });
        }

        async function createTraining() {
            const result = await Swal.fire({
                title: 'สร้างการฝึกอบรม?',
                html: 'ระบบจะสร้างโครงการในระบบ HRD <br>หลังจากนี้คุณยังสามารถแก้ไขวันอบรม ช่วงเวลา และผู้เข้าร่วมได้',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันสร้างโครงการ',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-accent px-10 mx-2',
                    cancelButton: 'btn btn-ghost px-10 mx-2'
                },
            });

            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังดําเนินการ...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                axios.post("{{ route('document.training.createTraining') }}", {
                    project_id: '{{ $document->id }}',
                }).then((response) => {
                    if (response.data.status == "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'สร้างการฝึกอบรมสำเร็จ',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: response.data.message,
                            confirmButtonText: 'ตกลง',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                });
            }
        }

        async function closeProject() {
            const result = await Swal.fire({
                title: 'เสร็จสิ้นการฝึกอบรม?',
                html: 'ต้องการปิดโครงการฝึกอบรมนี้ ใช่หรือไม่? <br><b class="text-error">กรุณาบันทึกผลการประเมิณก่อน<br>หลังจากปิดโครงการจะไม่สามารถประเมินได้อีก</b> ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันปิดโครงการ',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-warning px-10 mx-2',
                    cancelButton: 'btn btn-ghost px-10 mx-2'
                },
            });

            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังดําเนินการ...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                axios.post("{{ route('document.training.closeProject') }}", {
                    project_id: '{{ $document->id }}',
                }).then((response) => {
                    if (response.data.status == "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'เสร็จสิ้นการฝึกอบรมสำเร็จ',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: response.data.message,
                            confirmButtonText: 'ตกลง',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                });
            }
        }

        async function cancelTraining() {
            const result = await Swal.fire({
                title: 'ยกเลิกการฝึกอบรม?',
                html: 'ต้องการยกเลิกการฝึกอบรมนี้ ใช่หรือไม่? <br><b class="text-error">การยกเลิกโครงการ จะเป็นการยกเลิกเอกสารนี้ไปด้วย</b> ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันยกเลิกการฝึกอบรม',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-warning px-10 mx-2',
                    cancelButton: 'btn btn-ghost px-10 mx-2'
                },
            });

            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังดําเนินการ...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                axios.post("{{ route('document.training.cancelTraining') }}", {
                    project_id: '{{ $document->id }}',
                }).then((response) => {
                    if (response.data.status == "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'ยกเลิกการฝึกอบรมสำเร็จ',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: response.data.message,
                            confirmButtonText: 'ตกลง',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                });
            }
        }
    </script>
@endpush
