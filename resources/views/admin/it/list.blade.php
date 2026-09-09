@extends('layouts.app')
@section('content')
    <x-admin.list-header>
        @if ($action == 'approve')
            <div class="mb-3 text-end">
                <button class="btn btn-primary" type="button" onclick="approveAllDocuments()">อนุมัติเอกสารทั้งหมด</button>
            </div>
        @elseif ($action == 'all')
            <div>
                {{ $documents->links() }}
            </div>
        @endif
        <div class="border-base-content/5 bg-base-100 overflow-x-auto rounded-lg border">
            @if (in_array($action, ['all', 'new', 'my', 'borrow']))
                @php
                    $listRoute = match ($action) {
                        'all' => route('admin.it.alllist'),
                        'new' => route('admin.it.newlist'),
                        'my' => route('admin.it.mylist'),
                        'borrow' => route('admin.it.borrowlist'),
                    };
                    $hasAdvancedFilters = ($action === 'all' && filled($process_log ?? null))
                        || ($action !== 'borrow' && isset($type) && $type !== 'ALL')
                        || filled($subtype ?? null)
                        || (in_array($action, ['all', 'my', 'borrow'], true) && filled($status ?? null))
                        || filled($department ?? null)
                        || ($action === 'all' && filled($process_userid ?? null))
                        || filled($start_date ?? null)
                        || filled($end_date ?? null);
                    $advancedFilterCount = collect([
                        $action === 'all' && filled($process_log ?? null),
                        $action !== 'borrow' && isset($type) && $type !== 'ALL',
                        filled($subtype ?? null),
                        in_array($action, ['all', 'my', 'borrow'], true) && filled($status ?? null),
                        filled($department ?? null),
                        $action === 'all' && filled($process_userid ?? null),
                        filled($start_date ?? null),
                        filled($end_date ?? null),
                    ])->filter()->count();
                @endphp
                <div class="border-base-content/5 bg-base-200/30 border-b px-4 py-3">
                    <form action="{{ $listRoute }}" method="GET">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                            <div class="form-control min-w-0 flex-1">
                                <label class="label pt-0" for="it-admin-search">
                                    <span class="label-text text-xs font-semibold">ค้นหา</span>
                                </label>
                                <input
                                    class="input input-bordered input-sm w-full"
                                    id="it-admin-search"
                                    type="search"
                                    name="search"
                                    value="{{ $search ?? '' }}"
                                    placeholder="เลขที่, ชื่อเอกสาร, รายละเอียด..."
                                >
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button class="btn btn-primary btn-sm px-8" type="submit">
                                    <i class="fas fa-search mr-1"></i> ค้นหา
                                </button>
                                <button
                                    class="btn btn-ghost btn-sm border-base-content/20 gap-2 px-4"
                                    type="button"
                                    data-admin-filter-toggle
                                    id="it-admin-filters-toggle"
                                    aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}"
                                    aria-controls="it-admin-advanced-filters"
                                >
                                    <i class="fas fa-sliders-h"></i>
                                    ตัวกรอง
                                    @if ($advancedFilterCount > 0)
                                        <span class="badge badge-primary badge-sm">{{ $advancedFilterCount }}</span>
                                    @endif
                                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 {{ $hasAdvancedFilters ? 'rotate-180' : '' }}" data-filter-chevron></i>
                                </button>
                                @if ($action == 'all')
                                    <a
                                        class="btn btn-success btn-sm gap-2 px-4 text-success-content"
                                        href="{{ route('admin.it.alllist.export', request()->query()) }}"
                                    >
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </a>
                                @endif
                                <a class="btn btn-ghost btn-sm border-base-content/20 px-4" href="{{ $listRoute }}">
                                    <i class="fas fa-redo mr-1"></i> ล้างค่า
                                </a>
                            </div>
                        </div>

                        <div
                            class="{{ $hasAdvancedFilters ? '' : 'hidden' }} mt-4 border-t border-base-200/70 pt-4"
                            id="it-admin-advanced-filters"
                        >
                            <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-4">
                        @if ($action == 'all')
                            <div class="form-control col-span-1 ">
                                <label class="label pt-0"><span class="label-text text-xs font-semibold">รายการดำเนินงาน</span></label>
                                <input class="input input-bordered input-sm w-full" type="text" name="process_log" value="{{ $process_log ?? '' }}" placeholder="ค้นหาบันทึกการดำเนินการ...">
                            </div>
                        @endif
                        @if ($action !== 'borrow')
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">ประเภท</span></label>
                            <select class="select select-bordered select-sm w-full" id="type-filter" name="type">
                                <option value="ALL" {{ ! isset($type) || $type == 'ALL' ? 'selected' : '' }}>ทั้งหมด</option>
                                <option value="IT" {{ isset($type) && $type == 'IT' ? 'selected' : '' }}>แจ้งงาน/สนับสนุน</option>
                                <option value="USER" {{ isset($type) && $type == 'USER' ? 'selected' : '' }}>ขอสิทธิใช้งาน</option>
                                @if ($action == 'all')
                                    <option value="BORROW" {{ isset($type) && $type == 'BORROW' ? 'selected' : '' }}>ยืม/คืนอุปกรณ์</option>
                                @endif
                            </select>
                        </div>
                        @endif
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">ประเภทย่อย</span></label>
                            @if ($action === 'borrow')
                                <select class="select select-bordered select-sm w-full" name="subtype">
                                    <option value="">ทั้งหมด</option>
                                    @foreach ($documentSubtypes['BORROW'] ?? [] as $subtypeValue => $subtypeLabel)
                                        <option value="{{ $subtypeValue }}" {{ isset($subtype) && $subtype == $subtypeValue ? 'selected' : '' }}>{{ $subtypeLabel }}</option>
                                    @endforeach
                                </select>
                            @else
                            <select class="select select-bordered select-sm w-full" id="subtype-filter" name="subtype" {{ ! isset($type) || $type == 'ALL' ? 'disabled' : '' }}>
                                <option value="">ทั้งหมด</option>
                                @if (isset($type) && $type !== 'ALL' && isset($documentSubtypes[$type]))
                                    @foreach ($documentSubtypes[$type] as $subtypeValue => $subtypeLabel)
                                        <option value="{{ $subtypeValue }}" {{ isset($subtype) && $subtype == $subtypeValue ? 'selected' : '' }}>{{ $subtypeLabel }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @endif
                        </div>
                        @if (in_array($action, ['all', 'my'], true))
                            <div class="form-control">
                                <label class="label pt-0"><span class="label-text text-xs font-semibold">สถานะ</span></label>
                                <select class="select select-bordered select-sm w-full" name="status">
                                    <option value="">ทั้งหมด</option>
                                    @if ($action === 'all')
                                        <option value="wait_approval" {{ isset($status) && $status == 'wait_approval' ? 'selected' : '' }}>รออนุมัติจากหัวหน้าแผนก</option>
                                    @endif
                                    <option value="pending" {{ isset($status) && $status == 'pending' ? 'selected' : '' }}>รอการดำเนินการ</option>
                                    <option value="process" {{ isset($status) && $status == 'process' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                                    @if ($action === 'all')
                                        <option value="done" {{ isset($status) && $status == 'done' ? 'selected' : '' }}>เอกสารรออนุมัติ</option>
                                        <option value="complete" {{ isset($status) && $status == 'complete' ? 'selected' : '' }}>เสร็จสมบูรณ์</option>
                                        <option value="reject" {{ isset($status) && $status == 'reject' ? 'selected' : '' }}>ยกเลิกเอกสาร</option>
                                    @endif
                                </select>
                            </div>
                        @elseif ($action === 'borrow')
                            <div class="form-control">
                                <label class="label pt-0"><span class="label-text text-xs font-semibold">สถานะ</span></label>
                                <select class="select select-bordered select-sm w-full" name="status">
                                    <option value="">ทั้งหมด</option>
                                    <option value="pending" {{ isset($status) && $status == 'pending' ? 'selected' : '' }}>รอการดำเนินการ</option>
                                    <option value="borrow" {{ isset($status) && $status == 'borrow' ? 'selected' : '' }}>อุปกรณ์อยู่ระหว่างการยืม</option>
                                    <option value="return_approve" {{ isset($status) && $status == 'return_approve' ? 'selected' : '' }}>รอรับอุปกรณ์คืน</option>
                                </select>
                            </div>
                        @endif
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">แผนกที่สร้าง</span></label>
                            <select class="select select-bordered select-sm w-full" name="department">
                                <option value="">ทั้งหมด</option>
                                @foreach ($departments ?? [] as $dept)
                                    <option value="{{ $dept }}" {{ isset($department) && $department == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($action == 'all')
                            <div class="form-control">
                                <label class="label pt-0"><span class="label-text text-xs font-semibold">ดำเนินการโดย</span></label>
                                <select class="select select-bordered select-sm w-full" name="process_userid">
                                    <option value="">ทั้งหมด</option>
                                    @foreach ($processUsers ?? [] as $processUser)
                                        <option value="{{ $processUser->userid }}" {{ isset($process_userid) && $process_userid == $processUser->userid ? 'selected' : '' }}>
                                            {{ $processUser->userid }} : {{ $processUser->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">วันที่เริ่ม</span></label>
                            <input class="input input-bordered input-sm w-full" type="date" name="start_date" value="{{ $start_date ?? '' }}">
                        </div>
                        <div class="form-control">
                            <label class="label pt-0"><span class="label-text text-xs font-semibold">วันที่สิ้นสุด</span></label>
                            <input class="input input-bordered input-sm w-full" type="date" name="end_date" value="{{ $end_date ?? '' }}">
                        </div>
                            </div>
                        </div>
                    </form>
                    @if (($typeCounts['IT'] ?? 0) + ($typeCounts['USER'] ?? 0) + ($typeCounts['BORROW'] ?? 0) > 0)
                        <p class="text-base-content/60 mt-3 text-xs">
                            @if ($action == 'all')
                                พบ {{ $documents->total() }} รายการ
                            @else
                                พบ {{ $documents->count() }} รายการ
                            @endif
                            @if ($action === 'borrow')
                                · ยืม/คืนอุปกรณ์ {{ $typeCounts['BORROW'] ?? 0 }}
                            @else
                            · แจ้งงาน/สนับสนุน {{ $typeCounts['IT'] ?? 0 }}
                            · ขอสิทธิใช้งาน {{ $typeCounts['USER'] ?? 0 }}
                            @if ($action == 'all')
                                · ยืม/คืนอุปกรณ์ {{ $typeCounts['BORROW'] ?? 0 }}
                            @endif
                            @endif
                        </p>
                    @endif
                </div>
            @endif
            <table class="table">
                <thead>
                    <tr class="text-center">
                        <th>เลขที่</th>
                        <th>ชื่อเอกสาร</th>
                        <th>รายละเอียด</th>
                        <th>ผู้ขอ/วันที่ขอ</th>
                        <th>ผู้อนุมัติ</th>
                        @if ($action == 'approve')
                            <th>ปิดงานโดย</th>
                        @endif
                        <th>สถานะ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                        @php
                            $isNewJobOverdue = $action == 'new' && $document->created_at->diffInSeconds(now()) > 86400;
                        @endphp
                        <tr class="hover:bg-base-300 {{ $isNewJobOverdue ? 'bg-error/10' : '' }}">
                            <td class="text-center min-w-40">
                                @if ($action == 'new')
                                    <x-document.job-timing-badge :since="$document->created_at" />
                                @endif
                                @if (method_exists($document, 'hasChatMessages') && $document->hasChatMessages())
                                    <i class="fas fa-comments text-secondary" title="มีข้อความแชท"></i>
                                @endif
                                <div class="mt-1 flex items-center justify-center gap-1 text-xs">
                                    <span>{{ $document->document_number }}</span>
                                </div>
                            </td>
                            <td class="text-xs">
                                {{ $document->document_type_name }} <br>
                                @if (is_array($document->title))
                                    @foreach ($document->title as $title)
                                        {{ $title }}<br>
                                    @endforeach
                                @else
                                    {{ $document->title }}
                                @endif
                            </td>
                            <td class="text-xs">
                                <div class="preview-trigger cursor-pointer hover:text-blue-600" data-fulltext="{{ $document->detail }}" onclick="showDetail(this)">
                                    {!! Str::limit($document->ListDetail, 50) !!}
                                    <i class="fas fa-search-plus ml-1 text-gray-400"></i>
                                </div>
                            </td>
                            <td>
                                {{ $document->creator->name }}<br>
                                <span class="text-xs text-gray-500">{{ $document->creator->department }}</span><br>
                                <span class="text-xs text-gray-500">{{ $document->created_at->format("d/m/Y H:i:s") }}</span>
                            </td>
                            <td class="text-xs">
                                @foreach ($document->approvers as $approver)
                                    @if ($approver->status == "approve")
                                        <i class="fas fa-check text-primary"></i>
                                    @elseif($approver->status == "reject" || $approver->status == "cancel")
                                        <i class="fas fa-times text-error"></i>
                                    @else
                                        <i class="fas fa-hourglass-half text-ghost"></i>
                                    @endif
                                    {{ $approver->user->name ?? $approver->userid }}
                                @endforeach
                            </td>
                            @if ($action == 'approve')
                                <td class="text-center">
                                    <x-document.done-by :document="$document" />
                                </td>
                            @endif
                            <td class="text-center max-w-40">
                                @php
                                    switch ($document->status) {
                                        case "wait_approval":
                                            $text = "รออนุมัติจากหัวหน้าแผนก";
                                            $class = "badge-soft badge-warning text-xs";
                                            break;
                                        case "not_approval":
                                            $text = "หน่วยงานไม่อนุมัติ";
                                            $class = "badge-soft badge-error";
                                            break;
                                        case "cancel":
                                            $text = "ผู้ขอยกเลิกเอกสาร";
                                            $class = "badge-soft badge-error";
                                            break;
                                        case "pending":
                                            $text = "รอการดำเนินการ";
                                            $class = "badge-soft badge-warning";
                                            break;
                                        case "reject":
                                            $text = "ยกเลิกเอกสาร";
                                            $class = "badge-soft badge-error";
                                            break;
                                        case "process":
                                            $text = "กำลังดำเนินการ";
                                            $class = "badge-soft badge-warning";
                                            break;
                                        case "done":
                                            $text = "เอกสารรออนุมัติ";
                                            $class = "badge-soft badge-secondary";
                                            break;
                                        case "complete":
                                            $text = "เอกสารเสร็จสมบูรณ์";
                                            $class = "badge-soft badge-success";
                                            break;
                                        case "borrow_approve":
                                            $text = "รออนุมัติการยืมอุปกรณ์";
                                            $class = "badge-soft badge-secondary";
                                            break;
                                        case "borrow":
                                            $text = "อุปกรณ์อยู่ระหว่างการยืม";
                                            $class = "badge-soft badge-neutral";
                                            break;
                                        case "return_approve":
                                            $text = "รอรับอุปกรณ์คืน";
                                            $class = "badge-soft badge-primary";
                                            break;
                                        case "return":
                                            $text = "รออนุมัติการคืนอุปกรณ์";
                                            $class = "badge-soft badge-secondary";
                                            break;
                                        default:
                                            $text = "";
                                            $class = "";
                                    }
                                @endphp
                                <div class="badge {{ $class }}">{{ $text }}</div>
                                @if ($document->status == "process")
                                    <div class="bg-primary mt-1 rounded text-xs px-2 py-1 text-primary-content">
                                        {{ $document->assigned_user_id }} : {{ $document->assigned_user->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    @if ($action == "new" && blank($document->assigned_user_id))
                                        <button class="btn btn-accent" type="button" onclick="acceptDocument('{{ $document->id }}','{{ $document->document_tag["document_tag"] }}')">รับงาน</button>
                                    @endif
                                    <a class="btn btn-outline btn-accent" href="{{ route("admin.it.view", ["document_id" => $document->id, "action" => $action, "type" => $document->document_tag["document_tag"]]) }}">ดูเอกสาร</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.list-header>
@endsection
@push("scripts")
    @if ($action == "new")
        <script>
            function acceptDocument(documentId, type) {
                Swal.fire({
                    title: 'ยืนยันการรับงาน?',
                    text: "ต้องการรับงานเอกสารนี้หรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-accent me-2',
                        cancelButton: 'btn btn-ghost'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.post("{{ route("admin.it.accept") }}", {
                            id: documentId,
                            type: type
                        }).then((response) => {
                            if (response.data.status == "success") {
                                Swal.fire({
                                    title: 'สำเร็จ',
                                    text: response.data.message,
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                    timer: 1000
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'ผิดพลาด',
                                    text: response.data.message,
                                    icon: 'error',
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                    timer: 1000
                                });
                            }
                        });
                    }
                });
            }
        </script>
    @elseif($action == "approve")
        <script>
            function approveAllDocuments() {
                Swal.fire({
                    title: 'ยืนยันการอนุมัติ?',
                    text: "ต้องการอนุมัติเอกสารทั้งหมดหรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-accent me-2',
                        cancelButton: 'btn btn-ghost'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.post("{{ route("admin.it.completeall") }}").then((response) => {
                            if (response.data.status == "success") {
                                Swal.fire({
                                    title: 'สำเร็จ',
                                    text: response.data.message,
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                    timer: 1000
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'ผิดพลาด',
                                    text: response.data.message,
                                    icon: 'error',
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                    timer: 1000
                                });
                            }
                        });
                    }
                });
            }
        </script>
    @endif
    @if (in_array($action, ['all', 'new', 'my']))
        <script>
            const itDocumentSubtypes = @json($documentSubtypes ?? []);
            const selectedSubtype = @json($subtype ?? '');

            function updateSubtypeOptions() {
                const typeSelect = document.getElementById('type-filter');
                const subtypeSelect = document.getElementById('subtype-filter');
                const type = typeSelect.value;
                const preserveSelection = typeSelect.dataset.initialType === type;

                subtypeSelect.innerHTML = '<option value="">ทั้งหมด</option>';

                if (type && type !== 'ALL' && itDocumentSubtypes[type]) {
                    subtypeSelect.disabled = false;

                    Object.entries(itDocumentSubtypes[type]).forEach(([value, label]) => {
                        const option = document.createElement('option');
                        option.value = value;
                        option.textContent = label;

                        if (preserveSelection && value === selectedSubtype) {
                            option.selected = true;
                        }

                        subtypeSelect.appendChild(option);
                    });
                } else {
                    subtypeSelect.disabled = true;
                }
            }

            const typeFilter = document.getElementById('type-filter');
            if (typeFilter) {
                typeFilter.dataset.initialType = typeFilter.value;
                typeFilter.addEventListener('change', updateSubtypeOptions);
            }
        </script>
    @endif
    <x-admin.collapsible-filter-script />
    <script>
        let seconds = 30;

        function countdown() {
            document.getElementById('countdown').style.setProperty('--value', seconds);
            if (seconds === 0) {
                location.reload();
            } else {
                seconds--;
                setTimeout(countdown, 1000);
            }
        }
        countdown();

        function showDetail(element) {
            const content = element.getAttribute('data-fulltext');

            Swal.fire({
                title: '<strong>รายละเอียด</strong>',
                html: `<div class="text-left" style="font-size: 0.9rem; line-height: 1.5;">
                    ${content}
                </div>`,
                icon: 'info',
                showConfirmButton: false,
                width: '600px'
            });
        }
    </script>
@endpush
