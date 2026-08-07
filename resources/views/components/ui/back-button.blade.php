@props([
    'label' => 'ย้อนกลับ',
    'variant' => 'text',
])

@php
    $classes = $variant === 'button'
        ? 'btn btn-ghost btn-sm hover:bg-base-200 gap-2 transition-all'
        : 'text-accent w-24 cursor-pointer';
@endphp

<button type="button" {{ $attributes->merge(['class' => $classes.' no-print', 'onclick' => 'window.history.back()']) }}>
    <i class="fas fa-arrow-left"></i> {{ $label }}
</button>
