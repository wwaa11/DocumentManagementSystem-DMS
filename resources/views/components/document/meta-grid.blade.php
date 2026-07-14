@props([
    'title' => null,
    'createdAt' => null,
    'requesterName' => null,
    'department' => null,
    'phone' => null,
])

<div {{ $attributes->merge(['class' => 'mb-4 grid grid-cols-1 gap-4 md:grid-cols-2']) }}>
    <div>
        @if ($title !== null)
            <p>
                <strong>เรื่อง:</strong>
                <x-document.title-text :title="$title" />
            </p>
        @endif
        @if ($createdAt)
            <p><strong>วันที่:</strong> {{ $createdAt->format('d/m/Y') }}</p>
        @endif
        {{ $left ?? '' }}
    </div>
    <div>
        @if ($requesterName)
            <p><strong>ผู้ขอ:</strong> {{ $requesterName }}</p>
        @endif
        @if ($department)
            <p><strong>แผนก:</strong> {{ $department }}</p>
        @endif
        @if ($phone)
            <p><strong>เบอร์โทร:</strong> {{ $phone }}</p>
        @endif
        {{ $right ?? '' }}
    </div>
</div>
