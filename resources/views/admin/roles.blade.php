@extends("layouts.app")
@section("content")
    <div class="mx-8">
        <h1 class="text-primary text-2xl font-bold">Roles</h1>
        <div class="divider"></div>
        <form class="flex items-center" action="{{ route("roles.list") }}" method="GET">
            <input class="input input-bordered input-sm" type="text" name="search" value="{{ $search ?? "" }}" placeholder="Search by user ID or name">
            <button class="btn btn-sm btn-primary" type="submit">Search</button>
        </form>
        {{ $users->links() }}
        <div class="mt-4">
            <table class="table-zebra table">
                <thead>
                    <tr class="">
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->userid }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->position }}</td>
                            <td>{{ $user->department }}</td>
                            <td>
                                <form class="flex items-center" action="{{ route("roles.update") }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="userid" value="{{ $user->userid }}">
                                    <select class="select select-bordered select-sm" name="role">
                                        <option value="user" selected>User</option>
                                        @foreach ($roles as $role => $label)
                                            <option value="{{ $role }}" {{ $user->role == $role ? "selected" : "" }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-primary" type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@push("scripts")
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.querySelector("input[type='text']");
            const tableRows = document.querySelectorAll("table tbody tr");

            searchInput.addEventListener("input", function() {
                const searchTerm = searchInput.value.toLowerCase();

                tableRows.forEach(function(row) {
                    const userID = row.querySelector("td:first-child").textContent.toLowerCase();
                    const userName = row.querySelector("td:nth-child(2)").textContent.toLowerCase();

                    if (userID.includes(searchTerm) || userName.includes(searchTerm)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        });
    </script>
@endpush
