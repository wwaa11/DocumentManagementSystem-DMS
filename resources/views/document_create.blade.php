@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <section class="page-hero">
            <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-accent/15 blur-2xl"></div>
            <div class="relative">
                <p class="text-primary/70 mb-1 text-xs font-semibold tracking-wide uppercase">Create</p>
                <h1 class="text-primary text-3xl font-bold tracking-tight">สร้างเอกสาร</h1>
                <p class="text-base-content/60 mt-2 text-sm">เลือกประเภทเอกสารที่ต้องการสร้างจากรายการด้านล่าง</p>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($document as $item)
                @if ($item->active)
                    <a
                        class="group page-surface hover:border-primary/30 block p-5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        href="{{ route('document.create.type', $item->short_name) }}"
                    >
                        <div class="flex h-full flex-col items-start gap-4">
                            <span class="bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-content flex h-12 w-12 items-center justify-center rounded-xl text-xl transition">
                                <i class="fas fa-file-alt"></i>
                            </span>
                            <div>
                                <h2 class="text-base font-bold leading-snug">{{ $item->name }}</h2>
                                <p class="text-base-content/55 mt-1 text-sm">คลิกเพื่อสร้างเอกสาร</p>
                            </div>
                            <span class="text-primary mt-auto inline-flex items-center gap-1 text-sm font-semibold">
                                เริ่มสร้าง <i class="fas fa-arrow-right text-xs transition group-hover:translate-x-0.5"></i>
                            </span>
                        </div>
                    </a>
                @else
                    <div class="page-surface cursor-not-allowed p-5 opacity-60">
                        <div class="flex h-full flex-col items-start gap-4">
                            <span class="bg-base-200 text-base-content/40 flex h-12 w-12 items-center justify-center rounded-xl text-xl">
                                <i class="fas fa-file-alt"></i>
                            </span>
                            <div>
                                <h2 class="text-base font-bold leading-snug">{{ $item->name }}</h2>
                                <p class="text-base-content/50 mt-1 text-sm">ระหว่างการพัฒนา</p>
                            </div>
                            <span class="badge badge-ghost badge-sm mt-auto">Coming soon</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endsection
