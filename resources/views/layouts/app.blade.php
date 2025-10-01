<!DOCTYPE html>
<html data-theme="emerald" lang="{{ str_replace("_", "-", app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config("app.name", "Laravel") }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/a20e89230f.js" crossorigin="anonymous"></script>
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>

<body class="">
    @auth
        <div class="drawer lg:drawer-open">
            <input class="drawer-toggle" id="my-drawer-2" type="checkbox" />
            <div class="drawer-content flex flex-col justify-center md:items-center">
                <label class="btn btn-primary drawer-button text-end-safe m-3 lg:hidden" for="my-drawer-2">
                    <span class="fa fa-bars"></span>
                </label>
                @yield("content")
            </div>
            <div class="drawer-side">
                <label class="drawer-overlay" for="my-drawer-2" aria-label="close sidebar"></label>
                <ul class="menu bg-base-200 text-base-content min-h-full w-[13vw] p-4">
                    <img class="m-3 mx-auto w-24" src="{{ asset("images/Vertical Logo.png") }}" alt="logo">
                    <li class="text-primary mx-auto mb-3 text-center text-lg font-bold">DMS</li>
                    <div class="text-info-content bg-neutral-content rounded-md p-3 text-xs">
                        <div><span class="fas fa-user font-bold"></span>{{ auth()->user()->userid }}</div>
                        <div>{{ auth()->user()->name }}</div>
                        <div>{{ auth()->user()->department }}</div>
                    </div>
                    <li><a>Sidebar Item 1</a></li>
                    <li><a>Sidebar Item 2</a></li>
                    <li><a class="btn btn-error" onclick="logoutRequest()">Logout</a></li>
                </ul>
            </div>
        </div>
    @endauth
    @yield("content")
</body>
<script>
    function logoutRequest() {
        axios.post("{{ route("logout") }}")
            .then(function(response) {
                if (response.data.status === "success") {
                    window.location.href = "{{ route("login") }}";
                }
            });
    }
</script>
@stack("scripts")

</html>
