@extends("layouts.app")
@section("content")
    <div class="mx-8">
        <h1 class="text-primary text-2xl font-bold">เอกสารทั้งหมด</h1>
        @if (session("success"))
            <div class="alert alert-success" role="alert">
                <span class="font-bold">Success!</span>
                <span>{{ session("success") }}</span>
            </div>
        @endif
        <div class="divider"></div>
        <p class="text-base-content/50 text-sm">
            แสดง {{ $documents->firstItem() }} ถึง {{ $documents->lastItem() }} จาก {{ $documents->total() }} รายการ
        </p>
        <div class="border-base-content/5 bg-base-100 overflow-x-auto rounded-lg border">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th></th>
                        <th>หมายเลขเอกสาร</th>
                        <th>ประเภทเอกสาร</th>
                        <th>รายละเอียด</th>
                        <th>สถานะ</th>
                        <th>วันที่สร้าง</th>
                        <th>#</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                        <tr class="hover:bg-base-300">
                            <td class="flex flex-col gap-2">
                                <div class="badge badge-{{ $document["document_tag"]["colour"] }}">{{ $document["document_tag"]["document_tag"] }}</div>
                                @if ($document["flag"] == "approve")
                                    <div class="badge badge-accent">เอกสารที่ต้องอนุมัติ</div>
                                @elseif($document["flag"] == "my")
                                    <div class="badge badge-primary">เอกสารของฉัน</div>
                                @endif
                            </td>
                            <td>{{ $document["document_number"] }}</td>
                            <td>
                                <div class="text-sm">{{ $document["document_type_name"] }}</div>
                                <div>{{ $document["title"] }}</div>
                            </td>
                            <td class="max-w-xs overflow-hidden truncate text-ellipsis whitespace-nowrap">{!! $document["detail"] !!}</td>
                            <td>{{ $document["status"] }}</td>
                            <td>
                                <div class="text-base-content/50 text-sm">
                                    {{ $document["created_at"]->format("d/m/Y H:i") }}
                                </div>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route("document.type.view", ["document_type" => $document["document_tag"]["document_tag"], "document_id" => $document["id"]]) }}">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="divider"></div>
        <div class="flex justify-end">
            {{ $documents->links() }}
        </div>
    </div>
@endsection
