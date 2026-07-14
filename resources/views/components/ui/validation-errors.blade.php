@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'alert alert-error border-error/20 mb-4 shadow-sm', 'role' => 'alert']) }}>
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <h3 class="font-bold">พบข้อผิดพลาด</h3>
            <ul class="mt-1 list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
