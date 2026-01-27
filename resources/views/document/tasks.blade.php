<div class="divider text-base-content/30 text-xs font-bold uppercase tracking-widest">การอนุมัติและสถานะ</div>

<div class="space-y-4">
    <div class="mb-2 flex items-center gap-2">
        <div class="bg-primary/10 rounded-lg p-2">
            <i class="fas fa-signature text-primary"></i>
        </div>
        <strong class="text-sm font-bold uppercase tracking-wider">ลำดับการอนุมัติ</strong>
    </div>

    <div class="before:bg-base-300 relative space-y-6 pl-8 before:absolute before:bottom-2 before:left-[15px] before:top-2 before:w-[2px]">
        @foreach ($tasks as $task)
            @php
                $statusColor = "bg-base-300";
                $icon = "fa-clock";
                $iconColor = "text-base-content/40";
                $cardBorder = "border-base-200";
                $badgeClass = "badge-ghost";
                $statusText = "รอดำเนินการ";

                if ($task->status == "approve") {
                    $statusColor = "bg-success";
                    $icon = "fa-check";
                    $iconColor = "text-success-content";
                    $cardBorder = "border-success/30";
                    $badgeClass = "badge-success";
                    $statusText = "อนุมัติแล้ว";
                } elseif ($task->status == "cancel" || $task->status == "reject") {
                    $statusColor = "bg-error";
                    $icon = "fa-times";
                    $iconColor = "text-error-content";
                    $cardBorder = "border-error/30";
                    $badgeClass = "badge-error";
                    $statusText = "ปฏิเสธ/ยกเลิก";
                }
            @endphp

            <div class="relative">
                <!-- Timeline Dot -->
                <div class="{{ $statusColor }} {{ $iconColor }} ring-base-100 absolute -left-[31px] top-1 z-10 flex h-8 w-8 items-center justify-center rounded-full shadow-sm ring-4 transition-all">
                    <i class="fas {{ $icon }} text-[12px]"></i>
                </div>

                <!-- Task Card -->
                <div class="card bg-base-100 {{ $cardBorder }} border shadow-sm transition-all hover:shadow-md">
                    <div class="card-body p-4">
                        <div class="flex flex-col justify-between gap-2 md:flex-row md:items-center">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold">{{ $task->task_name }}</span>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <span class="text-[11px] opacity-70">
                                        {{ $task->task_user }} {{ $task->user->name ?? null }}
                                    </span>
                                    <div class="divider divider-horizontal mx-0 h-3"></div>
                                    <span class="text-primary text-[11px] font-medium italic">
                                        {{ $task->task_position }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-col items-start md:items-end">
                                <div class="badge {{ $badgeClass }} badge-sm text-[10px] font-bold uppercase tracking-tighter">
                                    {{ $statusText }}
                                </div>
                                <span class="mt-1 font-mono text-[10px] opacity-50">
                                    {{ $task->date ? date("d/m/Y H:i", strtotime($task->date)) : "-" }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
