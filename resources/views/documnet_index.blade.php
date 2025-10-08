@extends("layouts.app")
@section("content")
    <div class="hidden">
        {{-- temp CSS load --}}
        <div class="badge badge-primary">Primary</div>
        <div class="badge badge-secondary">Secondary</div>
        <div class="badge badge-accent">Accent</div>
        <div class="badge badge-neutral">Neutral</div>
        <div class="badge badge-info">Info</div>
        <div class="badge badge-success">Success</div>
        <div class="badge badge-warning">Warning</div>
        <div class="badge badge-error">Error</div>
        <div class="badge badge-outline badge-primary">Primary</div>
        <div class="badge badge-outline badge-secondary">Secondary</div>
        <div class="badge badge-outline badge-accent">Accent</div>
        <div class="badge badge-outline badge-info">Info</div>
        <div class="badge badge-outline badge-success">Success</div>
        <div class="badge badge-outline badge-warning">Warning</div>
        <div class="badge badge-outline badge-error">Error</div>
    </div>
    <div class="mx-8">
        <h1 class="text-primary text-2xl font-bold">เอกสารทั้งหมด</h1>
        <div class="divider"></div>
        <form class="mb-4" action="{{ route("document.index") }}" method="GET">
            <div class="bg-base-200 rounded-box flex flex-wrap gap-4 p-4">
                <input class="input input-bordered w-full max-w-xs" type="text" name="document_number" placeholder="Search Document Number" value="{{ request("document_number") }}">
                <select class="select select-bordered w-full max-w-xs" name="document_tag">
                    <option value="">ประเภทเอกสาร</option>
                    <option value="IT" {{ request("document_tag") == "IT" ? "selected" : "" }}>IT</option>
                    <option value="HCLAB" {{ request("document_tag") == "HCLAB" ? "selected" : "" }}>HCLAB</option>
                    <option value="PAC" {{ request("document_tag") == "PAC" ? "selected" : "" }}>PAC</option>
                </select>
                <select class="select select-bordered w-full max-w-xs" name="status">
                    <option value="">สถานะเอกสาร</option>
                    <option value="wait_approval" {{ request("status") == "wait_approval" ? "selected" : "" }}>รออนุมัติจากหัวหน้าแผนก</option>
                    <option value="not_approval" {{ request("status") == "not_approval" ? "selected" : "" }}>เอกสารที่ไม่อนุมัติ</option>
                    <option value="pending" {{ request("status") == "pending" ? "selected" : "" }}>รอดำเนินการจากหน่วยงาน</option>
                    <option value="reject" {{ request("status") == "reject" ? "selected" : "" }}>เอกสารที่ถูกปฏิเสธจากหน่วยงาน</option>
                    <option value="process" {{ request("status") == "process" ? "selected" : "" }}>เอกสารที่กำลังดำเนินการ</option>
                    <option value="done" {{ request("status") == "done" ? "selected" : "" }}>เอกสารที่รออนุมัติ</option>
                    <option value="finish" {{ request("status") == "finish" ? "selected" : "" }}>เอกสารที่เสร็จสมบูรณ์</option>
                </select>
                <div class="join w-xs">
                    <input class="join-item input input-bordered w-full max-w-xs" type="date" name="created_at_start" value="{{ request("created_at_start") }}">
                    <input class="join-item input input-bordered w-full max-w-xs" type="date" name="created_at_end" value="{{ request("created_at_end") }}">
                </div>
                <div class="flex flex-row gap-2">
                    <label class="inline-flex items-center">
                        <input class="radio radio-xs radio-primary" type="radio" name="flag" value="" {{ request("flag") == "" ? "checked" : "" }}>
                        <span class="ml-2">เอกสารทั้งหมด</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input class="radio radio-xs radio-primary" type="radio" name="flag" value="my" {{ request("flag") == "my" ? "checked" : "" }}>
                        <span class="ml-2">เอกสารของฉัน</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input class="radio radio-xs radio-primary" type="radio" name="flag" value="approve" {{ request("flag") == "approve" ? "checked" : "" }}>
                        <span class="ml-2">เอกสารที่ต้องอนุมัติ</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input class="radio radio-xs radio-primary" type="radio" name="flag" value="approved" {{ request("flag") == "approved" ? "checked" : "" }}>
                        <span class="ml-2">เอกสารที่อนุมัติ</span>
                    </label>
                </div>
                <div class="flex flex-1 flex-row justify-end gap-2">
                    <button class="btn btn-primary" type="submit">Apply Filters</button>
                    <a class="btn btn-ghost" href="{{ route("document.index") }}">Clear Filters</a>
                </div>
            </div>
        </form>
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
                        <th class="text-center">#</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                        <tr class="hover:bg-base-300">
                            <td class="flex flex-col gap-2">
                                <div class="badge badge-{{ $document["document_tag"]["colour"] }}">{{ $document["document_tag"]["document_tag"] }}</div>
                                @if ($document["flag"] == "approve")
                                    <div class="badge badge-outline badge-accent">เอกสารที่ต้องอนุมัติ</div>
                                @elseif($document["flag"] == "approved")
                                    <div class="badge badge-outline badge-accent">เอกสารที่อนุมัติ</div>
                                @elseif($document["flag"] == "my")
                                    <div class="badge badge-outline badge-primary">เอกสารของฉัน</div>
                                @endif
                            </td>
                            <td>{{ $document["document_number"] }}</td>
                            <td>
                                <div class="text-sm">{{ $document["document_type_name"] }}</div>
                                <div>{{ $document["title"] }}</div>
                            </td>
                            <td class="max-w-xs overflow-hidden truncate text-ellipsis whitespace-nowrap">{!! $document["detail"] !!}</td>
                            <td>
                                @php
                                    switch ($document["status"]) {
                                        case "wait_approval":
                                            $status = "รออนุมัติจากหัวหน้าแผนก";
                                            break;
                                        case "not_approval":
                                            $status = "เอกสารที่ไม่อนุมัติ";
                                            break;
                                        case "pending":
                                            $status = "รอดำเนินการจากหน่วยงาน";
                                            break;
                                        case "reject":
                                            $status = "เอกสารที่ถูกปฏิเสธจากหน่วยงาน";
                                            break;
                                        case "process":
                                            $status = "เอกสารที่กำลังดำเนินการ";
                                            break;
                                        case "done":
                                            $status = "เอกสารที่รออนุมัติ";
                                            break;
                                        case "finish":
                                            $status = "เอกสารที่เสร็จสมบูรณ์";
                                            break;
                                        default:
                                            $status = $document["status"];
                                            break;
                                    }
                                @endphp
                                {{ $status }}
                            </td>
                            <td>
                                <div class="text-base-content/50 text-sm">
                                    {{ $document["created_at"]->format("d/m/Y H:i") }}
                                </div>
                            </td>
                            <td>
                                @if ($document["flag"] == "approve")
                                    <a class="btn btn-sm btn-accent" href="{{ route("document.type.approve", ["document_type" => $document["document_tag"]["document_tag"], "document_id" => $document["id"]]) }}">อนุมัติ</a>
                                @else
                                    <a class="btn btn-sm btn-primary" href="{{ route("document.type.view", ["document_type" => $document["document_tag"]["document_tag"], "document_id" => $document["id"]]) }}">ดูเอกสาร</a>
                                @endif
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
@push("scripts")
    @if (session("success"))
        <script type="module">
            Swal.fire({
                icon: "success",
                title: "Success",
                text: "{{ session("success") }}",
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        </script>
    @endif
    @if (session("error"))
        <script type="module">
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "{{ session("error") }}",
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        </script>
    @endif
@endpush
