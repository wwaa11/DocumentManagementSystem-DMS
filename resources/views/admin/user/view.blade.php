@extends('layouts.app')
@section('content')
    <div class="justify-center gap-3 lg:flex">
        <div class="card bg-base-100 mb-4 shadow-xl max-w-2xl">
            <div class="card-body">
                <x-ui.back-button />
                <x-document.detail-masthead
                    :document-number="$document->document_number"
                    :document-type-name="$document->document_type_name"
                />
                <div class="divider"></div>
                <x-document.meta-grid
                    :title="$document->documentUser->document_type_name"
                    :created-at="$document->created_at"
                    :requester-name="$document->documentUser->creator->name"
                    :department="$document->documentUser->creator->department"
                    :phone="$document->documentUser->document_phone"
                />
                @if ($document->documentUser->files->count() > 0)
                    <x-document.files-list :files="$document->documentUser->files" />
                    <div class="divider"></div>
                @endif
                <strong>รายละเอียด</strong>
                <p class="border-secondary min-h-48 rounded-md border p-4">{!! $document->detail ?? $document->documentUser->detail !!}</p>
                <x-document.task-timeline :tasks="$document->tasks" />
            </div>
        </div>
        <div class="card bg-base-100 mb-4 shadow-xl max-w-xl">
            <div class="card-body">
                <h5 class="card-title">รายการดำเนินงาน</h5>
                @if ($document->logs()->where('action', 'process')->count() > 0)
                    @foreach ($document->logs()->whereIn('action', ['process', 'reject'])->get() as $log)
                        @php
                            $actionCss = $log->action == 'process' ? 'primary' : 'accent';
                        @endphp
                        <div class="rounded-box border-{{ $actionCss }} w-full border p-2">
                            <textarea class="textarea w-full border-0 focus:outline-none" rows="10" readonly>{!! $log->details !!}</textarea>
                            <div class="text-{{ $actionCss }} flex justify-between text-xs">
                                <div>{{ $log->userid }} {{ $log->user->name }}</div>
                                <div>{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif
                @foreach ($document->documentUser->gettAlllogs() as $item)
                    <div class="rounded-box border-accent text-accent w-full border p-2">
                        <div>การดำเนินการจากแผนก IT</div>
                        <div class="py-3">{{ $item->details }}</div>
                        <div class="flex justify-between text-xs">
                            <div>{{ $item->user->name ?? $item->userid }}</div>
                            <div>{{ $item->created_at->format('d/m/Y H:i:s') }}</div>
                        </div>
                    </div>
                @endforeach
                @include("admin.user.actions.$action")
                <div class="divider"></div>
                <x-document.activity-logs :logs="$document->logs" />
            </div>
        </div>
    </div>
@endsection
