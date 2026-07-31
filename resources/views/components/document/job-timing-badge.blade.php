@props(['since'])

@php
    $since = \Illuminate\Support\Carbon::parse($since);
    $isOnTime = $since->diffInSeconds(now()) <= 86400;
@endphp

@if ($isOnTime)
    <div {{ $attributes->merge(['class' => 'badge badge-soft badge-success mt-1']) }}>On Time</div>
@else
    <div {{ $attributes->merge(['class' => 'badge badge-soft badge-error mt-1']) }}>Overdue</div>
@endif
