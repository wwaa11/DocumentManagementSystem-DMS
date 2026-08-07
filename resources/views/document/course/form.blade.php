@extends('layouts.app')

@section('content')
    @php
        $existingCourses = old('courses');
        if ($existingCourses === null && ($plan ?? null)) {
            $existingCourses = $plan->items->map(function ($item) {
                return [
                    'number' => $item->number,
                    'name' => $item->name,
                    'origin' => $item->origin,
                    'objective' => $item->objective,
                    'training_type' => $item->training_type,
                    'schedule_months' => $item->schedule_months ?? [],
                    'estimated_cost' => $item->estimated_cost,
                    'instructors' => $item->instructors->map(fn ($row) => [
                        'userid' => $row->userid,
                        'name' => $row->name,
                        'position' => $row->position,
                        'source_type' => $row->source_type,
                    ])->values()->all(),
                    'target_positions' => $item->targetPositions->pluck('position')->all(),
                    'responsibles' => $item->responsibles->map(fn ($row) => [
                        'userid' => $row->userid,
                        'name' => $row->name,
                        'position' => $row->position,
                    ])->values()->all(),
                ];
            })->values()->all();
        }
        $existingCourses = $existingCourses ?: [[]];

        $approverFromPlan = function (int $level) use ($plan) {
            return ($plan ?? null)?->approvers->firstWhere('level', $level);
        };
    @endphp

    <div class="mx-auto max-w-6xl pb-28">
        <x-document.page-header
            title="{{ $isEdit ? 'แก้ไขแบบฟอร์มหลักสูตร ประจำปี' : 'สร้างแบบฟอร์มหลักสูตร ประจำปี' }}"
            description="1 แผนกต่อปี · หลายหลักสูตรในฟอร์มเดียว · อนุมัติครั้งเดียว · แก้ไขแล้วเริ่มอนุมัติใหม่"
            icon="fas fa-book"
            :back-route="route('document.course', ['year' => $year])"
        />

        @if ($isEdit)
            <div class="alert alert-warning mb-6">
                <i class="fas fa-redo"></i>
                <span>เมื่อบันทึกการแก้ไข ระบบจะเริ่มการอนุมัติใหม่ทั้ง 3 ระดับ</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error mb-6">
                <div>
                    <div class="font-bold">กรุณาตรวจสอบข้อมูลที่ยังไม่ครบ</div>
                    <ul class="mt-2 list-disc pl-4 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <nav class="bg-base-100 border-base-200 mb-8 grid overflow-hidden rounded-2xl border shadow-sm md:grid-cols-3" aria-label="ขั้นตอนการสร้างแบบฟอร์ม">
            <a class="group flex min-h-20 cursor-pointer items-center gap-3 border-b border-base-200 px-5 py-4 transition-colors duration-200 hover:bg-primary/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary md:border-r md:border-b-0" href="#form-scope">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary font-bold text-primary-content">1</span>
                <span>
                    <span class="block text-sm font-bold">กำหนดแบบฟอร์ม</span>
                    <span class="text-base-content/60 block text-xs">เลือกปีและแผนก</span>
                </span>
            </a>
            <a class="group flex min-h-20 cursor-pointer items-center gap-3 border-b border-base-200 px-5 py-4 transition-colors duration-200 hover:bg-primary/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary md:border-r md:border-b-0" href="#approval-chain">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary font-bold text-primary-content">2</span>
                <span>
                    <span class="block text-sm font-bold">กำหนดผู้อนุมัติ</span>
                    <span class="text-base-content/60 block text-xs">เรียงลำดับ 1 ถึง 3</span>
                </span>
            </a>
            <a class="group flex min-h-20 cursor-pointer items-center gap-3 px-5 py-4 transition-colors duration-200 hover:bg-primary/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary" href="#course-details">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary font-bold text-primary-content">3</span>
                <span>
                    <span class="block text-sm font-bold">เพิ่มหลักสูตร</span>
                    <span class="text-base-content/60 block text-xs">เพิ่มได้หลายรายการ</span>
                </span>
            </a>
        </nav>

        <form
            id="course-form"
            class="space-y-8"
            method="POST"
            action="{{ $isEdit ? route('document.course.update', $plan) : route('document.course.store') }}"
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <section id="form-scope" class="card bg-base-100 border-base-200 scroll-mt-6 border shadow-xl">
                <div class="card-body p-0">
                    <div class="bg-base-200/50 border-base-200 flex items-start gap-4 border-b px-6 py-5 md:px-8">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary font-bold text-primary-content">1</span>
                        <div>
                            <h2 class="font-bold">กำหนดแบบฟอร์มประจำปี</h2>
                            <p class="text-base-content/60 mt-1 text-sm">หนึ่งแผนกสร้างได้หนึ่งแบบฟอร์มต่อปี และเพิ่มหลายหลักสูตรไว้ภายในได้</p>
                        </div>
                    </div>
                    <div class="grid gap-6 p-8 md:grid-cols-2">
                        <div class="form-control">
                            <label class="label" for="form_year"><span class="label-text font-bold">1.1 ปีแผนหลักสูตร <span class="text-error">*</span></span></label>
                            <input class="input input-bordered focus:input-primary w-full" type="number" name="year" id="form_year" value="{{ old('year', $plan->year ?? $year) }}" min="2000" max="2100" required>
                            <p class="text-base-content/50 mt-2 text-xs">ค่าเริ่มต้นเป็นปีปัจจุบัน</p>
                        </div>
                        <div class="form-control">
                            <label class="label" for="course_department"><span class="label-text font-bold">1.2 แผนกเจ้าของแผน <span class="text-error">*</span></span></label>
                            <select class="select select-bordered focus:select-primary w-full" id="course_department" name="department" required>
                                <option value="">เลือกแผนก</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department }}" @selected(old('department', $plan->department ?? '') === $department)>{{ $department }}</option>
                                @endforeach
                            </select>
                            <p class="text-base-content/50 mt-2 text-xs">การเลือกแผนกจะโหลดตำแหน่งเป้าหมายและผู้อนุมัติที่บันทึกไว้</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="approval-chain" class="card bg-base-100 border-base-200 scroll-mt-6 border shadow-xl">
                <div class="card-body p-0">
                    <div class="bg-base-200/50 border-base-200 flex items-start gap-4 border-b px-6 py-5 md:px-8">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary font-bold text-primary-content">2</span>
                        <div>
                            <h2 class="font-bold">กำหนดสายการอนุมัติ</h2>
                            <p class="text-base-content/60 mt-1 text-sm">อนุมัติตามลำดับจากระดับ 1 ไป 3 เพียงครั้งเดียวสำหรับทุกหลักสูตรในแบบฟอร์ม</p>
                        </div>
                    </div>
                    <div class="relative space-y-4 p-6 md:p-8">
                        <div class="bg-primary/20 absolute top-14 bottom-14 left-10 hidden w-px md:block"></div>
                        @foreach ([1, 2, 3] as $level)
                            @php
                                $fromPlan = $approverFromPlan($level);
                                $defaultUserid = $level === 1 ? ($level1?->userid ?? '') : '';
                                $defaultName = $level === 1 ? ($level1?->name ?? '') : '';
                                $defaultPosition = $level === 1 ? ($level1?->position ?? '') : '';
                                $defaultEmail = $level === 1 ? ($level1?->email ?? '') : '';
                                $useridValue = old("approver_level{$level}.userid", $fromPlan->userid ?? $defaultUserid);
                                $nameValue = old("approver_level{$level}.name", $fromPlan->name ?? $defaultName);
                                $positionValue = old("approver_level{$level}.position", $fromPlan->position ?? $defaultPosition);
                                $emailValue = old("approver_level{$level}.email", $fromPlan->email ?? $defaultEmail);
                                $hint = $level === 1 ? 'หัวหน้าแผนก · ระบบกรอกให้และแก้ไขได้' : ($level === 2 ? 'ผู้อนุมัติระดับกลาง · เลือกหรือแก้ไขได้' : 'ผู้อนุมัติระดับสุดท้าย · เลือกหรือแก้ไขได้');
                            @endphp
                            <div class="bg-base-100 relative rounded-2xl border border-base-200 p-4 transition-colors duration-200 hover:border-primary/40 md:ml-12 md:p-5">
                                <span class="absolute -left-16 top-5 hidden size-8 items-center justify-center rounded-full border-4 border-base-100 bg-primary text-xs font-bold text-primary-content md:flex">{{ $level }}</span>
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-bold">2.{{ $level }} ผู้อนุมัติระดับ {{ $level }}</div>
                                        <div class="text-base-content/50 mt-1 text-xs">{{ $hint }}</div>
                                    </div>
                                    <button class="btn btn-outline btn-primary btn-sm min-h-11 cursor-pointer" type="button" data-search-level="{{ $level }}">
                                        <i class="fas fa-search"></i> ค้นหาผู้ใช้
                                    </button>
                                </div>
                                <input type="hidden" name="approver_level{{ $level }}[userid]" id="approver{{ $level }}_userid" value="{{ $useridValue }}">
                                <input type="hidden" name="approver_level{{ $level }}[name]" id="approver{{ $level }}_name" value="{{ $nameValue }}">
                                <div class="grid gap-4 lg:grid-cols-3">
                                    <div class="form-control">
                                        <label class="label py-1" for="approver{{ $level }}_display"><span class="label-text text-xs">รหัสและชื่อ</span></label>
                                        <input class="input input-bordered bg-base-200/50 w-full" id="approver{{ $level }}_display" type="text" readonly placeholder="ยังไม่ได้เลือก" value="{{ $useridValue ? $useridValue.' - '.$nameValue : '' }}">
                                    </div>
                                    <div class="form-control">
                                        <label class="label py-1" for="approver{{ $level }}_position"><span class="label-text text-xs">ตำแหน่ง</span></label>
                                        <input class="input input-bordered bg-base-200/50 w-full" id="approver{{ $level }}_position" name="approver_level{{ $level }}[position]" type="text" readonly placeholder="ตำแหน่ง" value="{{ $positionValue }}">
                                    </div>
                                    <div class="form-control">
                                        <label class="label py-1" for="approver{{ $level }}_email"><span class="label-text text-xs">อีเมลที่ใช้แจ้งเตือน</span></label>
                                        <input class="input input-bordered focus:input-primary w-full" id="approver{{ $level }}_email" name="approver_level{{ $level }}[email]" type="email" placeholder="name@example.com" value="{{ $emailValue }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="course-details" class="scroll-mt-6 space-y-5">
                <div class="card bg-primary text-primary-content shadow-xl">
                    <div class="card-body flex-row flex-wrap items-center justify-between gap-4 p-5 md:px-8">
                        <div class="flex items-start gap-4">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-content font-bold text-primary">3</span>
                            <div>
                                <h2 class="font-bold">เพิ่มข้อมูลหลักสูตร</h2>
                                <p class="mt-1 text-sm opacity-80">กรอกข้อมูลตามลำดับในแต่ละการ์ด และเพิ่มหลักสูตรได้ไม่จำกัด</p>
                            </div>
                        </div>
                        <button class="btn min-h-11 cursor-pointer border-0 bg-primary-content text-primary hover:bg-primary-content/90 add-course-btn" type="button">
                            <i class="fas fa-plus"></i> เพิ่มหลักสูตร
                        </button>
                    </div>
                </div>

                <div id="courses-container" class="space-y-6"></div>

                <div class="flex justify-center">
                    <button class="btn btn-primary btn-outline min-h-11 cursor-pointer add-course-btn" type="button">
                        <i class="fas fa-plus"></i> เพิ่มหลักสูตร
                    </button>
                </div>
            </section>

            <div class="bg-base-100/95 border-base-200 fixed right-0 bottom-0 left-0 z-20 border-t p-3 shadow-2xl backdrop-blur md:pl-72">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-3">
                    <p class="text-base-content/60 hidden text-sm sm:block">
                        <i class="fas fa-info-circle mr-1"></i>
                        ช่องที่มี <span class="text-error">*</span> จำเป็นต้องกรอก
                    </p>
                    <div class="ml-auto flex gap-2">
                        <a class="btn btn-ghost min-h-11" href="{{ route('document.course', ['year' => $year]) }}">ยกเลิก</a>
                        <button class="btn btn-primary min-h-11 min-w-40 cursor-pointer" type="submit">
                            <i class="fas fa-paper-plane"></i>
                            {{ $isEdit ? 'บันทึกและอนุมัติใหม่' : 'บันทึกและส่งอนุมัติ' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <template id="course-card-template">
        <article class="course-card card bg-base-100 border-base-200 overflow-hidden border shadow-xl" data-course-index="__INDEX__">
            <div class="card-body p-0">
                <div class="bg-base-200/60 border-base-200 flex items-center justify-between border-b px-5 py-4 md:px-6">
                    <div class="flex items-center gap-2">
                        <span class="flex size-8 items-center justify-center rounded-lg bg-primary text-sm font-bold text-primary-content"><span class="course-number-label">1</span></span>
                        <span class="course-title text-sm font-bold">หลักสูตรรายการที่ <span class="course-number-label">1</span></span>
                    </div>
                    <button class="btn btn-ghost btn-sm min-h-11 cursor-pointer text-error remove-course-btn" type="button">
                        <i class="fas fa-trash"></i> ลบ
                    </button>
                </div>
                <div class="space-y-8 p-5 md:p-6">
                    <section class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="flex size-7 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">A</span>
                            <div>
                                <h3 class="text-sm font-bold">ข้อมูลพื้นฐาน</h3>
                                <p class="text-base-content/50 text-xs">ระบุชื่อ ที่มา และเป้าหมายของหลักสูตร</p>
                            </div>
                        </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold">เลขลำดับหลักสูตร *</span></label>
                            <input class="input input-bordered focus:input-primary w-full" type="text" data-field="number" placeholder="เช่น 1" required>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold">ชื่อหลักสูตร *</span></label>
                            <input class="input input-bordered focus:input-primary w-full" type="text" data-field="name" placeholder="ระบุชื่อหลักสูตร" required>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">ที่มาหลักสูตร *</span></label>
                        <textarea class="textarea textarea-bordered focus:textarea-primary w-full" rows="3" data-field="origin" placeholder="อธิบายปัญหา ความจำเป็น หรือที่มาของหลักสูตร" required></textarea>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">วัตถุประสงค์ *</span></label>
                        <textarea class="textarea textarea-bordered focus:textarea-primary w-full" rows="3" data-field="objective" placeholder="อธิบายผลลัพธ์ที่ต้องการจากการฝึกอบรม" required></textarea>
                    </div>
                    </section>

                    <section class="border-base-200 space-y-4 border-t pt-6">
                        <div class="flex items-center gap-3">
                            <span class="flex size-7 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">B</span>
                            <div>
                                <h3 class="text-sm font-bold">รูปแบบและกำหนดการ</h3>
                                <p class="text-base-content/50 text-xs">เลือกประเภท เดือนที่จัด และงบประมาณ</p>
                            </div>
                        </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">ประเภทการฝึกอบรม *</span></label>
                        <div class="grid gap-2 sm:grid-cols-3">
                            @foreach ($trainingTypes as $value => $label)
                                <label class="label min-h-11 cursor-pointer justify-start gap-3 rounded-xl border border-base-200 px-4 transition-colors duration-200 hover:border-primary">
                                    <input class="radio radio-primary" type="radio" data-field="training_type" value="{{ $value }}">
                                    <span class="label-text font-medium">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">กำหนดการ *</span></label>
                        <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                            @foreach ($months as $month => $label)
                                <label class="label min-h-11 cursor-pointer justify-start gap-2 rounded-lg border border-base-200 px-3 py-2 transition-colors duration-200 hover:border-primary">
                                    <input class="checkbox checkbox-sm checkbox-primary" type="checkbox" data-field="schedule_months" value="{{ $month }}">
                                    <span class="label-text text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">ประมาณการค่าใช้จ่าย</span></label>
                        <label class="input input-bordered focus-within:input-primary flex w-full items-center gap-2 md:w-80">
                            <span class="text-base-content/50 text-sm">฿</span>
                            <input class="grow" type="number" step="0.01" min="0" data-field="estimated_cost" placeholder="0.00">
                            <span class="text-base-content/50 text-sm">บาท</span>
                        </label>
                    </div>
                    </section>

                    <section class="border-base-200 space-y-5 border-t pt-6">
                        <div class="flex items-center gap-3">
                            <span class="flex size-7 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">C</span>
                            <div>
                                <h3 class="text-sm font-bold">ผู้เกี่ยวข้องและกลุ่มเป้าหมาย</h3>
                                <p class="text-base-content/50 text-xs">เลือกตำแหน่งเป้าหมาย วิทยากร และผู้รับผิดชอบ</p>
                            </div>
                        </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">กลุ่มเป้าหมายงาน *</span></label>
                        <input class="input input-bordered mb-2 min-h-11 target-filter" type="search" placeholder="ค้นหาตำแหน่ง...">
                        <div class="target-positions max-h-40 overflow-y-auto rounded-xl border border-base-200 p-3">
                            <p class="text-xs italic opacity-40">เลือกแผนกเพื่อโหลดตำแหน่ง</p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-bold">วิทยากร *</span>
                            <div class="flex gap-2">
                                <button class="btn btn-sm btn-primary min-h-11 cursor-pointer add-internal-instructor" type="button"><i class="fas fa-user-plus"></i> ภายใน</button>
                                <button class="btn btn-sm btn-outline min-h-11 cursor-pointer add-external-instructor" type="button"><i class="fas fa-plus"></i> ภายนอก</button>
                            </div>
                        </div>
                        <table class="table instructor-table">
                            <tbody></tbody>
                        </table>
                    </div>
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-bold">ผู้รับผิดชอบ *</span>
                            <button class="btn btn-sm btn-primary min-h-11 cursor-pointer add-responsible" type="button"><i class="fas fa-user-plus"></i> เพิ่มผู้รับผิดชอบ</button>
                        </div>
                        <table class="table responsible-table">
                            <tbody></tbody>
                        </table>
                    </div>
                    </section>
                </div>
            </div>
        </article>
    </template>
@endsection

@push('scripts')
    <script>
        const initialCourses = @json($existingCourses);
        const months = @json($months);
        let positionCache = [];
        const isEdit = @json($isEdit);

        async function searchUser(userid) {
            if (!userid) return null;
            try {
                const response = await axios.post('{{ route('user.search') }}', { userid });
                if (response.data.status) return response.data.user;
                Swal.fire({ icon: 'error', title: 'ไม่พบผู้ใช้', timer: 1500, showConfirmButton: false });
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'เชื่อมต่อไม่สำเร็จ' });
            }
            return null;
        }

        function setApproverLevel(level, user) {
            document.getElementById(`approver${level}_userid`).value = user.userid || '';
            document.getElementById(`approver${level}_name`).value = user.name || '';
            document.getElementById(`approver${level}_position`).value = user.position || '';
            document.getElementById(`approver${level}_email`).value = user.email || '';
            document.getElementById(`approver${level}_display`).value = `${user.userid || ''} - ${user.name || ''}`;
        }

        async function openUserSearch(title, onPick) {
            const { value: userid } = await Swal.fire({
                title,
                input: 'text',
                inputPlaceholder: 'รหัสพนักงาน',
                showCancelButton: true,
                confirmButtonText: 'ค้นหา',
                cancelButtonText: 'ยกเลิก',
                buttonsStyling: false,
                customClass: { confirmButton: 'btn btn-primary mx-2', cancelButton: 'btn btn-ghost mx-2' },
            });
            if (!userid) return;
            const user = await searchUser(userid);
            if (user) onPick(user);
        }

        function reindexCourses() {
            document.querySelectorAll('#courses-container .course-card').forEach((card, index) => {
                card.dataset.courseIndex = String(index);
                card.querySelectorAll('.course-number-label').forEach((label) => {
                    label.textContent = String(index + 1);
                });

                card.querySelectorAll('[data-field="number"]').forEach((el) => el.name = `courses[${index}][number]`);
                card.querySelectorAll('[data-field="name"]').forEach((el) => el.name = `courses[${index}][name]`);
                card.querySelectorAll('[data-field="origin"]').forEach((el) => el.name = `courses[${index}][origin]`);
                card.querySelectorAll('[data-field="objective"]').forEach((el) => el.name = `courses[${index}][objective]`);
                card.querySelectorAll('[data-field="estimated_cost"]').forEach((el) => el.name = `courses[${index}][estimated_cost]`);
                card.querySelectorAll('[data-field="training_type"]').forEach((el) => el.name = `courses[${index}][training_type]`);
                card.querySelectorAll('[data-field="schedule_months"]').forEach((el) => el.name = `courses[${index}][schedule_months][]`);
                card.querySelectorAll('.target-checkbox').forEach((el) => el.name = `courses[${index}][target_positions][]`);

                card.querySelectorAll('.instructor-table tbody tr').forEach((tr, rowIndex) => {
                    tr.querySelectorAll('input[type="hidden"]').forEach((input) => {
                        const key = input.dataset.key;
                        input.name = `courses[${index}][instructors][${rowIndex}][${key}]`;
                    });
                });

                card.querySelectorAll('.responsible-table tbody tr').forEach((tr, rowIndex) => {
                    tr.querySelectorAll('input[type="hidden"]').forEach((input) => {
                        const key = input.dataset.key;
                        input.name = `courses[${index}][responsibles][${rowIndex}][${key}]`;
                    });
                });

                card.querySelector('.remove-course-btn').classList.toggle('hidden', document.querySelectorAll('#courses-container .course-card').length <= 1);
            });
        }
        window.reindexCourses = reindexCourses;

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            })[char]);
        }

        function selectedTargetsOf(card) {
            const rendered = card.querySelectorAll('.target-checkbox');
            if (rendered.length) {
                return Array.from(card.querySelectorAll('.target-checkbox:checked')).map((el) => el.value);
            }

            try {
                return JSON.parse(card.dataset.selectedTargets || '[]');
            } catch (e) {
                return [];
            }
        }

        function renderTargetPositions(card, selected = []) {
            const wrap = card.querySelector('.target-positions');
            card.dataset.selectedTargets = JSON.stringify(selected);

            const options = positionCache.slice();
            selected.forEach((position) => {
                if (!options.includes(position)) options.push(position);
            });

            if (!options.length) {
                wrap.innerHTML = '<p class="text-xs italic opacity-40">เลือกแผนกเพื่อโหลดตำแหน่ง</p>';
                return;
            }

            wrap.innerHTML = options.map((position) => `
                <label class="target-option label cursor-pointer justify-start gap-2 py-1" data-label="${escapeHtml(position.toLowerCase())}">
                    <input class="checkbox checkbox-sm checkbox-primary target-checkbox" type="checkbox" value="${escapeHtml(position)}" ${selected.includes(position) ? 'checked' : ''}>
                    <span class="label-text text-sm">${escapeHtml(position)}</span>
                </label>
            `).join('');
            reindexCourses();
        }

        function addInstructor(card, data) {
            const tbody = card.querySelector('.instructor-table tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-sm">${data.source_type === 'external' ? 'ภายนอก' : 'ภายใน'} · ${data.userid} · ${data.name} · ${data.position}
                    <input type="hidden" data-key="source_type" value="${data.source_type}">
                    <input type="hidden" data-key="userid" value="${data.userid}">
                    <input type="hidden" data-key="name" value="${data.name}">
                    <input type="hidden" data-key="position" value="${data.position}">
                </td>
                <td class="w-12"><button type="button" class="btn btn-ghost btn-xs text-error" onclick="this.closest('tr').remove(); reindexCourses();"><i class="fas fa-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
            reindexCourses();
        }

        function addResponsible(card, data) {
            const tbody = card.querySelector('.responsible-table tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-sm">${data.userid} · ${data.name} · ${data.position}
                    <input type="hidden" data-key="userid" value="${data.userid}">
                    <input type="hidden" data-key="name" value="${data.name}">
                    <input type="hidden" data-key="position" value="${data.position}">
                </td>
                <td class="w-12"><button type="button" class="btn btn-ghost btn-xs text-error" onclick="this.closest('tr').remove(); reindexCourses();"><i class="fas fa-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
            reindexCourses();
        }

        function bindCourseCard(card, data = {}) {
            card.querySelector('[data-field="number"]').value = data.number || '';
            card.querySelector('[data-field="name"]').value = data.name || '';
            card.querySelector('[data-field="origin"]').value = data.origin || '';
            card.querySelector('[data-field="objective"]').value = data.objective || '';
            card.querySelector('[data-field="estimated_cost"]').value = data.estimated_cost ?? '';

            const training = data.training_type || 'internal';
            card.querySelectorAll('[data-field="training_type"]').forEach((radio) => {
                radio.checked = radio.value === training;
            });

            const monthsSelected = (data.schedule_months || []).map(String);
            card.querySelectorAll('[data-field="schedule_months"]').forEach((checkbox) => {
                checkbox.checked = monthsSelected.includes(String(checkbox.value));
            });

            renderTargetPositions(card, data.target_positions || []);
            (data.instructors || []).forEach((instructor) => addInstructor(card, instructor));
            (data.responsibles || []).forEach((responsible) => addResponsible(card, responsible));

            card.querySelector('.remove-course-btn').addEventListener('click', () => {
                if (document.querySelectorAll('#courses-container .course-card').length <= 1) return;
                card.remove();
                reindexCourses();
            });

            card.querySelector('.add-internal-instructor').addEventListener('click', () => {
                openUserSearch('เพิ่มวิทยากรภายใน', (user) => addInstructor(card, {
                    source_type: 'internal',
                    userid: user.userid,
                    name: user.name,
                    position: user.position,
                }));
            });

            card.querySelector('.add-external-instructor').addEventListener('click', async () => {
                const { value } = await Swal.fire({
                    title: 'เพิ่มวิทยากรภายนอก',
                    html: `
                        <input id="ext-userid" class="input input-bordered w-full mb-2" placeholder="รหัส / อ้างอิง">
                        <input id="ext-name" class="input input-bordered w-full mb-2" placeholder="ชื่อ">
                        <input id="ext-position" class="input input-bordered w-full" placeholder="ตำแหน่ง">
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'เพิ่ม',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: { confirmButton: 'btn btn-primary mx-2', cancelButton: 'btn btn-ghost mx-2' },
                    preConfirm: () => {
                        const userid = document.getElementById('ext-userid').value.trim();
                        const name = document.getElementById('ext-name').value.trim();
                        const position = document.getElementById('ext-position').value.trim();
                        if (!userid || !name || !position) {
                            Swal.showValidationMessage('กรอกให้ครบ');
                            return false;
                        }
                        return { userid, name, position, source_type: 'external' };
                    },
                });
                if (value) addInstructor(card, value);
            });

            card.querySelector('.add-responsible').addEventListener('click', () => {
                openUserSearch('เพิ่มผู้รับผิดชอบ', (user) => addResponsible(card, {
                    userid: user.userid,
                    name: user.name,
                    position: user.position,
                }));
            });

            card.querySelector('.target-filter').addEventListener('input', (event) => {
                const keyword = event.target.value.trim().toLowerCase();
                card.querySelectorAll('.target-option').forEach((option) => {
                    option.classList.toggle('hidden', keyword && !option.dataset.label.includes(keyword));
                });
            });
        }

        function addCourseCard(data = {}) {
            const template = document.getElementById('course-card-template');
            const node = template.content.cloneNode(true);
            const card = node.querySelector('.course-card');
            document.getElementById('courses-container').appendChild(card);
            bindCourseCard(card, data);
            reindexCourses();
        }

        async function loadDepartmentData(department, fillApprovers = true) {
            if (!department) return;
            const [approverRes, positionRes] = await Promise.all([
                axios.post('{{ route('document.course.departmentApprovers') }}', { department }),
                axios.post('{{ route('document.course.departmentPositions') }}', { department }),
            ]);

            if (fillApprovers && approverRes.data.status) {
                if (approverRes.data.level2) setApproverLevel(2, approverRes.data.level2);
                if (approverRes.data.level3) setApproverLevel(3, approverRes.data.level3);
            }

            positionCache = positionRes.data.positions || [];
            document.querySelectorAll('#courses-container .course-card').forEach((card) => {
                renderTargetPositions(card, selectedTargetsOf(card));
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-search-level]').forEach((button) => {
                button.addEventListener('click', () => {
                    const level = button.getAttribute('data-search-level');
                    openUserSearch(`ค้นหาผู้อนุมัติระดับ ${level}`, (user) => setApproverLevel(level, user));
                });
            });

            document.querySelectorAll('.add-course-btn').forEach((button) => {
                button.addEventListener('click', () => addCourseCard());
            });
            document.getElementById('course_department').addEventListener('change', (event) => {
                loadDepartmentData(event.target.value, true);
            });

            initialCourses.forEach((course) => addCourseCard(course));
            if (!initialCourses.length) addCourseCard();

            const department = document.getElementById('course_department').value;
            if (department) {
                loadDepartmentData(department, !isEdit);
            }

            document.getElementById('course-form').addEventListener('submit', (event) => {
                const cards = document.querySelectorAll('#courses-container .course-card');
                if (!cards.length) {
                    event.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'กรุณาเพิ่มหลักสูตรอย่างน้อย 1 รายการ' });
                    return;
                }

                for (const card of cards) {
                    if (!card.querySelector('.instructor-table tbody tr') || !card.querySelector('.responsible-table tbody tr') || !card.querySelector('.target-checkbox:checked')) {
                        event.preventDefault();
                        Swal.fire({ icon: 'warning', title: 'กรอกวิทยากร / ผู้รับผิดชอบ / กลุ่มเป้าหมายให้ครบทุกหลักสูตร' });
                        return;
                    }
                }

                if (!document.getElementById('approver1_userid').value || !document.getElementById('approver2_userid').value || !document.getElementById('approver3_userid').value) {
                    event.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'กรุณาเลือกผู้อนุมัติครบ 3 ระดับ' });
                }
            });
        });
    </script>
@endpush
