@props([
    'title' => 'รายการเอกสาร',
    'seconds' => 30,
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <div class="page-hero">
        <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-primary/10 blur-2xl"></div>
        <div class="relative flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-primary/70 mb-1 text-xs font-semibold tracking-wide uppercase">Admin List</p>
                <h1 class="text-primary text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h1>
                <p class="text-base-content/55 mt-1 flex items-center gap-2 text-sm">
                    <i class="fas fa-sync-alt text-xs"></i>
                    รีเฟรชใน
                    <span class="countdown font-mono">
                        <span class="bg-base-100/80 border-base-200 rounded-md border px-2" id="countdown" style="--value:{{ $seconds }};"></span>
                    </span>
                    วินาที
                </p>
            </div>
        </div>
    </div>
    {{ $slot }}
</div>
