@extends("layouts.app")
@section("content")
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-primary mb-6">สร้างเอกสาร</h1>
        <div class="divider"></div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            @foreach ($document as $item)
                <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <a href="{{ route("document.create.type", $item->short_name) }}" class="h-full">
                        <div class="card-body flex flex-col items-center justify-center h-full">
                            <div class="text-5xl mb-4 text-primary">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h2 class="card-title text-center">{{ $item->name }}</h2>
                            <p class="text-sm text-center text-base-content/70 mt-2">คลิกเพื่อสร้างเอกสาร</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        
        <div class="mt-10 text-center">
            <p class="text-sm text-base-content/60">เลือกประเภทเอกสารที่ต้องการสร้าง</p>
        </div>
    </div>
@endsection
