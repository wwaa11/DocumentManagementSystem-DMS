@extends('layouts.app')
@section('content')
    <div class="mb-3 flex justify-end no-print">
        <x-document.print-button />
    </div>
    <div class="document-print-area justify-center gap-3 lg:flex">
        <div class="card bg-base-100 mb-4 max-w-3xl shadow-xl">
            @include('document.media.detail')
        </div>
        <div class="card bg-base-100 mb-4 max-w-xl shadow-xl">
            <div class="card-body">
                <x-document.process-logs :document="$document" />
                <div class="no-print">
                    @include("admin.media.actions.$action")
                </div>
                <div class="divider"></div>
                <x-document.activity-logs :logs="$document->logs" />
            </div>
        </div>
    </div>
@endsection
