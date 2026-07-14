@props([
    'title' => 'รายการเอกสาร',
    'seconds' => 30,
])

<div {{ $attributes->merge(['class' => 'mx-8']) }}>
    <h1 class="text-primary text-2xl font-bold">{{ $title }}</h1>
    <span class="countdown font-mono text-sm">
        Refesh in
        <span class="bg-base-300 mx-2 rounded-md px-2" id="countdown" style="--value:{{ $seconds }};"></span>
        seconds
    </span>
    <div class="divider"></div>
    {{ $slot }}
</div>
