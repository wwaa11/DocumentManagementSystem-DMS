<div class="card-body">
    <x-ui.back-button />
    <x-document.detail-masthead :document-type-name="$document->document_type_name" />
    <div class="divider"></div>
    <x-document.meta-grid
        :title="$document->title"
        :created-at="$document->created_at"
        :requester-name="$document->creator->name"
        :department="$document->creator->department"
        :phone="$document->document_phone"
    />
    @if ($document->files->count() > 0)
        <x-document.files-list :files="$document->files" />
        <div class="divider"></div>
    @endif
    <strong>รายละเอียด</strong>
    @if( (str_contains($document->detail, "<br>") || str_contains($document->detail, "\n")) )
    <p class="border-secondary rounded-md border p-4 whitespace-pre-wrap">{!! $document->detail !!}</p>
    @else
    <p class="border-secondary rounded-md border p-4">{{ $document->detail }}</p>
    @endif
    @foreach ($document->getAllDocuments() as $doc)
        @foreach ($doc->logs->where('action', 'process') as $log)
            <textarea class="textarea border-secondary rounded-md border p-4 w-full focus:outline-none" rows="10" readonly>{!! $log->details !!}</textarea>
            <div class="text-end text-xs text-gray-500">{{ $log->user->name }} {{ $log->created_at->format('d/m/Y H:i:s') }}</div>
        @endforeach
    @endforeach
    <div class="divider"></div>
    <strong>ผู้อนุมัติ</strong>
    @foreach ($document->getAllDocuments() as $key => $doc)
        <div class="card bg-base-100 mb-4 min-w-[450px] shadow-xl">
            <div class="bg-accent card-title flex cursor-pointer rounded-t-md p-3 text-white" onclick="toggleBody('body-{{ $key }}')">
                <div class="flex-1">
                    {{ $doc->document_number }}
                </div>
                <div>
                    {{ $doc->document_tag['document_tag'] }}
                </div>
                <div>
                    <i class="fas fa-caret-down" id="caret-{{ $key }}"></i>
                </div>
            </div>
            <div class="card-body hidden" id="body-{{ $key }}">
                @if ($doc->shouldDisplayChat())
                    <x-document.chat
                        document-type="USER"
                        :document-id="$doc->id"
                        :title="'สนทนา · ' . $doc->document_number"
                        :messages-url="route('document.messages.index', ['USER', $doc->id])"
                        :store-url="route('document.messages.store', ['USER', $doc->id])"
                    />
                    <div class="divider"></div>
                @endif
                <ul class="steps steps-vertical">
                    @foreach ($doc->tasks as $task)
                        @php
                            $stepClass = '';
                            $icon = 'fa-question';
                            if ($task->status == 'approve') {
                                $stepClass = 'step-success';
                                $icon = 'fa-check';
                            } elseif ($task->status == 'cancel' || $task->status == 'reject') {
                                $stepClass = 'step-error';
                                $icon = 'fa-times';
                            }
                        @endphp
                        <li class="step {{ $stepClass }}">
                            <span class="step-icon"><i class="fas {{ $icon }}"></i></span>
                            <div class="flex flex-col text-start">
                                <span class="fw-bold text-lg">{{ $task->task_name }}</span>
                                <span class="text-xs">{{ $task->task_user }} {{ $task->user->name ?? null }} ({{ $task->task_position }})</span>
                                <span class="text-xs">{{ $task->date ? date('d/m/Y H:i', strtotime($task->date)) : null }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</div>
@push('scripts')
    <script>
        function toggleBody(id) {
            document.getElementById(id).classList.toggle('hidden');
            document.getElementById('caret-' + id.split('-')[1]).classList.toggle('fa-caret-down');
            document.getElementById('caret-' + id.split('-')[1]).classList.toggle('fa-caret-up');
        }
    </script>
@endpush
