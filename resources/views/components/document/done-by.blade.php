@props(['document'])

@php
    $doneByTask = collect($document->tasks ?? [])
        ->where('status', 'approve')
        ->where('task_name', 'ดำเนินการเสร็จสิ้น')
        ->sortByDesc(fn ($task) => $task->date?->timestamp ?? 0)
        ->last();

    $doneByLog = collect($document->logs ?? [])
        ->whereIn('action', ['process', 'retreive'])
        ->sortByDesc('id')
        ->first();

    $userid = $doneByTask?->task_user ?? $doneByLog?->userid;
    $name = $doneByTask?->user?->name ?? $doneByLog?->user?->name;
@endphp

@if (filled($userid))
    <div {{ $attributes->merge(['class' => 'text-xs']) }}>
        {{ $userid }} : {{ $name }}
    </div>
@else
    <span {{ $attributes->merge(['class' => 'text-xs text-gray-400']) }}>-</span>
@endif
