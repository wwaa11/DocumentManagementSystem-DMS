<!DOCTYPE html>
<html data-theme="praram" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon" href="{{ asset('images/icon.ico') }}" type="image/x-icon">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-cyan-50 via-base-100 to-emerald-50"></div>
        <div class="pointer-events-none absolute -left-24 top-10 h-72 w-72 rounded-full bg-primary/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-16 bottom-0 h-80 w-80 rounded-full bg-accent/20 blur-3xl"></div>

        <div class="relative w-full max-w-md">
            <div class="border-base-200/80 bg-base-100/95 rounded-3xl border p-7 shadow-xl backdrop-blur-sm sm:p-8">
                <div class="mb-6 flex flex-col items-center text-center">
                    <img class="mb-4 h-20 w-auto object-contain" src="{{ asset('images/Vertical Logo.png') }}" alt="Praram9 DMS Logo" />
                    <p class="badge badge-primary badge-soft mb-2">Hospital Document System</p>
                    <h1 class="text-primary text-2xl font-bold tracking-tight">Praram9 DMS</h1>
                    <p class="text-base-content/55 mt-1 text-sm">เข้าสู่ระบบเพื่อจัดการเอกสารและการดำเนินงาน</p>
                </div>

                <form class="space-y-4" autocomplete="on">
                    <div class="form-control">
                        <label class="label py-1" for="login-userid">
                            <span class="label-text font-semibold">User ID</span>
                        </label>
                        <label class="input input-bordered flex w-full items-center gap-2">
                            <i class="fas fa-user text-base-content/40 text-sm"></i>
                            <input
                                class="@error('userid') input-error @enderror grow"
                                id="login-userid"
                                type="text"
                                name="userid"
                                placeholder="รหัสพนักงาน"
                                value="{{ old('userid') }}"
                                required
                                autofocus
                            />
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label py-1" for="login-password">
                            <span class="label-text font-semibold">Password</span>
                        </label>
                        <label class="input input-bordered flex w-full items-center gap-2">
                            <i class="fas fa-lock text-base-content/40 text-sm"></i>
                            <input
                                class="@error('password') input-error @enderror grow"
                                id="login-password"
                                type="password"
                                name="password"
                                placeholder="รหัสผ่าน"
                                required
                            />
                        </label>
                    </div>

                    <button class="btn btn-primary mt-2 w-full gap-2" type="submit">
                        <i class="fas fa-sign-in-alt"></i>
                        เข้าสู่ระบบ
                    </button>
                </form>

                <div class="divider text-base-content/40 my-5 text-xs">หรือ</div>

                <a class="btn btn-outline btn-accent w-full gap-2" href="http://172.20.1.12/w_dms" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-external-link-alt"></i>
                    ระบบเว็บเดิม
                </a>
            </div>

            <p class="text-base-content/45 mt-5 text-center text-xs">
                © {{ date('Y') }} Praram 9 Hospital · Document Management System
            </p>
        </div>
    </div>

    <script type="module">
        $(function() {
            $("input[name='userid']").focus();
        });

        $("form").submit(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'กำลังเข้าสู่ระบบ...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            axios.post('{{ route('post.login') }}', {
                    userid: $("input[name='userid']").val(),
                    password: $("input[name='password']").val(),
                })
                .then(response => {
                    if (response.data.status === 'success') {
                        localStorage.setItem('activeRoute', 'document.index');
                        window.location.href = '{{ route('document.index') }}';
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เข้าสู่ระบบไม่สำเร็จ',
                            text: response.data.message,
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'เข้าสู่ระบบไม่สำเร็จ',
                        text: error.message,
                        timer: 1000,
                        timerProgressBar: true,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.reload()
                    });
                });
        });
    </script>
</body>

</html>
