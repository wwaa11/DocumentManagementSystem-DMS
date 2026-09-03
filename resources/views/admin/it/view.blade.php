@extends('layouts.app')
@section('content')
    <div class="mb-3 flex justify-end no-print">
        <x-document.print-button />
    </div>
    <div class="document-print-area justify-center gap-3 lg:flex">
        <div class="card bg-base-100 mb-4 max-w-3xl shadow-xl">
            @include('document.it.detail')
        </div>
        <div class="card bg-base-100 mb-4 max-w-xl shadow-xl">
            <div class="card-body">
                <x-document.process-logs :document="$document" />
                @if (($type === 'IT' || $type === 'USER') && $document->shouldDisplayChat())
                    <div class="divider"></div>
                    <x-document.chat
                        :document-type="$type"
                        :document-id="$document->id"
                        :messages-url="route('admin.it.messages.index', [$type, $document->id])"
                        :store-url="route('admin.it.messages.store', [$type, $document->id])"
                        :show-pending-action="$action === 'my' && $document->assigned_user_id === auth()->user()->userid && $document->status === 'process'"
                        :pending-url="route('admin.it.set-pending')"
                    />
                @endif
                <div class="no-print">
                    @include("admin.it.actions.$action")
                </div>
                <div class="divider"></div>
                <x-document.activity-logs :logs="$document->logs" />
            </div>
        </div>
    </div>
@endsection
