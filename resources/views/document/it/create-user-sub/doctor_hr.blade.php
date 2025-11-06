<!-- Doctor/HR Request Form -->
<div class="border-accent hidden rounded border-2 border-dashed p-6" id="doctor_hr">
    <div class="grid grid-cols-3 gap-3">
        <div class="card shadow-xl" onclick="toggleCheckID('#doctor_hr_it')">
            <div class="card-body">
                <h2 class="card-title text-accent">SSB, Windows, Email</h2>
                <div class="form-control">
                    <label class="label cursor-pointer">
                        <span class="label-text">มีการขอ IT Request</span>
                        <input class="checkbox checkbox-accent" id="doctor_hr_it" onclick="toggleCheckID('#doctor_hr_it')" type="checkbox" value="false" />
                    </label>
                </div>
            </div>
        </div>

        <div class="card shadow-xl" onclick="toggleCheckID('#doctor_hr_hclab')">
            <div class="card-body">
                <h2 class="card-title text-accent">HCLAB</h2>
                <div class="form-control">
                    <label class="label cursor-pointer">
                        <span class="label-text">มีการขอ HC Request</span>
                        <input class="checkbox checkbox-accent" id="doctor_hr_hclab" onclick="toggleCheckID('#doctor_hr_hclab')" type="checkbox" value="false" />
                    </label>
                </div>
            </div>
        </div>

        <div class="card shadow-xl" onclick="toggleCheckID('#doctor_hr_pacs')">
            <div class="card-body">
                <h2 class="card-title text-accent">PACS</h2>
                <div class="form-control">
                    <label class="label cursor-pointer">
                        <span class="label-text">มีการขอ PAC Request</span>
                        <input class="checkbox checkbox-accent" id="doctor_hr_pacs" onclick="toggleCheckID('#doctor_hr_pacs')" type="checkbox" value="false" />
                    </label>
                </div>
            </div>
        </div>

        <div class="card shadow-xl" onclick="toggleCheckID('#doctor_hr_heartstream')">
            <div class="card-body">
                <h2 class="card-title text-accent">HeartStream</h2>
                <div class="form-control">
                    <label class="label cursor-pointer">
                        <span class="label-text">มีการขอ HeartStream Request</span>
                        <input class="checkbox checkbox-accent" id="doctor_hr_heartstream" onclick="toggleCheckID('#doctor_hr_heartstream')" type="checkbox" value="false" />
                    </label>
                </div>
            </div>
        </div>

        <div class="card shadow-xl" onclick="toggleCheckID('#doctor_hr_register')">
            <div class="card-body">
                <h2 class="card-title text-accent">Register</h2>
                <div class="form-control">
                    <label class="label cursor-pointer">
                        <span class="label-text">มีการขอ Register Request</span>
                        <input class="checkbox checkbox-accent" id="doctor_hr_register" onclick="toggleCheckID('#doctor_hr_register')" type="checkbox" value="false" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-control mt-6">
        <label class="label mb-3">
            <span class="fas fa-info-circle"></span>
            <span class="label-text">รายละเอียด</span>
        </label>
        <textarea class="textarea textarea-bordered focus:outline-primary w-full focus:border-0" id="user_detail" rows="20" name="user_detail" placeholder="รายละเอียด"></textarea>
    </div>

</div>

@push("scripts")
    <script>
        function toggleCheckID(id) {
            $(id).prop("checked", !$(id).prop("checked"));
            updateCreateFlags();
        }
    </script>
@endpush
