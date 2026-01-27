<div class="from-primary/10 to-base-100 mb-3 rounded-lg bg-gradient-to-r p-6 shadow-xl">
    <h2 class="text-primary mb-2 text-3xl font-bold tracking-tight">
        <a href="{{ route("document.create") }}"><i class="fas fa-chevron-left mr-2 cursor-pointer"></i></a> <i class="{{ $icon }} mr-2"></i> เอกสาร {{ $title }}
    </h2>
    <div class="divider opacity-50"></div>
    <p class="text-base-content/70">{{ $description }}</p>
</div>
