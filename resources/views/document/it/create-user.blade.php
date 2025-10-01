<section class="mt-6 hidden" id="user-section">
    <h3 class="mb-4 flex items-center text-xl font-semibold">
        <i class="fas fa-user-cog text-primary mr-2"></i>ขอรหัสผู้ใช้งาน/สิทธิ์
    </h3>

    <div class="mb-6">
        <h4 class="mb-3 font-medium">ประเภทการขอ</h4>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
            <div class="hover:bg-primary/5 flex cursor-pointer items-center rounded-lg border p-3 transition-colors" onclick="selectRequestType('add')">
                <input class="radio radio-primary mr-2" id="user-title-add" value="ขอเพิ่ม" type="radio" name="title" />
                <label class="cursor-pointer" for="user-title-add">ขอเพิ่ม</label>
            </div>
            <div class="hover:bg-primary/5 flex cursor-pointer items-center rounded-lg border p-3 transition-colors" onclick="selectRequestType('remove')">
                <input class="radio radio-primary mr-2" id="user-title-remove" value="ขอลด" type="radio" name="title" />
                <label class="cursor-pointer" for="user-title-remove">ขอลด</label>
            </div>
            <div class="hover:bg-primary/5 flex cursor-pointer items-center rounded-lg border p-3 transition-colors" onclick="selectRequestType('edit')">
                <input class="radio radio-primary mr-2" id="user-title-edit" value="ขอแก้ไข" type="radio" name="title" />
                <label class="cursor-pointer" for="user-title-edit">ขอแก้ไข</label>
            </div>
            <div class="hover:bg-primary/5 flex cursor-pointer items-center rounded-lg border p-3 transition-colors" onclick="selectRequestType('doctor')">
                <input class="radio radio-primary mr-2" id="user-title-doctor" value="เลขาแพทย์" type="radio" name="title" />
                <label class="cursor-pointer" for="user-title-doctor">เลขาแพทย์</label>
            </div>
            <div class="hover:bg-primary/5 flex cursor-pointer items-center rounded-lg border p-3 transition-colors" onclick="selectRequestType('hr')">
                <input class="radio radio-primary mr-2" id="user-title-hr" value="ฝ่ายบุคคล" type="radio" name="title" />
                <label class="cursor-pointer" for="user-title-hr">ฝ่ายบุคคล</label>
            </div>
        </div>
    </div>

    <!-- Standard Request Form -->
    <div class="hidden" id="user_search">
        <div class="bg-base-200 mb-4 rounded-lg p-6">
            <h4 class="mb-4 flex items-center font-medium">
                <i class="fas fa-search text-primary mr-2"></i>ค้นหาข้อมูลพนักงาน
            </h4>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">รหัสพนักงาน</span>
                    </label>
                    <div class="input-group">
                        <input class="input input-bordered w-full" id="request_userid" placeholder="รหัสพนักงาน" type="text" />
                        <button class="btn btn-square btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">ชื่อ-นามสกุล</span>
                    </label>
                    <input class="input input-bordered bg-base-100/50" id="request_name" placeholder="ชื่อ-นามสกุล" type="text" readonly />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">แผนก</span>
                    </label>
                    <input class="input input-bordered bg-base-100/50" id="request_department" placeholder="แผนก" type="text" readonly />
                </div>
            </div>

            <h4 class="mb-4 font-medium">สิทธิ์การใช้งานที่ต้องการ</h4>
            <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3">
                <label class="hover:bg-base-100 flex cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors">
                    <input class="checkbox checkbox-primary" id="request_ssb" type="checkbox" />
                    <span>SSB</span>
                </label>
                <label class="hover:bg-base-100 flex cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors">
                    <input class="checkbox checkbox-primary" id="request_windows" type="checkbox" />
                    <span>Windows</span>
                </label>
                <label class="hover:bg-base-100 flex cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors">
                    <input class="checkbox checkbox-primary" id="request_email" type="checkbox" />
                    <span>Email</span>
                </label>
                <label class="hover:bg-base-100 flex cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors">
                    <input class="checkbox checkbox-primary" id="request_hclab" type="checkbox" />
                    <span>HCLAB</span>
                </label>
                <label class="hover:bg-base-100 flex cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors">
                    <input class="checkbox checkbox-primary" id="request_pacs" type="checkbox" />
                    <span>PACS</span>
                </label>
                <label class="hover:bg-base-100 flex cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors">
                    <input class="checkbox checkbox-primary" id="request_other_check" type="checkbox" />
                    <span>อื่นๆ</span>
                    <input class="input input-bordered input-sm" id="request_other" type="text" />
                </label>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">รายละเอียดเพิ่มเติม</span>
                </label>
                <textarea class="textarea textarea-bordered h-24" id="request_detail" placeholder="รายละเอียดเพิ่มเติม"></textarea>
            </div>
        </div>

        <div class="flex justify-end">
            <button class="btn btn-primary btn-sm gap-2">
                <i class="fas fa-plus"></i> เพิ่มรายการ
            </button>
        </div>
    </div>

    <!-- Doctor/HR Request Form -->
    <div class="hidden" id="doctor_hr">
        <div class="form-control">
            <label class="label">
                <span class="label-text">รายละเอียด</span>
            </label>
            <textarea class="textarea textarea-bordered h-40" name="request_detail" placeholder="รายละเอียด"></textarea>
        </div>
    </div>
</section>

@push("scripts")
    <script></script>
@endpush
