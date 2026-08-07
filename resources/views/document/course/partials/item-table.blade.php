@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\CoursePlanItem> $items */
@endphp
@if ($items->isEmpty())
    <p class="text-base-content/50 text-sm">{{ $emptyMessage }}</p>
@else
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr class="bg-base-200/50">
                    <th class="w-16">ลำดับ</th>
                    <th>ชื่อหลักสูตร</th>
                    <th>ประเภท</th>
                    <th>กำหนดการ</th>
                    <th>ประมาณการค่าใช้จ่าย</th>
                    <th>วิทยากร</th>
                    <th>ผู้รับผิดชอบ</th>
                    <th>ฝึกอบรม</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    @include('document.course.partials.item-row', ['item' => $item, 'form' => $form])
                @endforeach
            </tbody>
        </table>
    </div>
@endif
