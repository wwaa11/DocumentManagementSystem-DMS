@extends('layouts.app')

@php
    $isEdit = $hisLog !== null;
    $defaultReportedAt = $isEdit ? $hisLog->reported_at?->toDateString() : now()->toDateString();
    $defaultTime = $isEdit
        ? ($hisLog->time ? \Illuminate\Support\Str::of($hisLog->time)->substr(0, 5)->toString() : now()->format('H:i'))
        : now()->format('H:i');
@endphp

@section('content')
    <div class="mx-auto max-w-5xl pb-8">
        <div class="page-hero mb-4">
            <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-primary/10 blur-2xl"></div>
            <div class="relative">
                <p class="text-primary/70 mb-1 text-xs font-semibold tracking-wide uppercase">HIS Logs</p>
                <h2 class="text-primary text-3xl font-bold tracking-tight">
                    <i class="fas {{ $isEdit ? 'fa-edit' : 'fa-notes-medical' }} mr-2"></i>
                    {{ $isEdit ? 'แก้ไข HIS Log' : 'สร้าง HIS Log' }}
                </h2>
                <p class="text-base-content/65 mt-2 text-sm">
                    {{ $isEdit ? 'แก้ไขรายละเอียดเคสปัญหา HIS' : 'บันทึกเคสปัญหา HIS สำหรับทีม IT' }}
                </p>
            </div>
        </div>

        <form
            action="{{ $isEdit ? route('admin.it.hislogs.update', $hisLog) : route('admin.it.hislogs.store') }}"
            method="POST"
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <x-ui.validation-errors />

            <div class="page-surface mb-6 p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="form-control">
                        <label class="label" for="reported_at">
                            <span class="label-text font-semibold">วันที่แจ้ง <span class="text-error">*</span></span>
                        </label>
                        <input
                            class="input input-bordered w-full"
                            id="reported_at"
                            name="reported_at"
                            type="date"
                            value="{{ old('reported_at', $defaultReportedAt) }}"
                            required
                        >
                    </div>

                    <div class="form-control">
                        <label class="label" for="time">
                            <span class="label-text font-semibold">Time <span class="text-error">*</span></span>
                        </label>
                        <input
                            class="input input-bordered w-full"
                            id="time"
                            name="time"
                            type="time"
                            value="{{ old('time', $defaultTime) }}"
                            required
                            onchange="updateShiftPreview()"
                        >
                        <label class="label">
                            <span class="label-text-alt">
                                Shift:
                                <span class="badge badge-outline badge-sm ml-1" id="shift-preview">-</span>
                            </span>
                        </label>
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label" for="reporter">
                            <span class="label-text font-semibold">ผู้แจ้ง/แผนก <span class="text-error">*</span></span>
                        </label>
                        <input
                            class="input input-bordered w-full"
                            id="reporter"
                            name="reporter"
                            type="text"
                            placeholder="เช่น ก้อย / Eye"
                            value="{{ old('reporter', $isEdit ? $hisLog->reporter : '') }}"
                            required
                        >
                    </div>

                    <div class="form-control">
                        <label class="label" for="module">
                            <span class="label-text font-semibold">Module <span class="text-error">*</span></span>
                        </label>
                        <select class="select select-bordered w-full" id="module" name="module" required>
                            <option value="" disabled {{ old('module', $isEdit ? $hisLog->module : null) ? '' : 'selected' }}>
                                โปรดเลือก Module
                            </option>
                            @foreach ($modules as $module)
                                <option
                                    value="{{ $module }}"
                                    @selected(old('module', $isEdit ? $hisLog->module : null) === $module)
                                >
                                    {{ $module }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label" for="status">
                            <span class="label-text font-semibold">สถานะ <span class="text-error">*</span></span>
                        </label>
                        <select class="select select-bordered w-full" id="status" name="status" required>
                            @foreach ($statuses as $status)
                                <option
                                    value="{{ $status }}"
                                    @selected(old('status', $isEdit ? $hisLog->status : 'Open') === $status)
                                >
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label" for="problem_detail">
                            <span class="label-text font-semibold">รายละเอียดปัญหา</span>
                        </label>
                        <textarea
                            class="textarea textarea-bordered w-full"
                            id="problem_detail"
                            name="problem_detail"
                            rows="3"
                            placeholder="อธิบายปัญหาที่พบ"
                        >{{ old('problem_detail', $isEdit ? $hisLog->problem_detail : '') }}</textarea>
                    </div>

                    <div class="form-control">
                        <label class="label" for="receiver">
                            <span class="label-text font-semibold">ผู้รับเรื่อง</span>
                        </label>
                        <input
                            class="input input-bordered w-full bg-base-200"
                            id="receiver"
                            type="text"
                            value="{{ $receiver }}"
                            readonly
                        >
                        <label class="label">
                            <span class="label-text-alt text-base-content/60">
                                {{ $isEdit ? 'คงค่าผู้รับเรื่องเดิมของการบันทึก' : 'กำหนดอัตโนมัติจากผู้ใช้งานที่เข้าสู่ระบบ' }}
                            </span>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label" for="fixer">
                            <span class="label-text font-semibold">ผู้แก้ไข</span>
                        </label>
                        @php
                            $selectedFixer = old('fixer', $isEdit ? $hisLog->fixer : null);
                            $fixerNames = $fixers->flatten()->pluck('name')->all();
                        @endphp
                        <select class="select select-bordered w-full" id="fixer" name="fixer">
                            <option value="">— ไม่ระบุ —</option>
                            @if (filled($selectedFixer) && ! in_array($selectedFixer, $fixerNames, true))
                                <option value="{{ $selectedFixer }}" selected>{{ $selectedFixer }} (เดิม)</option>
                            @endif
                            @foreach ($fixers as $department => $departmentUsers)
                                <optgroup label="{{ $department }}">
                                    @foreach ($departmentUsers as $fixerUser)
                                        <option
                                            value="{{ $fixerUser->name }}"
                                            @selected($selectedFixer === $fixerUser->name)
                                        >
                                            {{ $fixerUser->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label" for="root_cause">
                            <span class="label-text font-semibold">วิธีแก้ไข / Root Cause</span>
                        </label>
                        <textarea
                            class="textarea textarea-bordered w-full"
                            id="root_cause"
                            name="root_cause"
                            rows="3"
                            placeholder="วิธีแก้ไขหรือสาเหตุหลัก"
                        >{{ old('root_cause', $isEdit ? $hisLog->root_cause : '') }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a class="btn btn-ghost" href="{{ route('admin.it.hislogs.index') }}">ไปที่ All Logs</a>
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'บันทึกการแก้ไข' : 'บันทึก' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function resolveShift(timeValue) {
            if (!timeValue) {
                return '-';
            }

            const [hourText, minuteText] = timeValue.split(':');
            const totalMinutes = (parseInt(hourText, 10) * 60) + parseInt(minuteText, 10);

            if (totalMinutes >= (7 * 60) && totalMinutes <= (12 * 60)) {
                return 'เช้า';
            }

            if (totalMinutes >= ((12 * 60) + 1) && totalMinutes < (17 * 60)) {
                return 'บ่าย';
            }

            return 'ดึก';
        }

        function updateShiftPreview() {
            const timeInput = document.getElementById('time');
            const preview = document.getElementById('shift-preview');
            if (!timeInput || !preview) {
                return;
            }

            preview.textContent = resolveShift(timeInput.value);
        }

        document.addEventListener('DOMContentLoaded', updateShiftPreview);
    </script>
@endpush
