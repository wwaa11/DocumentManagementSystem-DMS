@extends("layouts.app")
@section("content")
    <div class="justify-center gap-3 lg:flex">
        <div class="card bg-base-100 mb-4 shadow-xl">
            <div class="card-body">
                <button class="text-accent w-24 cursor-pointer" onclick="window.history.back()"> <i class="fas fa-arrow-left"></i> ย้อนกลับ</button>
                <div class="flex items-center">
                    <img class="mr-4 h-auto w-36" src="{{ asset("images/Side Logo.png") }}" alt="Side Logo">
                    <div class="flex-1 text-end">
                        <h2 class="text-2xl font-bold">QF-ITD-09/Rev.3 (15-06-66)</h2>
                        <p class="text-sm text-gray-500">เลขที่เอกสาร: {{ $document->document_number }}</p>
                        <p class="text-sm text-gray-500">ประเภทเอกสาร: {{ $document->document_type_name }}</p>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <p><strong>เรื่อง:</strong>{{ $document->documentUser->document_type_name }}</p>
                        <p><strong>วันที่:</strong> {{ $document->created_at->format("d/m/Y") }}</p>
                    </div>
                    <div>
                        <p><strong>ผู้ขอ:</strong> {{ $document->documentUser->creator->name }}</p>
                        <p><strong>แผนก:</strong> {{ $document->documentUser->creator->department }}</p>
                        <p><strong>เบอร์โทร:</strong> {{ $document->documentUser->document_phone }}</p>
                    </div>
                </div>
                @if ($document->documentUser->files->count() > 0)
                    @include("document.files", ["files" => $document->documentUser->files])
                    <div class="divider"></div>
                @endif
                <strong>รายละเอียด</strong>
                <p class="border-secondary min-h-48 rounded-md border p-4">{!! $document->detail ?? $document->documentUser->detail !!}</p>
                @include("document.tasks", ["tasks" => $document->tasks])
            </div>
        </div>
        <div class="card bg-base-100 mb-4 shadow-xl">
            <div class="card-body">
                <h5 class="card-title">รายการดำเนินงาน</h5>
                @if ($document->logs()->where("action", "process")->count() > 0)
                    @foreach ($document->logs()->whereIn("action", ["process", "reject"])->get() as $log)
                        @php
                            $actionCss = $log->action == "process" ? "primary" : "accent";
                        @endphp
                        <div class="rounded-box border-{{ $actionCss }} w-full border p-2">
                            <textarea class="textarea w-full border-0 focus:outline-none" readonly>{!! $log->details !!}</textarea>
                            <div class="text-{{ $actionCss }} flex justify-between text-xs">
                                <div>{{ $log->userid }} {{ $log->user->name }}</div>
                                <div>{{ $log->created_at->format("Y-m-d H:i:s") }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif
                @foreach ($document->documentUser->gettAlllogs() as $item)
                    <div class="rounded-box border-accent text-accent w-full border p-2">
                        <div>การดำเนินการจากแผนก IT</div>
                        <div class="py-3">{{ $item->details }}</div>
                        <div class="flex justify-between text-xs">
                            <div>{{ $item->user->name }}</div>
                            <div>{{ $item->created_at->format("d/m/Y H:i:s") }}</div>
                        </div>
                    </div>
                @endforeach
                @include("admin.user.actions.$action")
                <div class="divider"></div>
                @include("document.logs", ["logs" => $document->logs])
            </div>
        </div>
    </div>
@endsection
