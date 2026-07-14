{{-- Page create header --}}
@props([
    'title',
    'description' => '',
    'icon' => 'fas fa-file-alt',
    'backRoute' => null,
])

<div {{ $attributes->merge(['class' => 'page-hero mb-4']) }}>
    <div class="pointer-events-none absolute -right-6 -top-10 h-32 w-32 rounded-full bg-accent/10 blur-2xl"></div>
    <div class="relative">
        <div class="mb-3 flex flex-wrap items-center gap-3">
            <a
                class="btn btn-ghost btn-sm border-base-content/10 gap-2"
                href="{{ $backRoute ?? route('document.create') }}"
                aria-label="ย้อนกลับ"
            >
                <i class="fas fa-chevron-left"></i>
                กลับ
            </a>
            <span class="badge badge-primary badge-soft gap-1">
                <i class="{{ $icon }} text-xs"></i>
                สร้างเอกสาร
            </span>
        </div>
        <h2 class="text-primary text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h2>
        @if ($description !== '')
            <p class="text-base-content/65 mt-2 max-w-3xl text-sm leading-relaxed">{{ $description }}</p>
        @endif
    </div>
</div>
