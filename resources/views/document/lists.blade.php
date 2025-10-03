@extends("layouts.app")
@section("content")
    <h1 class="flex-1 text-center">รายการเอกสาร</h1>
    @if (session("success"))
        <div class="alert alert-success" role="alert">
            <span class="font-bold">Success!</span>
            <span>{{ session("success") }}</span>
        </div>
    @endif
    <div class="divider"></div>
    <div class="overflow-x-auto">
        <table class="table-zebra table w-full">
            <thead>
                <tr>
                    <th>Document Number</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Document Type</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($my_documents as $document)
                    <tr>
                        <td>{{ $document->document_number }}</td>
                        <td>{{ $document->title }}</td>
                        <td>{{ $document->status }}</td>
                        <td>{{ $document->document_type }}</td>
                        <td>{{ $document->created_at }}</td>
                        <td>
                            <a class="btn btn-sm btn-primary" href="#">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
