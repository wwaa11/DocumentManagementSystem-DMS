<section class="mt-6 hidden" id="support-section">
    <h3 class="mb-4 flex items-center text-xl font-semibold">
        <i class="fas fa-headset text-primary mr-2"></i>แจ้งงาน/สนับสนุนการทำงาน
    </h3>

    <div class="mb-6">
        <h4 class="mb-3 font-medium">ประเภทของงานที่ต้องการแจ้ง</h4>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="hover:bg-primary/5 flex cursor-pointer items-center rounded-lg border p-4 transition-colors" onclick="selectSupportType('HARDWARE')">
                <input class="radio radio-primary mr-3" id="title_hardware" value="HARDWARE" name="title_type" type="radio" />
                <label class="flex-1 cursor-pointer" for="title_hardware">
                    <div class="font-medium">HARDWARE</div>
                    <div class="text-base-content/70 text-sm">อุปกรณ์คอมพิวเตอร์</div>
                </label>
            </div>

            <div class="hover:bg-primary/5 flex cursor-pointer items-center rounded-lg border p-4 transition-colors" onclick="selectSupportType('SOFTWARE')">
                <input class="radio radio-primary mr-3" id="title_software" value="SOFTWARE" name="title_type" type="radio" />
                <label class="flex-1 cursor-pointer" for="title_software">
                    <div class="font-medium">SOFTWARE</div>
                    <div class="text-base-content/70 text-sm">โปรแกรมคอมพิวเตอร์</div>
                </label>
            </div>

            <div class="hover:bg-primary/5 flex cursor-pointer items-center rounded-lg border p-4 transition-colors" onclick="selectSupportType('SSB')">
                <input class="radio radio-primary mr-3" id="title_ssb" value="SSB" name="title_type" type="radio" />
                <label class="flex-1 cursor-pointer" for="title_ssb">
                    <div class="font-medium">SSB</div>
                    <div class="text-base-content/70 text-sm">ระบบ SSB</div>
                </label>
            </div>

            <div class="hover:bg-primary/5 flex cursor-pointer items-center rounded-lg border p-4 transition-colors" onclick="selectSupportType('RESET_PASSWORD')">
                <input class="radio radio-primary mr-3" id="title_reset_password" value="RESET_PASSWORD" name="title_type" type="radio" />
                <label class="flex-1 cursor-pointer" for="title_reset_password">
                    <div class="font-medium">Reset Password</div>
                    <div class="text-base-content/70 text-sm">รีเซ็ตรหัสผ่าน</div>
                </label>
            </div>

            <div class="hover:bg-primary/5 flex cursor-pointer items-center rounded-lg border p-4 transition-colors" onclick="selectSupportType('OTHER')">
                <input class="radio radio-primary mr-3" id="title_other_radio" value="OTHER" name="title_type" type="radio" />
                <label class="flex-1 cursor-pointer" for="title_other_radio">
                    <div class="font-medium">อื่นๆ</div>
                    <input class="input input-bordered input-sm mt-1 w-full" id="request_other_text" type="text" placeholder="ระบุประเภทงาน" />
                </label>
            </div>
        </div>
    </div>

    {{-- if request_type == 'hardware' --}}
    <div id="hardware_request_fieldset">
        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-64 border p-4">
            <legend class="fieldset-legend">รายละเอียดการขอ</legend>
            <label class="label">
                <input class="checkbox" id="request_hardware_1" value="ขอเพิ่ม คอมพิวเตอร์ / Notebook / Printer / อุปกรณ์ต่อพ่วงต่างๆ" name="request_type_detail" type="radio" />
                ขอเพิ่ม คอมพิวเตอร์ / Notebook / Printer / อุปกรณ์ต่อพ่วงต่างๆ
            </label>
            <label class="label">
                <input class="checkbox" id="request_hardware_2" value="ขอย้ายจุดติดตั้งคอมพิวเตอร์ / Printer" name="request_type_detail" type="radio" />
                ขอย้ายจุดติดตั้งคอมพิวเตอร์ / Printer
            </label>
            <label class="label">
                <input class="checkbox" id="request_hardware_3" value="ติดตั้งระบบ Conference" name="request_type_detail" type="radio" />
                ติดตั้งระบบ Conference
            </label>
            <label class="label">
                <input class="checkbox" id="request_hardware_4" value="แจ้งซ่อม คอมพิวเตอร์ ชำรุด" name="request_type_detail" type="radio" />
                แจ้งซ่อม คอมพิวเตอร์ ชำรุด
            </label>
            <label class="label">
                <input class="checkbox" id="request_hardware_5" value="แจ้งซ่อม ปริ้นเตอร์ (Printer) ใช้งานไม่ได้" name="request_type_detail" type="radio" />
                แจ้งซ่อม ปริ้นเตอร์ (Printer) ใช้งานไม่ได้
            </label>
            <label class="label">
                <input class="checkbox" id="request_hardware_6" value="แจ้งซ่อม เมาส์-คีย์บอร์ด ชำรุด" name="request_type_detail" type="radio" />
                แจ้งซ่อม เมาส์-คีย์บอร์ด ชำรุด
            </label>
            <label class="label">
                <input class="checkbox" id="request_hardware_7" value="แจ้งซ่อม เครื่องสำรองไฟ (UPS) ชำรุด" name="request_type_detail" type="radio" />
                แจ้งซ่อม เครื่องสำรองไฟ (UPS) ชำรุด
            </label>
            <label class="label">
                <input class="checkbox" id="request_hardware_8" value="แจ้งซ่อม ประตูอัตโนมัติ Access Control (เฉพาะ อาคาร A)" name="request_type_detail" type="radio" />
                แจ้งซ่อม ประตูอัตโนมัติ Access Control (เฉพาะ อาคาร A)
            </label>
            <label class="label">
                <input class="checkbox" id="request_hardware_9" value="แจ้งปัญหาการใช้งานคอมพิวเตอร์ หรือ อุปกรณ์ต่อพวงอื่นๆ" name="request_type_detail" type="radio" />
                แจ้งปัญหาการใช้งานคอมพิวเตอร์ หรือ อุปกรณ์ต่อพวงอื่นๆ
            </label>
            <label class="label">
                <input class="checkbox" id="request_hardware_10" value="อื่นๆ" name="request_type_detail" type="radio" />
                อื่นๆ
                <input class="input input-bordered" id="request_other_hardware" type="text" />
            </label>
        </fieldset>
    </div>
    {{-- if request_type == 'software' --}}
    <div id="software_request_fieldset">
        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-64 border p-4">
            <legend class="fieldset-legend">รายละเอียดการขอ</legend>
            <label class="label">
                <input class="checkbox" id="request_software_1" value="แจ้งปัญหาการใช้งาน Microsoft Office,Office365 ไม่ได้" name="request_type_detail" type="radio" />
                แจ้งปัญหาการใช้งาน Microsoft Office,Office365 ไม่ได้
            </label>
            <label class="label">
                <input class="checkbox" id="request_software_2" value="แจ้งปัญหาการใช้งานโปรแกรมบนหน้า Desktop ไม่ได้" name="request_type_detail" type="radio" />
                แจ้งปัญหาการใช้งานโปรแกรมบนหน้า Desktop ไม่ได้
            </label>
            <label class="label">
                <input class="checkbox" id="request_software_3" value="แจ้งปัญหา SoftWare อื่นๆ" name="request_type_detail" type="radio" />
                แจ้งปัญหา SoftWare อื่นๆ
                <input class="input input-bordered" id="request_other_software" type="text" />
            </label>
        </fieldset>
    </div>
    {{-- if request_type == 'ssb' --}}
    <div id="ssb_request_fieldset">
        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-64 border p-4">
            <legend class="fieldset-legend">รายละเอียดการขอ</legend>
            <label class="label">
                <input class="checkbox" id="request_ssb_1" value="แจ้งปัญหาเกี่ยวกับโปรแกรม SSB" name="request_type_detail" type="radio" />
                แจ้งปัญหาเกี่ยวกับโปรแกรม SSB
            </label>
            <label class="label">
                <input class="checkbox" id="request_ssb_2" value="แจ้งปัญหา “พิมพ์เอกสารไม่ได้” หรือ “พิมพ์เอกสารไม่เป็นหน้า-หลัง”" name="request_type_detail" type="radio" />
                แจ้งปัญหา “พิมพ์เอกสารไม่ได้” หรือ “พิมพ์เอกสารไม่เป็นหน้า-หลัง”
            </label>
            <label class="label">
                <input class="checkbox" id="request_ssb_3" value="แจ้งขอ Setup/Config ต่างๆ ในระบบ SSB" name="request_type_detail" type="radio" />
                แจ้งขอ Setup/Config ต่างๆ ในระบบ SSB
            </label>
            <label class="label">
                <input class="checkbox" id="request_ssb_4" value="แจ้งขอ แก้ไข/ปรับปรุง รายงานใน SSB" name="request_type_detail" type="radio" />
                แจ้งขอ แก้ไข/ปรับปรุง รายงานใน SSB
            </label>
            <label class="label">
                <input class="checkbox" id="request_ssb_5" value="แจ้งขอ รายงานใหม่" name="request_type_detail" type="radio" />
                แจ้งขอ รายงานใหม่
            </label>
            <label class="label">
                <input class="checkbox" id="request_ssb_6" value="แจ้งปัญหา SSB อื่นๆ" name="request_type_detail" type="radio" />
                แจ้งปัญหา SSB อื่นๆ
                <input class="input input-bordered" id="request_other_ssb" type="text" />
            </label>
        </fieldset>
    </div>

    <textarea id="support_detail" name="detail" cols="30" rows="10"></textarea>
</section>
