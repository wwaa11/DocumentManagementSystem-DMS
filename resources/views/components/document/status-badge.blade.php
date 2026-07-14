@props(['status'])

@php
    $map = [
        'wait_approval' => ['text' => 'รออนุมัติจากหน่วยงาน', 'class' => 'badge-warning'],
        'pending' => ['text' => 'รอการดำเนินการ', 'class' => 'badge-info'],
        'process' => ['text' => 'กำลังดำเนินการ', 'class' => 'badge-primary'],
        'done' => ['text' => 'เอกสารรออนุมัติ', 'class' => 'badge-secondary'],
        'complete' => ['text' => 'เสร็จสมบูรณ์', 'class' => 'badge-success'],
        'reject' => ['text' => 'ยกเลิกเอกสาร', 'class' => 'badge-error'],
        'cancel' => ['text' => 'ยกเลิกเอกสาร', 'class' => 'badge-error'],
        'not_approval' => ['text' => 'ไม่อนุมัติ', 'class' => 'badge-error'],
        'borrow' => ['text' => 'กำลังยืม', 'class' => 'badge-primary'],
        'borrow_approve' => ['text' => 'รออนุมัติยืม', 'class' => 'badge-secondary'],
        'return' => ['text' => 'รออนุมัติคืน', 'class' => 'badge-secondary'],
        'return_approve' => ['text' => 'รอรับคืน', 'class' => 'badge-info'],
    ];
    $badge = $map[$status] ?? ['text' => $status, 'class' => 'badge-ghost'];
@endphp

<span {{ $attributes->merge(['class' => 'badge '.$badge['class']]) }}>{{ $badge['text'] }}</span>
