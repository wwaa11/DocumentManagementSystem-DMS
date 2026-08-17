@php
    $hasTrainingProject = $document->training_id !== null;
    $isAttendanceApprover =
        $hasTrainingProject &&
        $document->status === 'pending' &&
        $document->tasks->where('task_user', auth()->user()->userid)->count() > 0;

    $projectTypeLabel = match ($document->project_type) {
        'single' => 'ลงทะเบียนครั้งเดียว',
        'attendance' => 'เช็คชื่ออย่างเดียว',
        default => 'ลงทะเบียนได้หลายครั้ง',
    };

    $thaiWeekdays = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
    $mentorCount = count($document->mentors);
    $participantCount = count($document->participants);

    $assessmentMethodLabels = [
        'P' => 'ฝึกปฏิบัติจริง',
        'O' => 'สังเกตการปฏิบัติงาน',
        'I' => 'ถาม-ตอบ',
    ];
    $scoreLabels = [
        '3' => ['ดี', 'badge-success'],
        '2' => ['พอใช้', 'badge-warning'],
        '1' => ['ปรับปรุง', 'badge-error'],
    ];

    $canManageTraining =
        $document->status === 'pending' && $document->tasks->where('task_user', auth()->user()->userid)->count() > 0;
    $assessmentRows = $document->participants
        ->sortByDesc(fn ($participant) => filled($participant->assetment_type) || filled($participant->score))
        ->unique('participant')
        ->sortBy('id')
        ->values();
    $assessedCount = $assessmentRows
        ->filter(fn ($participant) => filled($participant->assetment_type) && filled($participant->score))
        ->count();
    $showAssessmentResults = ! $canManageTraining && ($assessedCount > 0 || $document->status === 'complete');
@endphp

