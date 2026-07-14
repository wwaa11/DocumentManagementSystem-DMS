@props([
    'icon' => null,
    'tag' => 'h3',
])

<{!! $tag !!} {{ $attributes->merge(['class' => 'text-primary mb-4 flex items-center gap-2 text-lg font-bold tracking-tight']) }}>
    @if ($icon)
        <span class="bg-primary/10 text-primary flex h-8 w-8 items-center justify-center rounded-lg text-sm">
            <i class="{{ $icon }}"></i>
        </span>
    @endif
    <span>{{ $slot }}</span>
</{!! $tag !!}>
