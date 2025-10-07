<div class="flex flex-col gap-3">
    @if ($document->status == "wait_approval")
        <button class="btn btn-error w-full">ยกเลิกใบงาน</button>
    @elseif($document->status == "pending")
        <button class="btn btn-neutral w-full">ไม่สามาถยกเลิกใบงานได้</button>
    @endif
</div>
<div class="divider"></div>
