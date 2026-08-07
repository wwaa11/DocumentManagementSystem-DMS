@props([
    'logs' => null,
    'document' => null,
    'title' => 'รายการดำเนินงาน',
])

@php
    $processLogs = $logs;
    if ($processLogs === null && $document !== null) {
        $processLogs = $document->logs()
            ->whereIn('action', ['process', 'reject'])
            ->get();
    }
    $processLogs = $processLogs ?? collect();
@endphp

<div {{ $attributes }}>
    <h5 class="card-title">{{ $title }}</h5>
    @forelse ($processLogs as $log)
        @php
            $actionCss = $log->action == 'process' ? 'primary' : 'accent';
        @endphp
        <div class="rounded-box border-{{ $actionCss }} mb-2 w-full border p-2">
            <div class="min-h-[4rem] w-full whitespace-pre-wrap break-words p-2 text-sm">{!! $log->details !!}</div>
            <div class="text-{{ $actionCss }} flex justify-between text-xs">
                <div>{{ $log->userid }} {{ $log->user->name ?? '' }}</div>
                <div>{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
            </div>
        </div>
    @empty
        <p class="text-base-content/40 mb-2 text-sm">ยังไม่มีรายการดำเนินงาน</p>
    @endforelse

    {{ $slot }}
</div>
