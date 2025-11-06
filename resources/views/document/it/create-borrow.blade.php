<section class="mt-6 hidden" id="borrow-section">
    <div class="mb-6">
        <h4 class="card-title text-primary mb-4 flex items-center text-xl">
            <i class="fas fa-list-alt mr-2"></i> ประเภทของงานที่ต้องการแจ้ง
        </h4>
        <div class="flex flex-col gap-3">
            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="borrow_notebook">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="borrow_notebook" value="Notebook" name="borrow_type" type="radio" onchange="selectBorrowType('Notebook')" />
                    <div>
                        <h4 class="font-medium">Notebook</h4>
                    </div>
                </div>
            </label>

            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="borrow_computer">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="borrow_computer" value="Computer" name="borrow_type" type="radio" onchange="selectBorrowType('Computer')" />
                    <div>
                        <h4 class="font-medium">Computer</h4>
                    </div>
                </div>
            </label>

            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="borrow_printer">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="borrow_printer" value="Printer" name="borrow_type" type="radio" onchange="selectBorrowType('Printer')" />
                    <div>
                        <h4 class="font-medium">Printer</h4>
                    </div>
                </div>
            </label>

            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="borrow_projector">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="borrow_projector" value="Projector" name="borrow_type" type="radio" onchange="selectBorrowType('Projector')" />
                    <div>
                        <h4 class="font-medium">Projector</h4>
                    </div>
                </div>
            </label>

            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="borrow_ipad_tablet">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="borrow_ipad_tablet" value="Ipad/Tablet" name="borrow_type" type="radio" onchange="selectBorrowType('Ipad/Tablet')" />
                    <div>
                        <h4 class="font-medium">Ipad/Tablet</h4>
                    </div>
                </div>
            </label>

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

    <h4 class="card-title text-accent mb-4 flex items-center text-xl">
        <i class="fas fa-calendar-alt mr-2"></i> วันที่คาดว่าจะคืนอุปกรณ์
    </h4>
    <input class="input input-bordered input-accent mt-1 w-full" id="return_date" name="return_date" type="date" />

    <label class="label mt-6">
        <span class="fa fa-info-circle"></span>
        <span class="label-text">รายละเอียดเพิ่มเติม</span>
    </label>
    <textarea class="textarea textarea-bordered h-24 w-full" id="borrow_detail" name="borrow_detail" placeholder="รายละเอียดเพิ่มเติม"></textarea>
</section>
@push("scripts")
    <script>
        function selectBorrowType(type) {
            // Hide all fieldsets first
            $('#borrow_other_text').prop('disabled', true);

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
