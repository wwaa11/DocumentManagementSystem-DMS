@props([
    'formCode' => 'QF-ITD-09/Rev.3 (15-06-66)',
    'documentNumber' => null,
    'documentTypeName' => null,
])

<div {{ $attributes->merge(['class' => 'flex items-center']) }}>
    <img class="mr-4 h-auto w-36" src="{{ asset('images/Side Logo.png') }}" alt="Side Logo">
    <div class="flex-1 text-end">
        <h2 class="text-2xl font-bold">{{ $formCode }}</h2>
        @if ($documentNumber)
            <p class="text-sm text-gray-500">เลขที่เอกสาร: {{ $documentNumber }}</p>
        @endif
        @if ($documentTypeName)
            <p class="text-sm text-gray-500">ประเภทเอกสาร: {{ $documentTypeName }}</p>
        @endif
    </div>
</div>
