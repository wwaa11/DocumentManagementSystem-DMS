<div class="divider"></div>
<strong>ผู้อนุมัติ</strong>
<ul class="steps steps-vertical">
    @foreach ($tasks as $task)
        @php
            $stepClass = "";
            $icon = "fa-question";
            if ($task->status == "approve") {
                $stepClass = "step-success";
                $icon = "fa-check";
            } elseif ($task->status == "cancel") {
                $stepClass = "step-error";
                $icon = "fa-times";
            }
        @endphp
        <li class="step {{ $stepClass }}">
            <span class="step-icon"><i class="fas {{ $icon }}"></i></span>
            <div class="flex flex-col text-start">
                <span class="fw-bold text-lg">{{ $task->task_name }}</span>
                <span class="text-xs">{{ $task->task_user }} {{ $task->user->name ?? null }} ({{ $task->task_position }})</span>
                <span class="text-xs">{{ $task->date ? date("d/m/Y H:i", strtotime($task->date)) : null }}</span>
            </div>
        </li>
    @endforeach
</ul>
