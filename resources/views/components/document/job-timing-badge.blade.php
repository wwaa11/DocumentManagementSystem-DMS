@props(['since'])

@php
    $since = \Illuminate\Support\Carbon::parse($since);
    $isOnTime = $since->diffInSeconds(now()) <= 86400;
@endphp

@if ($isOnTime)
    <div {{ $attributes->merge(['class' => 'badge badge-success mt-1']) }}>On Time</div>
@else
    <div {{ $attributes->merge(['class' => 'badge badge-error job-timing-fire mt-1 gap-1']) }}>
        Overdue
        <i class="fas fa-fire job-timing-fire__flame" aria-hidden="true"></i>
    </div>
@endif
