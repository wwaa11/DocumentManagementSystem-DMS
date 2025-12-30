@extends("layouts.app")
@section("content")
    <div class="mx-8">
        <h1 class="text-primary text-2xl font-bold">รายชื่อผู้อนุมัติแต่ละแผนก</h1>
        <div class="divider"></div>
        <div class="form-control w-full">
            <label class="input input-bordered flex w-full items-center gap-2">
                <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </g>
                </svg>
                <input class="grow" id="deptSearch" type="search" list="dept-suggestions" placeholder="Search departments..." />
            </label>
            <datalist id="dept-suggestions">
                @foreach ($depts as $id => $item)
                    <option id="department_{{ $id }}" value="{{ $item }}">
                @endforeach
            </datalist>
            <div class="flex">
                <form class="flex flex-wrap items-end gap-2" action="{{ route("approvers.update") }}" method="POST">
                    @csrf
                    <div class="form-control">
                        <span class="label-text text-xs">Department</span>
                        <input class="input input-bordered input-sm" id="form_dept" type="text" name="department" readonly>
                    </div>
                    <div class="form-control">
                        <span class="label-text text-xs">User ID</span>
                        <input class="input input-bordered input-sm" id="form_userid" type="text" name="userid">
                    </div>
                    <div class="form-control">
                        <span class="label-text text-xs">Name</span>
                        <input class="input input-bordered input-sm" id="form_name" type="text" name="name">
                    </div>
                    <div class="form-control">
                        <span class="label-text text-xs">Position</span>
                        <input class="input input-bordered input-sm" id="form_position" type="text" name="position">
                    </div>
                    <div class="form-control">
                        <span class="label-text text-xs">Email</span>
                        <input class="input input-bordered input-sm" id="form_email" type="text" name="email">
                    </div>
                    <button class="btn btn-primary btn-sm">Update</button>
                </form>
            </div>
        </div>
        <div class="divider">รายชื่อผู้อนุมัติ</div>
        <table class="table-zebra table">
            <thead>
                <th>Department</th>
                <th>Userid</th>
                <th>Name</th>
                <th>Position</th>
                <th>Email</th>
                <th>last update</th>
            </thead>
            <tbody>
                @foreach ($datas as $index => $item)
                    <tr class="hover row-item">
                        <td id="{{ $item->id }}_department">{{ $item->department }}</td>
                        <td id="{{ $item->id }}_userid">{{ $item->userid }}</td>
                        <td id="{{ $item->id }}_name">{{ $item->name }}</td>
                        <td id="{{ $item->id }}_position">{{ $item->position }}</td>
                        <td id="{{ $item->id }}_email">{{ $item->email }}</td>
                        <td class="flex flex-col">
                            <small class="text-muted">{{ $item->last_update }}</small>
                            @if ($item->last_userid || $item->last_username)
                                <small class="text-primary">
                                    <i class="fas fa-user-edit me-1"></i>
                                    {{ $item->last_userid }} {{ $item->last_username }}
                                </small>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection
@push("scripts")
    <script>
        document.getElementById('deptSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const filter = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.row-item');
                let foundMatch = false;

                rows.forEach(row => {
                    // Column 0 is Department
                    const deptText = row.cells[0].textContent.toLowerCase().trim();
                    const isMatch = deptText.includes(filter);

                    // Toggle row visibility
                    row.style.display = isMatch ? '' : 'none';

                    // If this is an exact match (or the first partial match), fill the form
                    if (!foundMatch && isMatch && filter !== "") {
                        fillUpdateForm(row);
                        foundMatch = true;
                    }
                });
            }
        });

        function fillUpdateForm(row) {
            // We use the IDs you already set in your TD tags or index-based selection
            // Since you have IDs like "{{ $item->id }}_name", we can extract them
            document.getElementById('form_dept').value = row.cells[0].innerText.trim();
            document.getElementById('form_userid').value = row.cells[1].innerText.trim();
            document.getElementById('form_name').value = row.cells[2].innerText.trim();
            document.getElementById('form_position').value = row.cells[3].innerText.trim();
            document.getElementById('form_email').value = row.cells[4].innerText.trim();

            // Visual feedback: briefly highlight the form
            const formInputs = ['form_dept', 'form_userid', 'form_name', 'form_position', 'form_email'];
            formInputs.forEach(id => {
                document.getElementById(id).classList.add('input-primary');
                setTimeout(() => document.getElementById(id).classList.remove('input-primary'), 1000);
            });
        }
    </script>
@endpush
