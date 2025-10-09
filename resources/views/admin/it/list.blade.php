@extends("layouts.app")
@section("content")
    <div class="mx-8">
        <h1 class="text-primary text-2xl font-bold">รายการเอกสาร</h1>
        <div class="divider"></div>
        <div class="border-base-content/5 bg-base-100 overflow-x-auto rounded-lg border">
            <table class="table">
                <thead>
                    <tr class="text-center">
                        <th>เลขที่</th>
                        <th>ชื่อเอกสาร</th>
                        <th>รายละเอียด</th>
                        <th>ผู้ขอ/วันที่ขอ</th>
                        <th>ผู้อนุมัติ</th>
                        <th>สถานะ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                        <tr class="hover:bg-base-300">
                            <td class="text-center">{{ $document->document_number }}</td>
                            <td class="text-xs">
                                {{ $document->document_type_name }} <br>
                                @if (is_array($document->title))
                                    @foreach ($document->title as $title)
                                        {{ $title }}<br>
                                    @endforeach
                                @else
                                    {{ $document->title }}
                                @endif
                            </td>
                            <td class="text-xs">{!! $document->ListDetail !!}</td>
                            <td>
                                {{ $document->creator->name }}<br>
                                {{ $document->created_at->format("d/m/Y H:i:s") }}
                            </td>
                            <td class="text-xs">
                                @foreach ($document->approvers as $approver)
                                    @if ($approver->status == "approve")
                                        <i class="fas fa-check text-primary"></i>
                                    @elseif($approver->status == "reject" || $approver->status == "cancel")
                                        <i class="fas fa-times text-error"></i>
                                    @else
                                        <i class="fas fa-hourglass-half text-ghost"></i>
                                    @endif
                                    {{ $approver->user->name ?? $approver->userid }}
                                @endforeach
                            </td>
                            <td>
                                {{ $document->status }}
                            </td>
                            <td>
                                @include("admin.it.list_buttons.$action")
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
