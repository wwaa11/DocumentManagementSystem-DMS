@extends("layouts.app")

@section("content")
    <div class="mx-8 pb-10">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-primary text-3xl font-bold">User Roles Management</h1>
                <p class="text-base-content/60 text-sm">จัดการสิทธิ์การใช้งานระบบแยกตามหน้าที่</p>
            </div>
            <div class="bg-base-100 border-base-200 rounded-lg border p-3 shadow-sm">
                <form class="flex items-center gap-2" action="{{ route("roles.list") }}" method="GET">
                    <div class="join">
                        <input class="input input-bordered input-sm join-item w-64 md:w-80" type="text" name="search" value="{{ $search ?? "" }}" placeholder="ค้นหาโดย User ID หรือ ชื่อ...">
                        <button class="btn btn-primary btn-sm join-item" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    @if ($search)
                        <a class="btn btn-ghost btn-sm btn-circle" href="{{ route("roles.list") }}">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="divider my-6"></div>

        <div class="card bg-base-100 border-base-200 border shadow-xl">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table-zebra table w-full border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-base-200/50">
                                <th class="py-4 pl-6 text-xs font-bold uppercase tracking-wider">User Information</th>
                                <th class="py-4 text-xs font-bold uppercase tracking-wider">Position / Department</th>
                                <th class="py-4 pr-6 text-center text-xs font-bold uppercase tracking-wider">System Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="hover:bg-base-200/30 transition-colors">
                                    <td class="border-base-200/50 border-b py-4 pl-6">
                                        <div class="flex items-center gap-3">

                                            <div>
                                                <div class="font-bold">{{ $user->name }}</div>
                                                <div class="text-xs opacity-50">{{ $user->userid }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="border-base-200/50 border-b py-4">
                                        <div class="text-sm font-medium">{{ $user->position }}</div>
                                        <div class="text-xs opacity-60">{{ $user->department }}</div>
                                    </td>
                                    <td class="border-base-200/50 border-b py-4 pr-6">
                                        <form class="role-update-form flex items-center justify-center gap-2" action="{{ route("roles.update") }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="userid" value="{{ $user->userid }}">
                                            <input type="hidden" name="username" value="{{ $user->name }}">
                                            <div class="join">
                                                <select class="select select-bordered select-sm join-item focus:outline-none" name="role">
                                                    <option value="user" {{ $user->role == "user" ? "selected" : "" }}>User (General)</option>
                                                    @foreach ($roles as $role => $label)
                                                        <option value="{{ $role }}" {{ $user->role == $role ? "selected" : "" }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn-sm btn-primary join-item update-btn" type="button">
                                                    <i class="fas fa-save mr-1"></i> Update
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if ($users->isEmpty())
                                <tr>
                                    <td class="py-10 text-center italic opacity-50" colspan="3">
                                        <i class="fas fa-user-slash mb-2 block text-4xl"></i>
                                        ไม่พบข้อมูลผู้ใช้
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($users->hasPages())
                <div class="card-footer border-base-200 bg-base-100 rounded-b-xl border-t p-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push("scripts")
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const updateButtons = document.querySelectorAll('.update-btn');

            updateButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const form = this.closest('form');
                    const username = form.querySelector('input[name="username"]').value;
                    const roleSelect = form.querySelector('select[name="role"]');
                    const roleName = roleSelect.options[roleSelect.selectedIndex].text;

                    Swal.fire({
                        title: 'ยืนยันการเปลี่ยนสิทธิ์?',
                        html: `คุณกำลังจะเปลี่ยนสิทธิ์ของ <b>${username}</b> <br>เป็น <b>${roleName}</b>`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'ยืนยันการเปลี่ยนแปลง',
                        cancelButtonText: 'ยกเลิก',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-primary px-6 mx-2',
                            cancelButton: 'btn btn-ghost px-6 mx-2'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
