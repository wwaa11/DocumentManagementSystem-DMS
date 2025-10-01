@extends("layouts.app")
@section("content")
    <h1 class="flex-1 text-center">รายการเอกสาร</h1>
    <div class="divider"></div>
    <div class="flex justify-center gap-4">
        @foreach ($my_documents as $document)
            <div class="card bg-primary/50 vertical-center m-auto">
                <a href="{{ route("document.create", $document->document_type) }}">
                    <div class="card-body flex-1 text-center">{{ $document->document_type }}</div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
