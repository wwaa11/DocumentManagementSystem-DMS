@extends("layouts.app")
@section("content")
    <div class="justify-center gap-3 lg:flex">
        <div class="card bg-base-100 mb-4 shadow-xl">
            @include("document.it.detail")
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
                @include("admin.it.actions.$action")
                <div class="divider"></div>
                @include("document.logs", ["logs" => $document->logs])
            </div>
        </div>
    </div>
@endsection
