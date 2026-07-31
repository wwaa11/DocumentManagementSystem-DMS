<section class="mt-6 hidden" id="borrow-section">
    <div class="mb-6">
        <x-ui.section-title icon="fas fa-list-alt" tag="h4">ประเภทของงานที่ต้องการแจ้ง</x-ui.section-title>
        <div class="flex flex-col gap-3">
            @foreach ([
                'Notebook' => 'Notebook',
                'Computer' => 'Computer',
                'Printer' => 'Printer',
                'Projector' => 'Projector',
                'Ipad/Tablet' => 'Ipad/Tablet',
            ] as $value => $label)
                <x-ui.radio-option
                    :id="'borrow_'.Str::slug($value, '_')"
                    name="borrow_type"
                    :value="$value"
                    :onchange="'selectBorrowType(\''.$value.'\')'"
                >
                    {{ $label }}
                </x-ui.radio-option>
            @endforeach

            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="borrow_other">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="borrow_other" value="OTHER" name="borrow_type" type="radio" onchange="selectBorrowType('OTHER')" />
                    <div>
                        <h4 class="font-medium">อื่นๆ</h4>
                        <input class="input input-bordered input-sm mt-1 w-full" id="borrow_other_text" disabled name="borrow_other_text" type="text" placeholder="โปรดระบุประเภทอุปกรณ์" />
                    </div>
                </div>
            </label>
        </div>
    </div>

    <x-ui.section-title icon="fas fa-calendar-alt" tag="h4" class="text-accent">
        วันที่ขอยืมอุปกรณ์
    </x-ui.section-title>
    <input class="input input-bordered input-accent mt-1 w-full" id="borrow_date" name="borrow_date" type="date" />

    <x-ui.section-title icon="fas fa-calendar-alt" tag="h4" class="text-accent mt-6">
        วันที่คาดว่าจะคืนอุปกรณ์
    </x-ui.section-title>
    <input class="input input-bordered input-accent mt-1 w-full" id="return_date" name="return_date" type="date" />

    <label class="label mt-6">
        <span class="fa fa-info-circle"></span>
        <span class="label-text">รายละเอียดเพิ่มเติม</span>
    </label>
    <textarea class="textarea textarea-bordered h-24 w-full" id="borrow_detail" name="borrow_detail" placeholder="รายละเอียดเพิ่มเติม"></textarea>
</section>
@push('scripts')
    <script>
        function selectBorrowType(type) {
            $('#borrow_other_text').prop('disabled', true);
            $('#send_to_it_admin').addClass('hidden');

            if (type == 'Projector' || type == 'Ipad/Tablet') {
                $('input[name="isHardware"]').val(false);
            } else {
                $('input[name="isHardware"]').val(true);
            }

            if (type === 'OTHER') {
                $('#other_request_fieldset').removeClass('hidden');
                $('#borrow_other_text').prop('disabled', false);
            } else {
                $('#borrow_other_text').prop('disabled', true);
            }

            $('#document-addtional-info').removeClass('hidden');
        }
    </script>
@endpush
