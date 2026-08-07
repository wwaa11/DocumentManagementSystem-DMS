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
    <script src="https://kit.fontawesome.com/a20e89230f.js" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="app-shell drawer lg:drawer-open">
        <input class="drawer-toggle" id="my-drawer" type="checkbox" />

        <div class="drawer-content app-main">
            <div class="app-topbar flex items-center gap-3 md:hidden">
                <label class="btn btn-primary btn-sm gap-2" for="my-drawer" aria-label="เปิดเมนู">
                    <i class="fas fa-bars"></i>
                    เมนู
                </label>
            </div>

            <div class="app-content">
                @yield('content')
            </div>
        </div>

        <div class="drawer-side z-50">
            <label class="drawer-overlay" for="my-drawer" aria-label="ปิดเมนู"></label>
            <aside class="app-sidebar">
                <a class="app-sidebar-brand" href="{{ route('document.index') }}">
                    <img class="h-12 w-auto object-contain" src="{{ asset('images/Vertical Logo.png') }}" alt="Praram9 logo">
                    <div class="text-center">
                        <p class="text-primary text-base font-bold tracking-tight">Praram9 DMS</p>
                        <p class="text-base-content/45 text-xs">Document Management</p>
                    </div>
                </a>

                <div class="app-user-card">
                    <div class="mb-1 flex items-center justify-between gap-2">
                        <span class="badge badge-primary badge-soft badge-xs">{{ auth()->user()->userid }}</span>
                        <button class="text-error hover:bg-error/10 cursor-pointer rounded px-1.5 py-0.5 text-[10px] font-semibold transition" type="button" onclick="logoutRequest()">
                            ออกจากระบบ
                        </button>
                    </div>
                    <p class="truncate font-semibold" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</p>
                    <p class="text-base-content/55 truncate" title="{{ auth()->user()->department }}">{{ auth()->user()->department }}</p>
                </div>

                @if (auth()->user()->role !== 'user' && auth()->user()->menu)
                    @php
                        $menuGroups = auth()->user()->menu['groups'] ?? [];
                        $menuLists = auth()->user()->menu['lists'] ?? [];
                    @endphp

                    @if (! empty($menuGroups))
                        <div class="app-menu-group-picker">
                            <span class="text-base-content/40 mb-1 block px-0.5 text-[10px] font-bold tracking-[0.14em] uppercase">
                                กลุ่มงาน
                            </span>
                            <details class="dropdown w-full" id="menu-group-dropdown">
                                <summary
                                    class="btn btn-sm btn-outline border-base-300 bg-base-100 hover:bg-base-200 w-full justify-between font-normal"
                                    id="menu-group-select"
                                    aria-label="เลือกกลุ่มเมนู"
                                >
                                    <span class="truncate" id="menu-group-label">{{ $menuGroups[0]['label'] ?? 'เลือกกลุ่มงาน' }}</span>
                                    <i class="fas fa-chevron-down text-[10px] opacity-50"></i>
                                </summary>
                                <ul class="dropdown-content menu bg-base-100 rounded-box border-base-200 z-50 mt-1 w-full border p-1 shadow-lg" role="listbox">
                                    @foreach ($menuGroups as $group)
                                        <li role="option">
                                            <button class="menu-group-option" type="button" data-group="{{ $group['key'] }}">
                                                {{ $group['label'] }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        </div>
                    @endif
                @endif

                <ul class="app-nav menu flex-1 overflow-y-auto pb-4 w-full">
                    <li class="menu-section">หลัก</li>
                    <li>
                        <a class="nav-link" data-route="document.index" href="{{ route('document.index') }}">
                            <i class="fas fa-folder-open w-4 text-center opacity-70"></i>
                            เอกสารทั้งหมด
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" data-route="document.create" href="{{ route('document.create') }}">
                            <i class="fas fa-plus-circle w-4 text-center opacity-70"></i>
                            สร้างเอกสาร
                        </a>
                    </li>
                    @if (auth()->user()->canCreateCourseForDepartment())
                        <li>
                            <a class="nav-link" data-route="document.course" href="{{ route('document.course') }}">
                                <i class="fas fa-book w-4 text-center opacity-70"></i>
                                แผนการฝึกในหน่วยงาน
                            </a>
                        </li>
                    @endif

                    @if (auth()->user()->role !== 'user' && auth()->user()->menu)
                        @if (! empty($menuGroups))
                            @foreach ($menuGroups as $group)
                                <li class="w-full !p-0">
                                    <ul class="menu menu-group-panel hidden w-full" data-group="{{ $group['key'] }}">
                                        @foreach ($group['menus'] as $link)
                                            @if ($link['link'] == null)
                                                <li class="menu-section">{{ $link['title'] }}</li>
                                            @else
                                                <li class="w-full">
                                                    <a class="nav-link" data-route="{{ $link['type'] }}.{{ $link['link'] }}" href="{{ route($link['link'], ['type' => $link['type']]) }}">
                                                        <span class="truncate">{{ $link['title'] }}</span>
                                                        @if ($link['count'])
                                                            <span class="badge badge-xs badge-primary" id="{{ $link['type'] }}.{{ $link['id'] }}">-</span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        @else
                            <li class="menu-section mt-3">เมนูงาน</li>
                            @foreach ($menuLists as $link)
                                @if ($link['link'] == null)
                                    <li class="menu-section">{{ $link['title'] }}</li>
                                @else
                                    <li>
                                        <a class="nav-link" data-route="{{ $link['type'] }}.{{ $link['link'] }}" href="{{ route($link['link'], ['type' => $link['type']]) }}">
                                            <span class="truncate">{{ $link['title'] }}</span>
                                            @if ($link['count'])
                                                <span class="badge badge-xs badge-primary" id="{{ $link['type'] }}.{{ $link['id'] }}">-</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endif
                </ul>
            </aside>
        </div>
    </div>

    @if (session('success'))
        <script type="module">
            window.Swal.fire({
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
            window.Swal.fire({
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
                window.axios.get(url).then(function(response) {
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
                const dropdown = document.getElementById('menu-group-dropdown');
                const label = document.getElementById('menu-group-label');
                const savedGroup = localStorage.getItem('activeMenuGroup');
                const initialGroup = menuGroups.some((group) => group.key === savedGroup)
                    ? savedGroup
                    : menuGroups[0].key;

                const groupLabels = {
                    @foreach ($menuGroups as $group)
                        '{{ $group['key'] }}': @json($group['label']),
                    @endforeach
                };

                function setGroupLabel(groupKey) {
                    if (label) {
                        label.textContent = groupLabels[groupKey] || groupKey;
                    }
                }

                document.querySelectorAll('.menu-group-option').forEach((option) => {
                    option.addEventListener('click', function() {
                        const groupKey = this.getAttribute('data-group');
                        setGroupLabel(groupKey);
                        showMenuGroup(groupKey);
                        if (dropdown) {
                            dropdown.open = false;
                        }
                    });
                });

                setGroupLabel(initialGroup);
                showMenuGroup(initialGroup);
            } else {
                startCounts(flatCounts);
            }
        @endif
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
    <script>
        function clearFormFieldErrors(form) {
            const $form = form ? $(form) : $('form');
            $form.find('.input-error, .select-error, .textarea-error').removeClass('input-error select-error textarea-error');
            $form.find('[data-field-error]').removeClass('ring-2 ring-error border-error').removeAttr('data-field-error');
        }

        function highlightInvalidField(selector, form) {
            clearFormFieldErrors(form);

            const $scope = form ? $(form) : $(document);
            const $el = $scope.find(selector).first();
            if (!$el.length) {
                return null;
            }

            if ($el.is('input[type="radio"], input[type="checkbox"]')) {
                const name = $el.attr('name');
                $scope.find(`input[name="${name}"]`).each(function () {
                    $(this).closest('label').addClass('ring-2 ring-error border-error').attr('data-field-error', '1');
                });

                const target = $el.closest('label')[0] || $el[0];
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });

                return $el[0];
            }

            if ($el.is('select')) {
                $el.addClass('select-error');
            } else if ($el.is('textarea')) {
                $el.addClass('textarea-error');
            } else if ($el.is('input')) {
                $el.addClass('input-error');
            } else {
                $el.addClass('ring-2 ring-error border-error rounded-lg').attr('data-field-error', '1');
            }

            $el[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            if ($el.is('input, select, textarea')) {
                setTimeout(() => $el.trigger('focus'), 300);
            }

            return $el[0];
        }

        function showValidationError(message, fieldSelector, form) {
            if (fieldSelector) {
                highlightInvalidField(fieldSelector, form);
            }

            return Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                text: message,
                confirmButtonText: 'ตกลง',
                buttonsStyling: false,
                customClass: { confirmButton: 'btn btn-primary' },
            });
        }
    </script>
    <script type="module">
        window.$(document).on('input change', '.input-error, .select-error, .textarea-error', function () {
            window.$(this).removeClass('input-error select-error textarea-error');
        });

        window.$(document).on('change', 'input[type="radio"], input[type="checkbox"]', function () {
            const name = window.$(this).attr('name');
            if (!name) {
                return;
            }

            window.$(`input[name="${name}"]`).closest('label[data-field-error]').removeClass('ring-2 ring-error border-error').removeAttr('data-field-error');
        });
    </script>
    @stack('scripts')
</body>

</html>
