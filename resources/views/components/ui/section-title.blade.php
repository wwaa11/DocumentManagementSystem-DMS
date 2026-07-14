@props([
    'icon' => null,
    'tag' => 'h3',
])

<{!! $tag !!} {{ $attributes->merge(['class' => 'card-title text-primary mb-4 flex items-center text-xl']) }}>
    @if ($icon)
        <i class="{{ $icon }} mr-2"></i>
    @endif
    {{ $slot }}
</{!! $tag !!}>
