@props([
    'id',
    'name',
    'value',
    'onchange' => null,
    'checked' => false,
    'hint' => null,
])

<label
    {{ $attributes->merge(['class' => 'bg-base-100 hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md']) }}
    for="{{ $id }}"
>
    <div class="flex items-center">
        <input
            class="radio radio-primary mr-3"
            id="{{ $id }}"
            type="radio"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($checked)
            @if ($onchange) onchange="{{ $onchange }}" @endif
        />
        <div>
            <h4 class="font-medium">{{ $slot }}</h4>
            @if ($hint)
                <div class="text-sm text-red-500">{{ $hint }}</div>
            @endif
        </div>
    </div>
</label>
