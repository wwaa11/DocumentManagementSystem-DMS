@extends("layouts.app")
@section("content")
    <div class="bg-base-200 flex min-h-screen items-center justify-center">
        <div class="card bg-base-100 w-96 shadow-xl">
            <div class="card-body">
                <img class="w-48 justify-center self-center" src="{{ asset("images/Vertical Logo.png") }}" alt="DMS Logo" />
                <h2 class="card-title text-primary justify-center text-center text-2xl font-bold">DMS</h2>
                <form class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">User ID</span>
                        </label>
                        <input class="input input-bordered @error("userid") input-error @enderror w-full" type="text" name="userid" placeholder="Enter your user ID" value="{{ old("userid") }}" required autofocus />
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Password</span>
                        </label>
                        <input class="input input-bordered @error("password") input-error @enderror w-full" type="password" name="password" placeholder="Enter your password" required />
                    </div>
                    <div class="form-control mt-6">
                        <button class="btn btn-primary w-full" type="submit">
                            <span class="fa fa-sign-in"></span>
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push("scripts")
    <script type="module">
        $(document).ready(function() {
            $("input[name='userid']").focus();
        });

        $("form").submit(function(e) {
            e.preventDefault();

            axios.post('{{ route("post.login") }}', {
                    userid: $("input[name='userid']").val(),
                    password: $("input[name='password']").val(),
                })
                .then(response => {
                    if (response.data.status === 'success') {
                        window.location.href = '{{ route("index") }}';
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: response.data.message,
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: error.message,
                    });
                });
        });
    </script>
@endpush
