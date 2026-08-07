@props([
    'label' => 'พิมพ์',
])

<button
    type="button"
    {{ $attributes->merge(['class' => 'btn btn-outline btn-sm gap-2 no-print']) }}
    onclick="window.print()"
>
    <i class="fas fa-print"></i> {{ $label }}
</button>
