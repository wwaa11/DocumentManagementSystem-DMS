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
        <h1 class="text-primary text-2xl font-bold">เอกสารทั้งหมด <span class="float-end"><a class="btn btn-soft btn-primary" href="{{ route("document.create") }}"><i class="fa fa-plus"></i>สร้างเอกสารใหม่</a></span></h1>
        <span class="countdown font-mono text-sm">Refesh in <span class="bg-base-300 mx-2 rounded-md px-2" id="countdown" style="--value:30;"></span> seconds</span>
        <div class="divider"></div>
        <form class="mb-4" action="{{ route("document.index") }}" method="GET">
            <div class="bg-base-200 rounded-box p-4 shadow-sm">
                <div class="flex flex-row flex-wrap items-center gap-4">
                    <div class="flex flex-1 flex-row gap-2">
                        <input class="input input-bordered flex-1" type="text" name="document_number" placeholder="Search Document Number" value="{{ request("document_number") }}">
                        <input class="input input-bordered flex-1" type="text" name="detail" placeholder="Search Details/Keywords" value="{{ request("detail") }}">
                    </div>

                    <div class="flex gap-2">
                        <button class="btn btn-primary" type="submit">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </button>

                        <label class="btn btn-outline btn-secondary" for="filter_toggle">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filters
                        </label>
                    </div>
                </div>
                <input class="peer hidden" id="filter_toggle" type="checkbox" />
                <div class="border-base-300 mt-6 hidden flex-col gap-6 border-t pt-6 peer-checked:flex">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold">ประเภทเอกสาร</span></label>
                            <select class="select select-bordered w-full" name="document_tag">
                                <option value="">ทั้งหมด</option>
                                <option value="IT" {{ request("document_tag") == "IT" ? "selected" : "" }}>IT</option>
                                <option value="USER" {{ request("document_tag") == "USER" ? "selected" : "" }}>USER</option>
                                <option value="Training" {{ request("document_tag") == "Training" ? "selected" : "" }}>Training</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold">สถานะเอกสาร</span></label>
                            <select class="select select-bordered w-full" name="status">
                                <option value="">ทั้งหมด</option>
                                <option value="wait_approval" {{ request("status") == "wait_approval" ? "selected" : "" }}>รออนุมัติจากหัวหน้าแผนก</option>
                                <option value="not_approval" {{ request("status") == "not_approval" ? "selected" : "" }}>เอกสารที่ไม่อนุมัติ</option>
                                <option value="cancel" {{ request("status") == "cancel" ? "selected" : "" }}>เอกสารที่ถูกยกเลิก</option>
                                <option value="pending" {{ request("status") == "pending" ? "selected" : "" }}>รอดำเนินการจากหน่วยงาน</option>
                                <option value="borrow_approve" {{ request("status") == "borrow_approve" ? "selected" : "" }}>รออนุมัติการยืมอุปกรณ์</option>
                                <option value="borrow" {{ request("status") == "borrow" ? "selected" : "" }}>อุปกรณ์อยู่ระหว่างการยืม</option>
                                <option value="reject" {{ request("status") == "reject" ? "selected" : "" }}>เอกสารที่ถูกปฏิเสธจากหน่วยงาน</option>
                                <option value="process" {{ request("status") == "process" ? "selected" : "" }}>เอกสารที่กำลังดำเนินการ</option>
                                <option value="done" {{ request("status") == "done" ? "selected" : "" }}>เอกสารที่รออนุมัติ</option>
                                <option value="complete" {{ request("status") == "complete" ? "selected" : "" }}>เอกสารที่เสร็จสมบูรณ์</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold">ช่วงวันที่สร้าง</span></label>
                            <div class="join w-full">
                                <input class="join-item input input-bordered w-1/2" type="date" name="created_at_start" value="{{ request("created_at_start") }}">
                                <input class="join-item input input-bordered w-1/2" type="date" name="created_at_end" value="{{ request("created_at_end") }}">
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-100 flex flex-row flex-wrap gap-6 rounded-lg p-4">
                        <label class="label cursor-pointer gap-2">
                            <input class="radio radio-primary" onchange="this.form.submit()" type="radio" name="flag" value="" {{ request("flag") == "" ? "checked" : "" }}>
                            <span class="label-text">เอกสารทั้งหมด</span>
                        </label>
                        <label class="label cursor-pointer gap-2">
                            <input class="radio radio-primary" onchange="this.form.submit()" type="radio" name="flag" value="my" {{ request("flag") == "my" ? "checked" : "" }}>
                            <span class="label-text">เอกสารของฉัน</span>
                        </label>
                        <label class="label cursor-pointer gap-2">
                            <input class="radio radio-primary" onchange="this.form.submit()" type="radio" name="flag" value="approve" {{ request("flag") == "approve" ? "checked" : "" }}>
                            <span class="label-text">เอกสารที่ต้องอนุมัติ</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a class="btn btn-ghost" href="{{ route("document.index") }}">Clear All Filters</a>
                        <button class="btn btn-primary px-8" type="submit">Apply Advanced Filters</button>
                    </div>
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
                                @php
                                    switch ($document["status"]) {
                                        case "wait_approval":
                                            $text = "รออนุมัติจากหน่วยงาน";
                                            $class = "badge-soft badge-warning";
                                            break;
                                        case "not_approval":
                                            $text = "หน่วยงานไม่อนุมัติ";
                                            $class = "badge-soft badge-error";
                                            break;
                                        case "cancel":
                                            $text = "ผู้ขอยกเลิกเอกสาร";
                                            $class = "badge-soft badge-error";
                                            break;
                                        case "pending":
                                            $text = "รอการดำเนินการ";
                                            $class = "badge-soft badge-warning";
                                            break;
                                        case "reject":
                                            $text = "ยกเลิกเอกสาร";
                                            $class = "badge-soft badge-error";
                                            break;
                                        case "process":
                                            $text = "กำลังดำเนินการ";
                                            $class = "badge-soft badge-warning";
                                            break;
                                        case "done":
                                            $text = "เอกสารรออนุมัติ";
                                            $class = "badge-soft badge-secondary";
                                            break;
                                        case "complete":
                                            $text = "เอกสารเสร็จสมบูรณ์";
                                            $class = "badge-soft badge-success";
                                            break;
                                        case "borrow_approve":
                                            $text = "รออนุมัติการยืมอุปกรณ์";
                                            $class = "badge-soft badge-secondary";
                                            break;
                                        case "borrow":
                                            $text = "อุปกรณ์อยู่ระหว่างการยืม";
                                            $class = "badge-soft badge-neutral";
                                            break;
                                        default:
                                            $text = "";
                                            $class = "";
                                    }
                                @endphp
                                <div class="badge {{ $class }}">{{ $text }}</div>
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
