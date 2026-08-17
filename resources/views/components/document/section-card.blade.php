@props([
    'title',
    'subtitle' => null,
    'icon' => 'fas fa-circle-info',
    'tone' => 'primary',
    'count' => null,
    'flush' => false,
])

@php
    $tones = [
        'primary' => ['chip' => 'bg-primary/10 text-primary', 'header' => 'bg-primary/5', 'eyebrow' => 'text-primary'],
        'secondary' => ['chip' => 'bg-secondary/10 text-secondary', 'header' => 'bg-secondary/5', 'eyebrow' => 'text-secondary'],
        'accent' => ['chip' => 'bg-accent/10 text-accent', 'header' => 'bg-accent/5', 'eyebrow' => 'text-accent'],
        'info' => ['chip' => 'bg-info/10 text-info', 'header' => 'bg-info/5', 'eyebrow' => 'text-info'],
        'success' => ['chip' => 'bg-success/10 text-success', 'header' => 'bg-success/5', 'eyebrow' => 'text-success'],
        'warning' => ['chip' => 'bg-warning/10 text-warning', 'header' => 'bg-warning/5', 'eyebrow' => 'text-warning'],
        'neutral' => ['chip' => 'bg-base-200 text-base-content/70', 'header' => 'bg-base-200/40', 'eyebrow' => 'text-base-content/70'],
    ];
    $palette = $tones[$tone] ?? $tones['primary'];
@endphp

<section {{ $attributes->merge(['class' => 'bg-base-100 border-base-200 overflow-hidden rounded-2xl border shadow-sm']) }}>
    <header class="{{ $palette['header'] }} border-base-200 flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4">
        <div class="flex min-w-0 items-center gap-3">
            <span class="{{ $palette['chip'] }} flex size-10 shrink-0 items-center justify-center rounded-xl">
                <i class="{{ $icon }}"></i>
            </span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="truncate text-sm font-bold">{{ $title }}</h3>
                    @if ($count !== null)
                        <span class="badge badge-sm badge-ghost font-mono font-bold">{{ $count }}</span>
                    @endif
                </div>
                @if ($subtitle)
                    <p class="text-base-content/50 mt-0.5 truncate text-xs">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @if (isset($actions))
            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endif
    </header>

    <div class="{{ $flush ? '' : 'p-5' }}">{{ $slot }}</div>
</section>
