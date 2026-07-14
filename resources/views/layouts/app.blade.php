<!DOCTYPE html>
<html data-theme="emerald" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon" href="{{ asset('images/icon.ico') }}" type="image/x-icon">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/a20e89230f.js" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="">
    <div class="drawer lg:drawer-open">
        <input class="drawer-toggle" id="my-drawer" type="checkbox" />
        <div class="drawer-content">
            <div class="flex p-6">
                <label class="btn btn-primary drawer-button m-auto flex-1 lg:hidden" for="my-drawer">MENU</label>
            </div>
            @yield('content')
        </div>
        <div class="drawer-side">
            <label class="drawer-overlay" for="my-drawer" aria-label="close sidebar"></label>
            <ul class="menu bg-base-200 text-base-content min-h-full w-80 p-4">
                <a href="{{ route('document.index') }}">
                    <img class="m-3 mx-auto w-24" src="{{ asset('images/Vertical Logo.png') }}" alt="logo">
                </a>
                <li class="menu-title">Praram9 - DMS</li>
                <div class="text-info-content bg-neutral-content mb-1 flex flex-col gap-0.5 rounded-md p-3 text-xs">
                    <div><i class="fa-regular fa-user"></i> : {{ auth()->user()->userid }} <a class="text-error float-right cursor-pointer" onclick="logoutRequest()">ออกจากระบบ</a></div>
                    <div><i class="fa-solid fa-minus"></i> : {{ auth()->user()->name }}</div>
                    <div><i class="fa-solid fa-minus"></i> : {{ auth()->user()->department }}</div>
                </div>
                <li class="mb-1"><a class="nav-link" data-route="document.index" href="{{ route('document.index') }}">เอกสารทั้งหมด</a></li>
                <li class="mb-1"><a class="nav-link" data-route="document.create" href="{{ route('document.create') }}">สร้างเอกสาร</a></li>
                @if (auth()->user()->role !== 'user' && auth()->user()->menu)
                    @php
                        $menuGroups = auth()->user()->menu['groups'] ?? [];
                        $menuLists = auth()->user()->menu['lists'] ?? [];
                        $menuCounts = auth()->user()->menu['count'] ?? [];
                    @endphp

                    @if (! empty($menuGroups))
                        <li class="mb-2 mt-2 px-1">
                            <div class="form-control w-full">
                                <label class="label py-1" for="menu-group-select">
                                    <span class="label-text text-xs font-semibold">กลุ่มเมนู</span>
                                </label>
                                <select class="select select-bordered select-sm w-full" id="menu-group-select">
                                    @foreach ($menuGroups as $group)
                                        <option value="{{ $group['key'] }}">{{ $group['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </li>
                        @foreach ($menuGroups as $group)
                            <ul class="menu-group-panel hidden" data-group="{{ $group['key'] }}">
                                @foreach ($group['menus'] as $link)
                                    @if ($link['link'] == null)
                                        <li>
                                            <div class="divider">{{ $link['title'] }}</div>
                                        </li>
                                    @else
                                        <li class="mb-1">
                                            <a class="nav-link" data-route="{{ $link['type'] }}.{{ $link['link'] }}" href="{{ route($link['link'], ['type' => $link['type']]) }}">
                                                {{ $link['title'] }}
                                                @if ($link['count'])
                                                    <span class="badge badge-sm badge-primary float-right" id="{{ $link['type'] }}.{{ $link['id'] }}">-</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endforeach
                    @else
                        <ul>
                            @foreach ($menuLists as $link)
                                @if ($link['link'] == null)
                                    <li>
                                        <div class="divider">{{ $link['title'] }}</div>
                                    </li>
                                @else
                                    <li class="mb-1">
                                        <a class="nav-link" data-route="{{ $link['type'] }}.{{ $link['link'] }}" href="{{ route($link['link'], ['type' => $link['type']]) }}">
                                            {{ $link['title'] }}
                                            @if ($link['count'])
                                                <span class="badge badge-sm badge-primary float-right" id="{{ $link['type'] }}.{{ $link['id'] }}">-</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                @endif
            </ul>
        </div>
    </div>
</body>

@if (session('success'))
    <script type="module">
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    </script>
@endif
@if (session('error'))
    <script type="module">
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
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

        @if (auth()->user()->role !== 'user' && auth()->user()->menu)
            @php
                $layoutGroups = collect(auth()->user()->menu['groups'] ?? [])->map(function (array $group) {
                    return [
                        'key' => $group['key'],
                        'counts' => collect($group['counts'])->map(function (array $count) {
                            return [
                                'url' => route($count['route'], ['type' => $count['type']]),
                            ];
                        })->values()->all(),
                    ];
                })->values()->all();

                $layoutFlatCounts = collect(auth()->user()->menu['count'] ?? [])->map(function (array $count) {
                    return [
                        'url' => route($count['route'], ['type' => $count['type']]),
                    ];
                })->values()->all();
            @endphp

            const menuGroups = @json($layoutGroups);
            const flatCounts = @json($layoutFlatCounts);
            const countTimers = {};

            function clearCountTimers() {
                Object.values(countTimers).forEach((timerId) => clearTimeout(timerId));
                Object.keys(countTimers).forEach((key) => delete countTimers[key]);
            }

            function pollCount(url, key) {
                axios.get(url).then(function(response) {
                    Object.keys(response.data).forEach(badgeKey => {
                        updateCount(badgeKey, response.data[badgeKey]);
                    });
                    countTimers[key] = setTimeout(() => {
                        pollCount(url, key);
                    }, 60 * 1000);
                });
            }

            function startCounts(counts) {
                clearCountTimers();
                counts.forEach((link, index) => {
                    pollCount(link.url, `${link.url}.${index}`);
                });
            }

            function showMenuGroup(groupKey) {
                document.querySelectorAll('.menu-group-panel').forEach((panel) => {
                    panel.classList.toggle('hidden', panel.getAttribute('data-group') !== groupKey);
                });

                const selectedGroup = menuGroups.find((group) => group.key === groupKey);
                startCounts(selectedGroup ? selectedGroup.counts : []);
                localStorage.setItem('activeMenuGroup', groupKey);
            }

            if (menuGroups.length > 0) {
                const select = document.getElementById('menu-group-select');
                const savedGroup = localStorage.getItem('activeMenuGroup');
                const initialGroup = menuGroups.some((group) => group.key === savedGroup)
                    ? savedGroup
                    : menuGroups[0].key;

                if (select) {
                    select.value = initialGroup;
                    select.addEventListener('change', function() {
                        showMenuGroup(this.value);
                    });
                }

                showMenuGroup(initialGroup);
            } else {
                startCounts(flatCounts);
            }
        @endif
    });
</script>
<script>
    function updateCount(id, number) {
        if (document.getElementById(id)) {
            document.getElementById(id).textContent = number;
        }
    }

    function logoutRequest() {
        Swal.fire({
            title: 'ต้องการออกจากระบบหรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ตกลง',
            cancelButtonText: 'ยกเลิก',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-error mx-3',
                cancelButton: 'btn btn-ghost mx-3'
            },
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.setItem('activeRoute', 'logout');
                axios.post('{{ route('logout') }}')
                    .then(function(response) {
                        if (response.data.status === 'success') {
                            window.location.href = '{{ route('login') }}';
                        }
                    });
            }
        });
    }
</script>
@stack('scripts')
</body>

</html>
