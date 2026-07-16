@php
    switch ($document['status']) {
        case 'wait_approval':
            $text = 'รออนุมัติจากหน่วยงาน';
            $class = 'badge-soft badge-warning';
            break;
        case 'not_approval':
            $text = 'หน่วยงานไม่อนุมัติ';
            $class = 'badge-soft badge-error';
            break;
        case 'cancel':
            $text = 'ผู้ขอยกเลิกเอกสาร';
            $class = 'badge-soft badge-error';
            break;
        case 'pending':
            $text = 'รอการดำเนินการ';
            $class = 'badge-soft badge-warning';
            break;
        case 'reject':
            $text = 'ยกเลิกเอกสาร';
            $class = 'badge-soft badge-error';
            break;
        case 'process':
            $text = 'กำลังดำเนินการ';
            $class = 'badge-soft badge-warning';
            break;
        case 'done':
            $text = 'เอกสารรออนุมัติ';
            $class = 'badge-soft badge-secondary';
            break;
        case 'complete':
            $text = 'เอกสารเสร็จสมบูรณ์';
            $class = 'badge-soft badge-success';
            break;
        case 'complete-partial':
            $text = 'เสร็จบางส่วน';
            $class = 'badge-soft badge-info';
            break;
        case 'borrow_approve':
            $text = 'รออนุมัติการยืมอุปกรณ์';
            $class = 'badge-soft badge-secondary';
            break;
        case 'borrow':
            $text = 'อุปกรณ์อยู่ระหว่างการยืม';
            $class = 'badge-soft badge-neutral';
            break;
        default:
            $text = $document['status'] ?: '-';
            $class = 'badge-soft badge-ghost';
    }
@endphp
<tr class="hover:bg-base-300">
    <td class="flex flex-col gap-1">
        <div class="join">
            <div class="join-item badge badge-soft badge-{{ $document['document_tag']['colour'] }}">
                {{ $document['document_tag']['document_tag'] }}
            </div>
            @if ($document['flag'] == 'approve')
                <div class="join-item badge badge-soft badge-warning">
                    เอกสารที่ต้องอนุมัติ
                </div>
            @elseif ($document['flag'] == 'my')
                <div class="join-item badge badge-soft badge-{{ $document['document_tag']['colour'] }}">
                    เอกสารของฉัน
                </div>
            @elseif ($document['flag'] == 'dept')
                <div class="join-item badge badge-soft badge-{{ $document['document_tag']['colour'] }}">
                    เอกสารที่จากแผนก
                </div>
            @endif
        </div>
        @if ($document['document_number'])
            <div class="badge badge-soft badge-{{ $document['document_tag']['colour'] }}">
                {{ $document['document_number'] }}
            </div>
        @endif
    </td>
    <td class="w-64">
        <div class="text-sm">{{ $document['document_type_name'] }}</div>
        <div>
            @if (is_array($document['title']))
                @foreach ($document['title'] as $title)
                    @continue($title === $document['document_type_name'] || $title === '')
                    {{ $title }} <br>
                @endforeach
            @elseif ($document['title'] && $document['title'] !== $document['document_type_name'])
                {{ $document['title'] }}
            @endif
        </div>
    </td>
    <td class="max-w-xs overflow-hidden truncate text-ellipsis whitespace-nowrap">
        {!! $document['detail'] !!}
    </td>
    <td>
        <div class="badge {{ $class }}">{{ $text }}</div>
    </td>
    <td>
        <div class="text-base-content/50 text-sm">
            {{ $document['created_at']->format('d/m/Y H:i') }}
        </div>
    </td>
    <td>
        @if ($document['flag'] == 'approve')
            <a class="btn btn-sm btn-accent"
                href="{{ route('document.type.approve', ['document_type' => $document['document_tag']['document_tag'], 'document_id' => $document['id']]) }}">อนุมัติ</a>
        @else
            <a class="btn btn-sm btn-primary"
                href="{{ route('document.type.view', ['document_type' => $document['document_tag']['document_tag'], 'document_id' => $document['id']]) }}">ดูเอกสาร</a>
        @endif
    </td>
</tr>
