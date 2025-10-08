@extends("layouts.app")
@section("content")
    <div class="justify-center gap-3 lg:flex">
        @if ($document_type == "IT" || $document_type == "HCLAB" || $document_type == "PAC")
            @include("document.it.detail")
        @endif
        <div class="card bg-base-100 mb-4 shadow-xl">
            <div class="card-body">
                @include("document.component.requester_action")
                @include("document.logs", ["logs" => $document->logs])
            </div>
        </div>
    </div>
@endsection
