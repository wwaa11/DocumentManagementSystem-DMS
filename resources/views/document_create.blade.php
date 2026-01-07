@extends("layouts.app")
@section("content")
    <div class="mx-auto max-w-4xl">
        <h1 class="text-primary mb-6 text-3xl font-bold">สร้างเอกสาร</h1>
        <div class="divider"></div>

        <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($document as $item)
                @if ($item->active)
                    <div class="card bg-base-100 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                        <a class="h-full" href="{{ route("document.create.type", $item->short_name) }}">
                            <div class="card-body flex h-full flex-col items-center justify-center">
                                <div class="text-primary mb-4 text-5xl">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h2 class="card-title text-center">{{ $item->name }}</h2>
                                <p class="text-base-content/70 mt-2 text-center text-sm">คลิกเพื่อสร้างเอกสาร</p>
                            </div>
                        </a>
                    </div>
                @else
                    <div class="card bg-base-100 cursor-not-allowed opacity-80 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                        <div class="card-body text-base-content/70 flex h-full flex-col items-center justify-center">
                            <div class="mb-4 text-5xl">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h2 class="card-title text-center">{{ $item->name }}</h2>
                            <p class="mt-2 text-center text-sm">ระหว่างการพัฒนา</p>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <p class="text-base-content/60 text-sm">เลือกประเภทเอกสารที่ต้องการสร้าง</p>
        </div>
    </div>
@endsection
