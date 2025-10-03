@extends("layouts.app")
@section("content")
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ $document->document_tag["document_tag"] }}</h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ $document->document_number }}</p>
                        <p class="card-text">{{ $document->document_type_name }}</p>
                        <h5 class="card-title">Files</h5>
                        <p class="card-text">{{ $document->files }}</p>
                        <p class="card-text">{{ $document->title }}</p>
                        <p class="card-text">{!! $document->detail !!}</p>
                        <p class="card-text">{{ $document->status }}</p>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Approvers</h5>
                        <p class="card-text">{{ $document->approvers }}</p>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Tasks</h5>
                        <p class="card-text">{{ $document->tasks }}</p>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Logs</h5>
                        <p class="card-text">{{ $document->logs }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push("scripts")
@endpush
