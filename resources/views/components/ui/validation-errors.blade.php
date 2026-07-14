@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'alert alert-error mb-4', 'role' => 'alert']) }}>
        <span class="fas fa-exclamation-triangle mr-2"></span>
        <div>
            <span>มีข้อผิดพลาดในการสร้างเอกสาร</span>
            <ul class="mt-1 list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
