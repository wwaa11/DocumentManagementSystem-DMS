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
        <h1 class="text-primary text-2xl font-bold">เอกสารทั้งหมด <span class="float-end"><a class="btn btn-accent" href="{{ route("document.create") }}">สร้างเอกสารใหม่</a></span></h1>
        <span class="countdown font-mono text-sm">Refesh in <span class="bg-base-300 mx-2 rounded-md px-2" id="countdown" style="--value:30;"></span> seconds</span>
        <div class="divider"></div>
        <form class="mb-4" action="{{ route("document.index") }}" method="GET">
            <div class="bg-base-200 rounded-box flex flex-row flex-wrap gap-4 p-4">
                <input class="input input-bordered" type="text" name="document_number" placeholder="Search Document Number" value="{{ request("document_number") }}">
                <select class="select select-bordered" name="document_tag">
                    <option value="">ประเภทเอกสาร</option>
                    <option value="IT" {{ request("document_tag") == "IT" ? "selected" : "" }}>IT</option>
                    <option value="USER" {{ request("document_tag") == "USER" ? "selected" : "" }}>USER</option>
                </select>
                <select class="select select-bordered" name="status">
                    <option value="">สถานะเอกสาร</option>
                    <option value="wait_approval" {{ request("status") == "wait_approval" ? "selected" : "" }}>รออนุมัติจากหัวหน้าแผนก</option>
                    <option value="not_approval" {{ request("status") == "not_approval" ? "selected" : "" }}>เอกสารที่ไม่อนุมัติ</option>
                    <option value="cancel" {{ request("status") == "cancel" ? "selected" : "" }}>เอกสารที่ถูกยกเลิก</option>
                    <option value="pending" {{ request("status") == "pending" ? "selected" : "" }}>รอดำเนินการจากหน่วยงาน</option>
                    <option value="reject" {{ request("status") == "reject" ? "selected" : "" }}>เอกสารที่ถูกปฏิเสธจากหน่วยงาน</option>
                    <option value="process" {{ request("status") == "process" ? "selected" : "" }}>เอกสารที่กำลังดำเนินการ</option>
                    <option value="done" {{ request("status") == "done" ? "selected" : "" }}>เอกสารที่รออนุมัติ</option>
                    <option value="complete" {{ request("status") == "complete" ? "selected" : "" }}>เอกสารที่เสร็จสมบูรณ์</option>
                </select>
                <div class="join flex-1">
                    <input class="join-item input input-bordered w-full max-w-xs" type="date" name="created_at_start" value="{{ request("created_at_start") }}">
                    <input class="join-item input input-bordered w-full max-w-xs" type="date" name="created_at_end" value="{{ request("created_at_end") }}">
                </div>
                <div class="my-auto flex flex-row gap-3">
                    <label class="inline-flex items-center">
                        <input class="radio radio-xs radio-primary" onchange="this.form.submit()" type="radio" name="flag" value="" {{ request("flag") == "" ? "checked" : "" }}>
                        <span class="ml-2">เอกสารทั้งหมด</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input class="radio radio-xs radio-primary" onchange="this.form.submit()" type="radio" name="flag" value="my" {{ request("flag") == "my" ? "checked" : "" }}>
                        <span class="ml-2">เอกสารของฉัน</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input class="radio radio-xs radio-primary" onchange="this.form.submit()" type="radio" name="flag" value="dept" {{ request("flag") == "dept" ? "checked" : "" }}>
                        <span class="ml-2">เอกสารที่จากแผนก</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input class="radio radio-xs radio-primary" onchange="this.form.submit()" type="radio" name="flag" value="approve" {{ request("flag") == "approve" ? "checked" : "" }}>
                        <span class="ml-2">เอกสารที่ต้องอนุมัติ</span>
                    </label>
                </div>
                <div class="my-auto flex flex-1 flex-row justify-end gap-3">
                    <a class="btn btn-ghost" href="{{ route("document.index") }}">Clear Filters</a>
                    <button class="btn btn-primary" type="submit">Apply Filters</button>
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
                            <td class="flex flex-col gap-1">
                                <div class="join">
                                    <div class="join-item badge badge-soft badge-{{ $document["document_tag"]["colour"] }}">{{ $document["document_tag"]["document_tag"] }}</div>
                                    @if ($document["flag"] == "approve")
                                        <div class="join-item badge badge-soft badge-{{ $document["document_tag"]["colour"] }}">เอกสารที่ต้องอนุมัติ</div>
                                    @elseif($document["flag"] == "my")
                                        <div class="join-item badge badge-soft badge-{{ $document["document_tag"]["colour"] }}">เอกสารของฉัน</div>
                                    @elseif($document["flag"] == "dept")
                                        <div class="join-item badge badge-soft badge-{{ $document["document_tag"]["colour"] }}">เอกสารที่จากแผนก</div>
                                    @endif
                                </div>
                                @if ($document["document_number"])
                                    <div class="badge badge-soft badge-{{ $document["document_tag"]["colour"] }}">
                                        {{ $document["document_number"] }}
                                    </div>
                                @endif
                            </td>
                            <td class="w-64">
                                <div class="text-sm">{{ $document["document_type_name"] }}</div>
                                <div>
                                    @if (is_array($document["title"]))
                                        @foreach ($document["title"] as $title)
                                            {{ $title }} <br>
                                        @endforeach
                                    @else
                                        {{ $document["title"] }}
                                    @endif
                                </div>
                            </td>
                            <td class="max-w-xs overflow-hidden truncate text-ellipsis whitespace-nowrap">{!! $document["detail"] !!}</td>
                            <td>
                                @switch($document["status"])
                                    @case("wait_approval")
                                        <div class="badge badge-soft badge-accent">รออนุมัติจากหัวหน้าแผนก</div>
                                    @break

                                    @case("cancel")
                                        <div class="badge badge-soft badge-grey">เอกสารที่ถูกยกเลิก</div>
                                    @break

                                    @case("not_approval")
                                        <div class="badge badge-soft badge-error">เอกสารที่ไม่อนุมัติ</div>
                                    @break

                                    @case("pending")
                                        <div class="badge badge-soft badge-warning">รอดำเนินการจากหน่วยงาน</div>
                                    @break

                                    @case("reject")
                                        <div class="badge badge-soft badge-error">เอกสารที่ถูกปฏิเสธจากหน่วยงาน</div>
                                    @break

                                    @case("process")
                                        <div class="badge badge-soft badge-grey">เอกสารที่กำลังดำเนินการ</div>
                                    @break

                                    @case("done")
                                        <div class="badge badge-soft badge-grey">เอกสารที่รออนุมัติ</div>
                                    @break

                                    @case("complete")
                                        <div class="badge badge-soft badge-success">เอกสารที่เสร็จสมบูรณ์</div>
                                    @break
                                @endswitch
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
    <script>
        let seconds = 30;

        function countdown() {
            document.getElementById('countdown').style.setProperty('--value', seconds);
            if (seconds === 0) {
                location.reload();
            } else {
                seconds--;
                setTimeout(countdown, 1000);
            }
        }
        countdown();
    </script>
@endpush
