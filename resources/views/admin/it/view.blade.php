@extends("layouts.app")
@section("content")
    <div class="justify-center gap-3 lg:flex">
        @include("document.it.detail")
        <div class="card bg-base-100 mb-4 shadow-xl">
            <div class="card-body">
                @include("admin.it.view_actions.$action")
                @include("document.logs", ["logs" => $document->logs])
            </div>
        </div>
    </div>
@endsection
