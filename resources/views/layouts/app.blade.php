<!DOCTYPE html>
<html data-theme="emerald" lang="{{ str_replace("_", "-", app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config("app.name", "Laravel") }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon" href="{{ asset("images/logo.ico") }}" type="image/x-icon">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/a20e89230f.js" crossorigin="anonymous"></script>
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>

<body class="">
    <div class="drawer lg:drawer-open">
        <input class="drawer-toggle" id="my-drawer" type="checkbox" />
        <div class="drawer-content">
            <div class="flex p-6">
                <label class="btn btn-primary drawer-button m-auto flex-1 lg:hidden" for="my-drawer">MENU</label>
            </div>
            <!-- Page content here -->
            @yield("content")
        </div>
        <div class="drawer-side">
            <label class="drawer-overlay" for="my-drawer" aria-label="close sidebar"></label>
            <ul class="menu bg-base-200 text-base-content min-h-full w-80 p-4">
                <a href="{{ route("document.index") }}">
                    <img class="m-3 mx-auto w-24" src="{{ asset("images/Vertical Logo.png") }}" alt="logo">
                </a>
                <li class="menu-title">Praram9 - DMS</li>
                <div class="text-info-content bg-neutral-content mb-1 flex flex-col gap-0.5 rounded-md p-3 text-xs">
                    <div><i class="fa-regular fa-user"></i> : {{ auth()->user()->userid }} <a class="text-error float-right cursor-pointer" onclick="logoutRequest()">ออกจากระบบ</a></div>
                    <div><i class="fa-solid fa-minus"></i> : {{ auth()->user()->name }}</div>
                    <div><i class="fa-solid fa-minus"></i> : {{ auth()->user()->department }}</div>
                </div>
                <li class="mb-1"><a class="nav-link" data-route="document.index" href="{{ route("document.index") }}">เอกสารทั้งหมด</a></li>
                <li class="mb-1"><a class="nav-link" data-route="document.create" href="{{ route("document.create") }}">สร้างเอกสาร</a></li>
                @if (auth()->user()->role !== "user" && auth()->user()->menu)
                    <ul>
                        <li>
                            <ul>
                                @foreach (auth()->user()->menu["lists"] as $key => $link)
                                    @if ($link["link"] == null)
                                        <li>
                                            <div class="divider">{{ $link["title"] }}</div>
                                        </li>
                                    @else
                                        <li class="mb-1">
                                            <a class="nav-link" data-route="{{ $link["type"] }}.{{ $link["link"] }}" href="{{ route($link["link"], ["type" => $link["type"]]) }}">
                                                {{ $link["title"] }}
                                                @if ($link["count"])
                                                    <span class="badge badge-sm badge-primary float-right" id="{{ $link["type"] }}.{{ $link["id"] }}">-</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                @endif
            </ul>
        </div>
    </div>
</body>

@if (session("success"))
    <script type="module">
        Swal.fire({
            icon: "success",
            title: "Success",
            text: "{{ session("success") }}",
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    </script>
@endif
@if (session("error"))
    <script type="module">
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "{{ session("error") }}",
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    </script>
@endif
<script type="module">
    $(function() {
        const navLinks = document.querySelectorAll('.nav-link');
        const activeRoute = localStorage.getItem('activeRoute') || 'document.index';
        navLinks.forEach(link => {
            if (link.getAttribute('data-route') === activeRoute) {
                link.classList.add('menu-active');
            }

            link.addEventListener('click', function() {
                navLinks.forEach(l => l.classList.remove('menu-active'));
                this.classList.add('menu-active');
                localStorage.setItem('activeRoute', this.getAttribute('data-route'));
            });
        });

        @if (auth()->user()->role !== "user" && auth()->user()->menu)
            @foreach (auth()->user()->menu["count"] as $index => $link)
                getCount{{ $index }}();

                function getCount{{ $index }}() {
                    axios.get(`{{ route($link["route"], ["type" => $link["type"]]) }}`)
                        .then(function(response) {
                            Object.keys(response.data).forEach(key => {
                                updateCount(key, response.data[key]);
                            });
                            setTimeout(() => {
                                getCount{{ $index }}();
                            }, 60 * 1000);
                        });
                }
            @endforeach
        @endif
    });
</script>
<script>
    function updateCount(id, number) {
        document.getElementById(id).textContent = number;
    }

    function logoutRequest() {
        Swal.fire({
            title: "ต้องการออกจากระบบหรือไม่?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "ตกลง",
            cancelButtonText: "ยกเลิก",
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-error mx-3', // DaisyUI Primary Color
                cancelButton: 'btn btn-ghost mx-3' // DaisyUI Ghost/subtle style
            },
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.setItem('activeRoute', 'logout');
                axios.post("{{ route("logout") }}")
                    .then(function(response) {
                        if (response.data.status === "success") {
                            window.location.href = "{{ route("login") }}";
                        }
                    });
            }
        });
    }
</script>
@stack("scripts")
</body>

</html>
