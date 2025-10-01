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
        <input class="drawer-toggle" id="my-drawer-2" type="checkbox" />
        <label class="btn btn-primary drawer-button text-end-safe m-3 lg:hidden" for="my-drawer-2">
            <span class="fa fa-bars"></span>
        </label>
        <div class="drawer-content bg-base-100 min-h-screen p-6">
            @yield("content")
        </div>
        <div class="drawer-side">
            <label class="drawer-overlay" for="my-drawer-2" aria-label="close sidebar"></label>
            <ul class="menu bg-base-200 text-base-content relative min-h-full gap-2 p-4 md:w-[13vw]">
                <a href="{{ route("document.index") }}">
                    <img class="m-3 mx-auto w-24" src="{{ asset("images/Vertical Logo.png") }}" alt="logo">
                </a>
                <li class="menu-title">Praram9 - DMS</li>
                <div class="text-info-content bg-neutral-content flex flex-col gap-0.5 rounded-md p-3 text-xs">
                    <div><span class="fas fa-user font-bold"></span> : {{ auth()->user()->userid }}</div>
                    <div>{{ auth()->user()->name }}</div>
                    <div>{{ auth()->user()->department }}</div>
                </div>
                <li><a class="nav-link" data-route="document.index" href="{{ route("document.index") }}">เอกสารทั้งหมด</a></li>
                <li><a class="nav-link" data-route="document.create" href="{{ route("document.create") }}">สร้างเอกสาร</a></li>
                <li class="absolute bottom-4 left-4 right-4"><a class="btn btn-error" onclick="logoutRequest()">Logout</a></li>
            </ul>
        </div>
    </div>
</body>
<script>
    function logoutRequest() {
        Swal.fire({
            title: "ต้องการออกจากระบบหรือไม่?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#66cc8a",
            cancelButtonColor: "gray",
            confirmButtonText: "ตกลง",
            cancelButtonText: "ยกเลิก"
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
<script type="module">
    $(function() {
        // Get all navigation links
        const navLinks = document.querySelectorAll('.nav-link');

        // Get active route from localStorage or set default
        const activeRoute = localStorage.getItem('activeRoute') || 'document.index';

        // Apply active class to the active route
        navLinks.forEach(link => {
            if (link.getAttribute('data-route') === activeRoute) {
                link.classList.add('menu-active');
            }

            // Add click event listener to each link
            link.addEventListener('click', function() {
                // Remove active class from all links
                navLinks.forEach(l => l.classList.remove('menu-active'));

                // Add active class to clicked link
                this.classList.add('menu-active');

                // Store active route in localStorage
                localStorage.setItem('activeRoute', this.getAttribute('data-route'));
            });
        });
    });
</script>

@stack("scripts")

</html>
