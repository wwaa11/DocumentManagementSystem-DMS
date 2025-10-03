<section class="mt-6 hidden" id="support-section">
    <div class="mb-6">
        <h4 class="card-title text-primary mb-4 flex items-center text-xl">
            <i class="fas fa-list-alt mr-2"></i> ประเภทของงานที่ต้องการแจ้ง
        </h4>
        <div class="flex flex-col gap-3">
            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="title_hardware">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="title_hardware" value="HARDWARE" name="title" type="radio" onchange="selectSupportType('HARDWARE')" />
                    <div>
                        <h4 class="font-medium">HARDWARE</h4>
                        <div class="text-base-content/70 text-sm">อุปกรณ์คอมพิวเตอร์</div>
                    </div>
                </div>
            </label>

            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="title_software">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="title_software" value="SOFTWARE" name="title" type="radio" onchange="selectSupportType('SOFTWARE')" />
                    <div>
                        <h4 class="font-medium">SOFTWARE</h4>
                        <div class="text-base-content/70 text-sm">โปรแกรมคอมพิวเตอร์</div>
                    </div>
                </div>
            </label>

            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="title_ssb">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="title_ssb" value="SSB" name="title" type="radio" onchange="selectSupportType('SSB')" />
                    <div>
                        <h4 class="font-medium">SSB</h4>
                        <div class="text-base-content/70 text-sm">ระบบ SSB</div>
                    </div>
                </div>
            </label>

            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="title_reset_password">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="title_reset_password" value="RESET_PASSWORD" name="title" type="radio" onchange="selectSupportType('RESET_PASSWORD')" />
                    <div>
                        <h4 class="font-medium">Reset Password</h4>
                        <div class="text-base-content/70 text-sm">รีเซ็ตรหัสผ่าน</div>
                    </div>
                </div>
            </label>

            <label class="hover:bg-primary/5 cursor-pointer rounded-lg p-4 transition-all hover:shadow-md" for="title_other">
                <div class="flex items-center">
                    <input class="radio radio-primary mr-3" id="title_other" value="OTHER" name="title" type="radio" onchange="selectSupportType('OTHER')" />
                    <div>
                        <h4 class="font-medium">อื่นๆ</h4>
                        <input class="input input-bordered input-sm mt-1 w-full" id="title_other_text" disabled name="title_other_text" type="text" placeholder="ระบุประเภทงาน" />
                    </div>
                </div>
            </label>
        </div>
    </div>

    {{-- if request_type == 'hardware' --}}
    <div class="hidden" id="hardware_request_fieldset">
        <h4 class="card-title text-primary mb-4 flex items-center text-xl">
            <i class="fas fa-wrench text-primary mr-2"></i> รายละเอียดการขอ (Hardware)
        </h4>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_hardware_1">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_hardware_1" onclick="setDataApprove(false, true, 'ITJ')" value="ขอเพิ่ม คอมพิวเตอร์ / Notebook / Printer / อุปกรณ์ต่อพ่วงต่างๆ" name="request_type_detail" type="radio" />
                        <span class="label-text">ขอเพิ่ม คอมพิวเตอร์ / Notebook / Printer / อุปกรณ์ต่อพ่วงต่างๆ</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_hardware_2">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_hardware_2" onclick="setDataApprove(false, true, 'ITJ')" value="ขอย้ายจุดติดตั้งคอมพิวเตอร์ / Printer" name="request_type_detail" type="radio" />
                        <span class="label-text">ขอย้ายจุดติดตั้งคอมพิวเตอร์ / Printer</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_hardware_3">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_hardware_3" onclick="setDataApprove(true, false, 'ITS')" value="ติดตั้งระบบ Conference" name="request_type_detail" type="radio" />
                        <span class="label-text">ติดตั้งระบบ Conference</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_hardware_4">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_hardware_4" onclick="setDataApprove(true, false, 'ITS')" value="แจ้งซ่อม คอมพิวเตอร์ ชำรุด" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งซ่อม คอมพิวเตอร์ ชำรุด</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_hardware_5">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_hardware_5" onclick="setDataApprove(true, false, 'ITS')" value="แจ้งซ่อม ปริ้นเตอร์ (Printer) ใช้งานไม่ได้" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งซ่อม ปริ้นเตอร์ (Printer) ใช้งานไม่ได้</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_hardware_6">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_hardware_6" onclick="setDataApprove(true, false)" value="แจ้งซ่อม เมาส์-คีย์บอร์ด ชำรุด" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งซ่อม เมาส์-คีย์บอร์ด ชำรุด</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_hardware_7">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_hardware_7" onclick="setDataApprove(true, false, 'ITS')" value="แจ้งซ่อม เครื่องสำรองไฟ (UPS) ชำรุด" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งซ่อม เครื่องสำรองไฟ (UPS) ชำรุด</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_hardware_8">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_hardware_8" onclick="setDataApprove(true, false, 'ITS')" value="แจ้งซ่อม ประตูอัตโนมัติ Access Control (เฉพาะ อาคาร A)" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งซ่อม ประตูอัตโนมัติ Access Control (เฉพาะ อาคาร A)</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_hardware_9">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_hardware_9" onclick="setDataApprove(true, false, 'ITS')" value="แจ้งปัญหาการใช้งานคอมพิวเตอร์ หรือ อุปกรณ์ต่อพวงอื่นๆ" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งปัญหาการใช้งานคอมพิวเตอร์ หรือ อุปกรณ์ต่อพวงอื่นๆ</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_hardware_10">
                <div class="card-body p-4">
                    <div class="flex w-full items-center">
                        <input class="radio radio-primary mr-3" id="request_hardware_10" onclick="setDataApprove(true, false, 'ITS')" value="อื่นๆ" name="request_type_detail" type="radio" />
                        <span class="label-text mr-2">อื่นๆ:</span>
                        <input class="input input-bordered input-sm flex-grow" id="request_other_hardware" placeholder="ระบุรายละเอียด" name="request_type_detail_other" type="text" />
                    </div>
                </div>
            </label>
        </div>
    </div>
    {{-- if request_type == 'software' --}}
    <div class="hidden" id="software_request_fieldset">
        <h4 class="card-title text-primary mb-4 flex items-center text-xl">
            <i class="fas fa-laptop-code text-primary mr-2"></i> รายละเอียดการขอ (Software)
        </h4>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_software_1">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_software_1" onclick="setDataApprove(true, false, 'ITS')" value="แจ้งปัญหาการใช้งาน Microsoft Office, Office365 ไม่ได้" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งปัญหาการใช้งาน Microsoft Office, Office365 ไม่ได้</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_software_2">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_software_2" onclick="setDataApprove(true, false, 'ITS')" value="แจ้งปัญหาการใช้งานโปรแกรมบนหน้า Desktop ไม่ได้" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งปัญหาการใช้งานโปรแกรมบนหน้า Desktop ไม่ได้</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_software_3">
                <div class="card-body p-4">
                    <div class="flex w-full items-center">
                        <input class="radio radio-primary mr-3" id="request_software_3" onclick="setDataApprove(true, false, 'ITS')" value="อื่นๆ" name="request_type_detail" type="radio" />
                        <span class="label-text mr-2">อื่นๆ:</span>
                        <input class="input input-bordered input-sm flex-grow" id="request_other_software" name="request_type_detail_other" type="text" />
                    </div>
                </div>
            </label>
        </div>
    </div>
    {{-- if request_type == 'ssb' --}}
    <div class="hidden" id="ssb_request_fieldset">
        <h4 class="card-title text-primary mb-4 flex items-center text-xl">
            <i class="fas fa-database text-primary mr-2"></i> รายละเอียดการขอ (SSB)
        </h4>
        <div class="flex flex-col gap-3">
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_ssb_1">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_ssb_1" onclick="setDataApprove(true, false,'ITS')" value="แจ้งปัญหาเกี่ยวกับโปรแกรม SSB" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งปัญหาเกี่ยวกับโปรแกรม SSB</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_ssb_2">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_ssb_2" onclick="setDataApprove(true, false, 'ITS')" value="แจ้งปัญหา “พิมพ์เอกสารไม่ได้” หรือ “พิมพ์เอกสารไม่เป็นหน้า-หลัง”" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งปัญหา “พิมพ์เอกสารไม่ได้” หรือ “พิมพ์เอกสารไม่เป็นหน้า-หลัง”</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_ssb_3">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_ssb_3" onclick="setDataApprove(false, false, 'ITJ')" value="แจ้งขอ Setup/Config ต่างๆ ในระบบ SSB" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งขอ Setup/Config ต่างๆ ในระบบ SSB</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_ssb_4">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_ssb_4" onclick="setDataApprove(false, false, 'ITR')" value="แจ้งขอ แก้ไข/ปรับปรุง รายงานใน SSB" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งขอ แก้ไข/ปรับปรุง รายงานใน SSB</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_ssb_5">
                <div class="card-body p-4">
                    <div class="flex items-center">
                        <input class="radio radio-primary mr-3" id="request_ssb_5" onclick="setDataApprove(false, false, 'ITR')" value="แจ้งขอ รายงานใหม่" name="request_type_detail" type="radio" />
                        <span class="label-text">แจ้งขอ รายงานใหม่</span>
                    </div>
                </div>
            </label>
            <label class="hover:bg-primary/5 rounded-box cursor-pointer p-4 transition-all hover:shadow-md" for="request_ssb_6">
                <div class="card-body p-4">
                    <div class="flex w-full items-center">
                        <input class="radio radio-primary mr-3" id="request_ssb_6" onclick="setDataApprove(true, false, 'ITS')" value="อื่นๆ" name="request_type_detail" type="radio" />
                        <span class="label-text mr-2">อื่นๆ:</span>
                        <input class="input input-bordered input-sm flex-grow" id="request_other_ssb" name="request_type_detail_other" type="text" />
                    </div>
                </div>
            </label>
        </div>
    </div>

    <label class="label mt-6">
        <span class="fa fa-info-circle"></span>
        <span class="label-text">รายละเอียดเพิ่มเติม</span>
    </label>
    <textarea class="textarea textarea-bordered h-24 w-full" id="support_detail" name="support_detail" placeholder="รายละเอียดเพิ่มเติม"></textarea>
</section>
@push("scripts")
    <script>
        function selectSupportType(type) {
            // Hide all fieldsets first
            $('#hardware_request_fieldset').addClass('hidden');
            $('#software_request_fieldset').addClass('hidden');
            $('#ssb_request_fieldset').addClass('hidden');
            $('#reset_password_request_fieldset').addClass('hidden');
            $('#other_request_fieldset').addClass('hidden');
            $('#title_other_text').prop('disabled', true);

            // Show the selected fieldset
            if (type === 'HARDWARE') {
                $('#hardware_request_fieldset').removeClass('hidden');
            } else if (type === 'SOFTWARE') {
                $('#software_request_fieldset').removeClass('hidden');
            } else if (type === 'SSB') {
                $('#ssb_request_fieldset').removeClass('hidden');
            } else if (type === 'RESET_PASSWORD') {
                $('#reset_password_request_fieldset').removeClass('hidden');
                $('#documentCode').val('ITS');
            } else if (type === 'OTHER') {
                $('#other_request_fieldset').removeClass('hidden');
                $('#title_other_text').prop('disabled', false);
                $('#documentCode').val('ITS');
            }

            $('#document-addtional-info').removeClass('hidden');
        }
    </script>
@endpush
