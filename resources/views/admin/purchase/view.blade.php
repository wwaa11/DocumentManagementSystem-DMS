@extends('layouts.app')
@section('content')
    <div class="justify-center gap-3 lg:flex">
        <div class="card bg-base-100 mb-4 shadow-xl max-w-2xl">
            @include('document.purchase.detail')
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
                                <div>{{ $log->userid }} {{ $log->user->name ?? '' }}</div>
                                <div>{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif
                @include("admin.purchase.actions.$action")
                <div class="divider"></div>
                <x-document.activity-logs :logs="$document->logs" />
            </div>
        </div>
    </div>
@endsection