<div class="card-body space-y-6 p-5 md:p-8">
    <div class="no-print flex flex-wrap items-center justify-between gap-3">
        <x-ui.back-button variant="button" />
        <div class="flex flex-wrap items-center gap-2">
            <x-document.status-badge class="badge-sm font-bold" :status="$document->status" />
            @if ($hasTrainingProject)
                <span class="badge badge-sm badge-ghost gap-1 font-mono font-bold">
                    <i class="fas fa-link text-[9px] opacity-50"></i>HRD #{{ $document->training_id }}
                </span>
            @endif
        </div>
    </div>

    <!-- Hero -->
    <header class="border-base-200 from-primary/10 via-base-100 to-base-100 relative overflow-hidden rounded-2xl border bg-gradient-to-br p-5 md:p-7">
        <i class="fas fa-graduation-cap text-primary/5 pointer-events-none absolute -right-4 -top-6 text-9xl"></i>

        <div class="relative flex flex-col gap-5 md:flex-row md:items-center">
            <img class="h-12 w-auto self-start md:h-14" src="{{ asset('images/Side Logo.png') }}" alt="Logo">

            <div class="min-w-0 flex-1">
                <p class="text-primary/70 text-[11px] font-bold uppercase tracking-[0.18em]">
                    {{ $document->course_plan_item_id ? 'ใบบันทึกฝึกอบรมตามแผนหลักสูตร' : 'ใบบันทึกฝึกอบรมนอกแผน' }}
                </p>
                <h1 class="mt-1.5 text-xl font-black leading-snug md:text-2xl">{{ $document->title }}</h1>
                <div class="text-base-content/50 mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-hashtag opacity-50"></i>เอกสารเลขที่ {{ $document->id }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-calendar-day opacity-50"></i>สร้างเมื่อ {{ $document->created_at->format('d M Y') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-tag opacity-50"></i>{{ $projectTypeLabel }}
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Key facts -->
    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['ผู้ขอเอกสาร', $document->creator->name, 'fa-user'], ['แผนก', $document->creator->department ?? '—', 'fa-building'], ['วิทยากร / ผู้เข้าร่วม', $mentorCount.' / '.$participantCount.' คน', 'fa-users'], ['เวลารวมทั้งหมด', $document->hours.' ชม. '.$document->minutes.' น.', 'fa-hourglass-half']] as [$label, $value, $icon])
            <div class="border-base-200 bg-base-200/40 rounded-xl border px-4 py-3">
                <dt class="text-base-content/40 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider">
                    <i class="fas {{ $icon }}"></i>{{ $label }}
                </dt>
                <dd class="mt-1 truncate text-sm font-bold" title="{{ $value }}">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>

    <!-- Course information -->
    <x-document.section-card title="รายละเอียดหลักสูตร" subtitle="ข้อมูลหลักสูตรและที่มาของการฝึกอบรม" icon="fas fa-circle-info" tone="primary">
        <dl class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <dt class="text-base-content/40 text-[10px] font-bold uppercase tracking-wider">ชื่อหลักสูตร</dt>
                <dd class="mt-1 text-base font-bold leading-snug">{{ $document->title }}</dd>
            </div>
            <div>
                <dt class="text-base-content/40 text-[10px] font-bold uppercase tracking-wider">ประเภทการลงทะเบียน</dt>
                <dd class="mt-1.5">
                    <span class="badge badge-primary badge-outline badge-sm font-bold">{{ $projectTypeLabel }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-base-content/40 text-[10px] font-bold uppercase tracking-wider">จำนวนวันอบรม</dt>
                <dd class="mt-1 text-sm font-bold">{{ count($document->dates) }} วัน</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-base-content/40 text-[10px] font-bold uppercase tracking-wider">ที่มาของการอบรม</dt>
                <dd class="bg-base-200/50 mt-1.5 rounded-xl px-4 py-3 text-sm leading-relaxed">{{ $document->detail }}</dd>
            </div>
        </dl>
    </x-document.section-card>

    <!-- Attachments -->
    <x-document.section-card
        title="ไฟล์แนบประกอบ"
        subtitle="เอกสารและหลักฐานประกอบการอบรม"
        icon="fas fa-paperclip"
        tone="neutral"
        :count="$document->files->count()"
    >
        @if ($document->files->count() > 0)
            @include('document.files', ['files' => $document->files])
        @else
            <div class="text-base-content/30 flex flex-col items-center gap-2 py-6">
                <i class="fas fa-folder-open text-2xl"></i>
                <p class="text-xs">ไม่มีไฟล์แนบประกอบการอบรม</p>
            </div>
        @endif
    </x-document.section-card>

    <!-- Schedule -->
    <x-document.section-card
        title="กำหนดการฝึกอบรม"
        subtitle="วันและช่วงเวลาที่จัดอบรม"
        icon="fas fa-calendar-days"
        tone="info"
        :count="count($document->dates).' วัน'"
        flush
    >
        @if (count($document->dates) > 0)
            <ol class="divide-base-200 divide-y">
                @foreach ($document->dates as $index => $trainingDate)
                    @php
                        $startTime = \Carbon\Carbon::parse($trainingDate->start_time);
                        $endTime = \Carbon\Carbon::parse($trainingDate->end_time);
                        $durationMinutes = max(0, $startTime->diffInMinutes($endTime));
                    @endphp
                    <li class="flex flex-wrap items-center gap-x-4 gap-y-2 px-5 py-3.5">
                        <span class="bg-info/10 text-info flex size-8 shrink-0 items-center justify-center rounded-lg text-xs font-black">
                            {{ $index + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-bold">{{ $trainingDate->date->format('d M Y') }}</div>
                            <div class="text-base-content/45 text-[11px]">วัน{{ $thaiWeekdays[$trainingDate->date->dayOfWeek] }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono text-sm font-bold">{{ $startTime->format('H:i') }} - {{ $endTime->format('H:i') }}</div>
                            <div class="text-base-content/45 text-[11px]">
                                {{ intdiv($durationMinutes, 60) }} ชม.{{ $durationMinutes % 60 ? ' '.($durationMinutes % 60).' น.' : '' }}
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
            <div class="border-base-200 bg-base-200/40 flex items-center justify-between border-t px-5 py-3">
                <span class="text-base-content/50 text-xs font-bold uppercase tracking-wider">เวลารวมทั้งหมด</span>
                <span class="text-info text-base font-black">{{ $document->hours }} ชม. {{ $document->minutes }} น.</span>
            </div>
        @else
            <div class="text-base-content/30 flex flex-col items-center gap-2 py-10">
                <i class="fas fa-calendar-xmark text-2xl"></i>
                <p class="text-xs">ยังไม่มีกำหนดการฝึกอบรม</p>
            </div>
        @endif
    </x-document.section-card>

    <!-- Mentors -->
    <x-document.section-card
        title="วิทยากร"
        subtitle="ผู้ถ่ายทอดความรู้ในหลักสูตรนี้"
        icon="fas fa-chalkboard-user"
        tone="secondary"
        :count="$mentorCount.' คน'"
        flush
    >
        @if ($mentorCount > 0)
            @if ($mentorCount > 10)
                <div class="border-base-200 border-b px-5 py-3">
                    <label class="input input-bordered input-sm flex items-center gap-2">
                        <i class="fas fa-magnifying-glass text-xs opacity-40"></i>
                        <input class="grow" type="search" placeholder="ค้นหาวิทยากร" oninput="filterPeopleList(this, 'mentor-list')">
                    </label>
                </div>
            @endif
            <ul class="divide-base-200 max-h-96 divide-y overflow-y-auto sm:grid sm:grid-cols-2 sm:divide-y-0 sm:gap-px sm:bg-base-200/60" id="mentor-list">
                @foreach ($document->mentors as $mentor)
                    <li class="bg-base-100 hover:bg-base-200/30 flex items-center gap-3 px-5 py-3 transition-colors"
                        data-search="{{ mb_strtolower($mentor->mentor_name.' '.$mentor->mentor) }}">
                        <span class="bg-secondary/10 text-secondary flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-black">
                            {{ mb_substr($mentor->mentor_name, 0, 1) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-bold">{{ $mentor->mentor_name }}</div>
                            <div class="text-base-content/45 truncate text-[11px]">{{ $mentor->mentor_position }}</div>
                        </div>
                        <span class="badge badge-ghost badge-sm shrink-0 font-mono">{{ $mentor->mentor }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="hidden py-8 text-center text-xs italic opacity-30" data-empty-for="mentor-list">ไม่พบวิทยากรที่ค้นหา</p>
        @else
            <div class="text-base-content/30 flex flex-col items-center gap-2 py-10">
                <i class="fas fa-user-slash text-2xl"></i>
                <p class="text-xs">ไม่มีข้อมูลวิทยากร</p>
            </div>
        @endif
    </x-document.section-card>

    <!-- Participants & attendance -->
    <x-document.section-card
        title="ผู้เข้าร่วมและการเช็คอิน"
        :subtitle="$hasTrainingProject ? 'ข้อมูลเช็คอินจากระบบ HRD แบบเรียลไทม์' : 'รายชื่อผู้เข้าร่วมตามเอกสาร'"
        icon="fas fa-user-check"
        tone="accent"
        :count="$hasTrainingProject ? null : $participantCount.' คน'"
        flush
    >
        @if ($hasTrainingProject)
            <x-slot:actions>
                <span class="badge badge-accent badge-sm gap-1 font-bold">
                    <span class="status status-accent animate-pulse"></span>
                    Live
                </span>
                @if ($isAttendanceApprover)
                    <button class="btn btn-outline btn-accent btn-sm gap-2 rounded-full" type="button" id="approve-selected-btn" disabled onclick="approveSelectedAttendance()">
                        <i class="fas fa-check"></i>
                        อนุมัติที่เลือก (<span id="selected-approve-count">0</span>)
                    </button>
                    <button class="btn btn-accent btn-sm gap-2 rounded-full" type="button" onclick="approveAllAttendance()">
                        <i class="fas fa-check-double"></i>
                        อนุมัติทั้งหมด
                    </button>
                @endif
            </x-slot:actions>

            <div class="border-base-200 bg-base-200/30 grid grid-cols-3 divide-x divide-base-200 border-b">
                @foreach ([['ทั้งหมด', 'total', ''], ['เช็คอินแล้ว', 'checked', 'text-info'], ['อนุมัติแล้ว', 'approved', 'text-success']] as [$label, $key, $tone])
                    <div class="px-5 py-3 text-center">
                        <div class="text-base-content/40 text-[10px] font-bold uppercase tracking-wider">{{ $label }}</div>
                        <div class="{{ $tone }} text-lg font-black leading-tight" data-attendance-stat="{{ $key }}">—</div>
                    </div>
                @endforeach
            </div>

            <div class="border-base-200 flex flex-wrap items-center gap-2 border-b px-5 py-3">
                <label class="input input-bordered input-sm flex min-w-52 flex-1 items-center gap-2">
                    <i class="fas fa-magnifying-glass text-xs opacity-40"></i>
                    <input class="grow" type="search" id="attendance-search" placeholder="ค้นหาชื่อหรือรหัสพนักงาน" oninput="filterAttendance()">
                </label>
                <div class="join">
                    @foreach ([['all', 'ทั้งหมด'], ['pending', 'รออนุมัติ'], ['approved', 'อนุมัติแล้ว'], ['absent', 'ยังไม่เช็คอิน']] as [$value, $label])
                        <button class="btn btn-sm join-item {{ $value === 'all' ? 'btn-active' : '' }}" type="button" data-attendance-filter="{{ $value }}" onclick="setAttendanceFilter(this)">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div class="max-h-[32rem] overflow-auto">
                <table class="table-pin-rows table w-full">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/50">
                            @if ($isAttendanceApprover)
                                <th class="w-12 text-center">
                                    <input class="checkbox checkbox-accent checkbox-sm" type="checkbox" id="attendance-select-all" title="เลือกทั้งหมดที่อนุมัติได้" onchange="toggleSelectAllAttendance(this)">
                                </th>
                            @endif
                            <th class="text-[11px] font-bold uppercase tracking-wider">ผู้เข้าร่วม</th>
                            <th class="text-[11px] font-bold uppercase tracking-wider">เวลาเช็คอิน</th>
                            <th class="text-right text-[11px] font-bold uppercase tracking-wider">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-table">
                        <tr>
                            <td class="py-12" colspan="{{ $isAttendanceApprover ? 4 : 3 }}">
                                <div class="text-base-content/30 flex flex-col items-center gap-3">
                                    <span class="loading loading-spinner loading-lg"></span>
                                    <p class="text-xs font-bold">กำลังดึงข้อมูลการเช็คอิน...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @elseif ($participantCount > 0)
            @if ($participantCount > 10)
                <div class="border-base-200 border-b px-5 py-3">
                    <label class="input input-bordered input-sm flex items-center gap-2">
                        <i class="fas fa-magnifying-glass text-xs opacity-40"></i>
                        <input class="grow" type="search" placeholder="ค้นหาชื่อหรือรหัสพนักงาน" oninput="filterPeopleList(this, 'participant-list')">
                    </label>
                </div>
            @endif
            <div class="max-h-[32rem] overflow-auto">
                <table class="table-pin-rows table w-full">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/50">
                            <th class="text-[11px] font-bold uppercase tracking-wider">ผู้เข้าร่วม</th>
                            <th class="text-[11px] font-bold uppercase tracking-wider">ตำแหน่ง</th>
                            <th class="text-[11px] font-bold uppercase tracking-wider">แผนก</th>
                        </tr>
                    </thead>
                    <tbody id="participant-list">
                        @foreach ($document->participants as $participant)
                            <tr class="hover:bg-base-200/30 transition-colors"
                                data-search="{{ mb_strtolower($participant->participant_name.' '.$participant->participant) }}">
                                <td>
                                    <div class="text-sm font-bold">{{ $participant->participant_name }}</div>
                                    <div class="text-base-content/45 font-mono text-[11px]">{{ $participant->participant }}</div>
                                </td>
                                <td class="text-base-content/70 text-sm">{{ $participant->participant_position }}</td>
                                <td class="text-base-content/70 text-sm">{{ $participant->participant_department }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="hidden py-8 text-center text-xs italic opacity-30" data-empty-for="participant-list">ไม่พบรายชื่อที่ค้นหา</p>
        @else
            <div class="text-base-content/30 flex flex-col items-center gap-2 py-10">
                <i class="fas fa-users-slash text-2xl"></i>
                <p class="text-xs">ไม่มีข้อมูลผู้เข้าร่วม</p>
            </div>
        @endif
    </x-document.section-card>

    <!-- Assessment results -->
    @if ($showAssessmentResults)
        <x-document.section-card
            title="ผลการประเมินผู้เข้าร่วม"
            subtitle="วิธีการประเมินและคะแนนที่บันทึกไว้"
            icon="fas fa-clipboard-check"
            tone="success"
            :count="$assessedCount.'/'.$assessmentRows->count()"
            flush
        >
            @if ($assessmentRows->isNotEmpty())
                <div class="max-h-[32rem] overflow-auto">
                    <table class="table-pin-rows table w-full">
                        <thead>
                            <tr class="bg-base-200/50 text-base-content/50">
                                <th class="text-[11px] font-bold uppercase tracking-wider">ผู้เข้าร่วม</th>
                                <th class="text-[11px] font-bold uppercase tracking-wider">วันที่ประเมิน</th>
                                <th class="text-[11px] font-bold uppercase tracking-wider">วิธีการประเมิน</th>
                                <th class="text-right text-[11px] font-bold uppercase tracking-wider">คะแนน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assessmentRows as $participant)
                                @php
                                    $resultTypes = collect(preg_split('/[+,|\\s]+/', (string) $participant->assetment_type, -1, PREG_SPLIT_NO_EMPTY))
                                        ->map(fn ($value) => strtoupper(trim($value)))
                                        ->filter()
                                        ->values()
                                        ->all();
                                    $scoreKey = (string) $participant->score;
                                    $scoreBadge = $scoreLabels[$scoreKey] ?? null;
                                @endphp
                                <tr class="hover:bg-base-200/30 transition-colors">
                                    <td>
                                        <div class="text-sm font-bold">{{ $participant->participant_name }}</div>
                                        <div class="text-base-content/45 font-mono text-[11px]">{{ $participant->participant }}</div>
                                    </td>
                                    <td class="text-sm">
                                        @if ($participant->assetment_date)
                                            {{ $participant->assetment_date->format('d M Y') }}
                                        @else
                                            <span class="text-base-content/25">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($resultTypes)
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach ($resultTypes as $code)
                                                    <span class="badge badge-sm badge-ghost font-bold" title="{{ $assessmentMethodLabels[$code] ?? $code }}">{{ $code }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-base-content/25">ยังไม่ประเมิน</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if ($scoreBadge)
                                            <span class="badge badge-sm {{ $scoreBadge[1] }} font-bold">{{ $scoreKey }} · {{ $scoreBadge[0] }}</span>
                                        @else
                                            <span class="text-base-content/25">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-base-200 bg-base-200/40 border-t px-5 py-3 text-[11px]">
                    <p class="text-base-content/70 leading-relaxed">
                        P = ฝึกปฏิบัติจริง · O = สังเกตการปฏิบัติงาน · I = ถาม-ตอบ · คะแนน 3 = ดี, 2 = พอใช้, 1 = ปรับปรุง
                    </p>
                </div>
            @else
                <div class="text-base-content/30 flex flex-col items-center gap-2 py-10">
                    <i class="fas fa-clipboard text-2xl"></i>
                    <p class="text-xs">ไม่มีข้อมูลผลการประเมิน</p>
                </div>
            @endif
        </x-document.section-card>
    @endif

    

    <div class="no-print">
        @include('document.training.management')
    </div>

    <!-- Approval chain -->
    <div class="pt-2">
        <div class="divider my-8 text-[10px] font-bold uppercase tracking-widest opacity-30">สถานะและประวัติการอนุมัติ</div>
        @include('document.tasks', ['tasks' => $document->tasks])
    </div>
</div>

@push('scripts')
    <script>
        function filterPeopleList(input, listId) {
            const list = document.getElementById(listId);
            if (!list) return;

            const term = input.value.trim().toLowerCase();
            let visible = 0;

            list.querySelectorAll('[data-search]').forEach((item) => {
                const matches = term === '' || item.dataset.search.includes(term);
                item.classList.toggle('hidden', !matches);
                if (matches) visible += 1;
            });

            const empty = document.querySelector(`[data-empty-for="${listId}"]`);
            if (empty) empty.classList.toggle('hidden', visible > 0);
        }
    </script>

    @if ($document->training_id != null)
        <script>
            const attendanceState = {
                isApprovable: {{ $isAttendanceApprover ? 'true' : 'false' }},
                columnCount: {{ $isAttendanceApprover ? 4 : 3 }},
            };

            document.addEventListener("DOMContentLoaded", function() {
                fetchAttendanceData();
            });

            function fetchAttendanceData() {
                const projectId = '{{ $document->id }}';

                axios.post("{{ route('document.training.getAttendance') }}", {
                        project_id: projectId,
                    })
                    .then((response) => {
                        if (response.data.success) {
                            renderAttendanceTable(response.data.transaction, attendanceState.isApprovable);
                        } else {
                            handleApiError(response.data.message);
                        }
                    })
                    .catch((error) => {
                        console.error("Attendance Error:", error);
                        handleApiError("Failed to fetch attendance records. Please try again later.");
                    });
            }

            function renderAttendanceTable(transactions, isApprovable) {
                const container = document.querySelector("#attendance-table");
                const colSpan = attendanceState.columnCount;
                const summary = {
                    total: 0,
                    checked: 0,
                    approved: 0
                };
                let html = "";

                if (!transactions || Object.keys(transactions).length === 0) {
                    container.innerHTML =
                        `<tr><td colspan="${colSpan}" class="py-16 text-center text-xs text-base-content/30">ไม่พบข้อมูลการเช็คอินในระบบหลัก</td></tr>`;
                    updateAttendanceSummary(summary);
                    updateSelectedCount();
                    return;
                }

                let sessionIndex = 0;

                for (const date in transactions) {
                    for (const time in transactions[date]) {
                        const rows = transactions[date][time];
                        sessionIndex += 1;
                        html += sessionHeaderRow(date, time, rows, colSpan, sessionIndex);

                        rows.forEach((user) => {
                            summary.total += 1;
                            if (user.attend_datetime) summary.checked += 1;
                            if (user.approve_datetime) summary.approved += 1;
                            html += generateUserRow(user, isApprovable, sessionIndex);
                        });
                    }
                }

                html += `
                    <tr class="hidden" id="attendance-no-result">
                        <td colspan="${colSpan}" class="text-base-content/30 py-12 text-center text-xs">ไม่พบรายชื่อที่ตรงกับเงื่อนไข</td>
                    </tr>`;

                container.innerHTML = html;
                updateAttendanceSummary(summary);
                filterAttendance();
            }

            function attendanceStatus(user) {
                if (user.approve_datetime) return 'approved';
                if (user.attend_datetime) return 'pending';

                return 'absent';
            }

            function setAttendanceFilter(button) {
                document.querySelectorAll('[data-attendance-filter]').forEach((node) => {
                    node.classList.toggle('btn-active', node === button);
                });
                filterAttendance();
            }

            function filterAttendance() {
                const searchInput = document.getElementById('attendance-search');
                if (!searchInput) return;

                const term = searchInput.value.trim().toLowerCase();
                const activeFilter = document.querySelector('[data-attendance-filter].btn-active');
                const status = activeFilter ? activeFilter.dataset.attendanceFilter : 'all';
                const visiblePerSession = {};
                let visible = 0;

                document.querySelectorAll('#attendance-table [data-session-of]').forEach((row) => {
                    const matchesTerm = term === '' || row.dataset.search.includes(term);
                    const matchesStatus = status === 'all' || row.dataset.status === status;
                    const matches = matchesTerm && matchesStatus;

                    row.classList.toggle('hidden', !matches);

                    if (matches) {
                        visible += 1;
                        visiblePerSession[row.dataset.sessionOf] = true;
                        return;
                    }

                    const checkbox = row.querySelector('.attendance-check');
                    if (checkbox) checkbox.checked = false;
                });

                document.querySelectorAll('#attendance-table [data-session]').forEach((header) => {
                    header.classList.toggle('hidden', !visiblePerSession[header.dataset.session]);
                });

                const emptyRow = document.getElementById('attendance-no-result');
                if (emptyRow) emptyRow.classList.toggle('hidden', visible > 0);

                updateSelectedCount();
            }

            function sessionHeaderRow(date, time, rows, colSpan, sessionIndex) {
                const checkedIn = rows.filter((user) => user.attend_datetime).length;

                return `
                    <tr class="bg-base-200/70" data-session="${sessionIndex}">
                        <td colspan="${colSpan}" class="py-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="flex items-center gap-2 text-xs font-bold">
                                    <i class="fas fa-calendar-day text-accent/60"></i>${date}
                                    <span class="font-mono opacity-50">${time}</span>
                                </span>
                                <span class="text-[11px] text-base-content/50">เช็คอิน ${checkedIn}/${rows.length}</span>
                            </div>
                        </td>
                    </tr>`;
            }

            function updateAttendanceSummary(summary) {
                Object.entries(summary).forEach(([key, value]) => {
                    const node = document.querySelector(`[data-attendance-stat="${key}"]`);
                    if (node) node.textContent = value;
                });
            }

            function generateUserRow(user, isApprovable, sessionIndex) {
                const canApprove = user.attend_datetime != null && user.approve_datetime == null;
                const statusHtml = generateStatusHtml(user, canApprove, isApprovable);
                const timeHtml = user.attend_datetime ?
                    `<span class="font-mono text-sm font-bold text-info">${user.attend_datetime}</span>` :
                    `<span class="text-xs text-base-content/30">ยังไม่เช็คอิน</span>`;

                const checkboxCell = isApprovable ? `
                    <td class="text-center">
                        ${canApprove
                            ? `<input class="checkbox checkbox-accent checkbox-sm attendance-check" type="checkbox" value="${user.id}" data-userid="${user.userid}" onchange="updateSelectedCount()">`
                            : `<span class="text-base-content/15">—</span>`}
                    </td>` : '';

                return `
                    <tr class="hover:bg-base-200/30 transition-colors" data-transaction-id="${user.id}"
                        data-session-of="${sessionIndex}" data-status="${attendanceStatus(user)}"
                        data-search="${`${user.name} ${user.userid}`.toLowerCase()}">
                        ${checkboxCell}
                        <td>
                            <div class="text-sm font-bold">${user.name}</div>
                            <div class="font-mono text-[11px] text-base-content/45">${user.userid}</div>
                        </td>
                        <td>${timeHtml}</td>
                        <td class="text-right">${statusHtml}</td>
                    </tr>
                `;
            }

            function generateStatusHtml(user, canApprove, isApprovable) {
                if (canApprove && isApprovable) {
                    return `
                        <button class='btn btn-accent btn-xs gap-1 rounded-full px-4' onclick='approveAttendance("${user.id}", "${user.userid}")'>
                            <i class="fas fa-check"></i> อนุมัติ
                        </button>`;
                }

                if (user.approve_datetime) {
                    return `
                        <span class="badge badge-success badge-sm gap-1 font-mono text-[10px] font-bold">
                            <i class="fas fa-circle-check text-[9px]"></i>${user.approve_datetime}
                        </span>`;
                }

                if (canApprove) {
                    return `<span class="badge badge-info badge-sm badge-outline font-bold">รออนุมัติ</span>`;
                }

                return `<span class="text-base-content/15">—</span>`;
            }

            function selectedTransactionIds() {
                return Array.from(document.querySelectorAll('.attendance-check:checked'))
                    .map((checkbox) => Number(checkbox.value))
                    .filter((id) => id > 0);
            }

            function updateSelectedCount() {
                const selected = selectedTransactionIds().length;
                const countNode = document.getElementById('selected-approve-count');
                const button = document.getElementById('approve-selected-btn');
                const selectAll = document.getElementById('attendance-select-all');
                const available = visibleAttendanceCheckboxes().length;

                if (countNode) {
                    countNode.textContent = String(selected);
                }

                if (button) {
                    button.disabled = selected === 0;
                }

                if (selectAll) {
                    selectAll.checked = available > 0 && selected === available;
                    selectAll.indeterminate = selected > 0 && selected < available;
                }
            }

            function visibleAttendanceCheckboxes() {
                return Array.from(document.querySelectorAll('.attendance-check'))
                    .filter((checkbox) => !checkbox.closest('tr').classList.contains('hidden'));
            }

            function toggleSelectAllAttendance(source) {
                visibleAttendanceCheckboxes().forEach((checkbox) => {
                    checkbox.checked = source.checked;
                });
                updateSelectedCount();
            }

            function handleApiError(message) {
                Swal.fire({
                    position: "top-end",
                    icon: "error",
                    title: "System Notification",
                    text: message,
                    showConfirmButton: false,
                    timer: 2000
                });
            }

            function showApproveResult(response) {
                const approved = response.approved?.length || 0;
                const skipped = response.skipped?.length || 0;
                const failed = response.failed?.length || 0;
                const ok = response.success !== false && failed === 0;

                Swal.fire({
                    icon: ok ? 'success' : 'warning',
                    title: ok ? 'อนุมัติสำเร็จ' : 'อนุมัติบางส่วน',
                    html: `สำเร็จ <b>${approved}</b> · ข้าม <b>${skipped}</b> · ไม่สำเร็จ <b>${failed}</b>`,
                    showConfirmButton: false,
                    timer: 1800
                }).then(() => window.location.reload());
            }

            function postApprove(payload, loadingTitle) {
                Swal.fire({
                    title: loadingTitle,
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                return axios.post("{{ route('document.training.approveAttendance') }}", {
                        project_id: '{{ $document->id }}',
                        ...payload,
                    })
                    .then((response) => {
                        if (response.data.success === false && !(response.data.approved || []).length) {
                            handleApiError(response.data.message || 'อนุมัติไม่สำเร็จ');
                            return;
                        }

                        showApproveResult(response.data);
                    })
                    .catch((error) => {
                        console.error("Approval Error:", error);
                        handleApiError(error.response?.data?.message || "Failed to process approval. Please contact administrator.");
                    });
            }

            function approveAttendance(id, userid) {
                Swal.fire({
                    title: 'ยืนยันการอนุมัติเข้าร่วม?',
                    text: `ต้องการอนุมัติการเข้าร่วมอบรมของพนักงานรหัส ${userid}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันอนุมัติ',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-accent px-8 mx-2',
                        cancelButton: 'btn btn-ghost px-8 mx-2'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        postApprove({
                            transaction_id: id,
                            userid
                        }, 'กำลังอนุมัติ...');
                    }
                });
            }

            function approveSelectedAttendance() {
                const ids = selectedTransactionIds();

                if (ids.length === 0) {
                    return handleApiError('กรุณาเลือกรายการที่ต้องการอนุมัติ');
                }

                Swal.fire({
                    title: 'อนุมัติรายการที่เลือก?',
                    text: `ต้องการอนุมัติ ${ids.length} รายการพร้อมกัน`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันอนุมัติ',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-accent px-8 mx-2',
                        cancelButton: 'btn btn-ghost px-8 mx-2'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        postApprove({
                            transaction_ids: ids
                        }, `กำลังอนุมัติ ${ids.length} รายการ...`);
                    }
                });
            }

            function approveAllAttendance() {
                Swal.fire({
                    title: 'อนุมัติทั้งหมดที่เช็คอินแล้ว?',
                    html: 'ระบบจะอนุมัติทุกคนที่เช็คอินแล้วและยังไม่อนุมัติในโครงการนี้<br><span class="text-xs opacity-60">รายการที่อนุมัติแล้วจะถูกข้ามอัตโนมัติ</span>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'อนุมัติทั้งหมด',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-accent px-8 mx-2',
                        cancelButton: 'btn btn-ghost px-8 mx-2'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        postApprove({
                            approve_all: true
                        }, 'กำลังอนุมัติทั้งโครงการ...');
                    }
                });
            }
        </script>
    @endif
@endpush
