<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('tab-title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-orange-50">

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        @include('layouts.sidebar')

        {{-- Right column: header + main --}}
        <div class="flex flex-col flex-1 min-w-0">

            <nav class="bg-white border-b border-orange-100 px-6 py-4 shrink-0">
                <div class="flex justify-between items-center">
                    <h1 class="text-xl font-bold text-orange-600">@yield('page-title')</h1>
                    <div class="flex items-center gap-4">
                        {{-- Language switcher --}}
                        <div class="relative" id="lang-switcher">
                            <button type="button" id="lang-toggle"
                                class="flex items-center gap-1 text-xs font-semibold bg-orange-100 hover:bg-orange-200 text-orange-700 px-3 py-1 rounded transition">
                                <span id="lang-current">{{ strtoupper(app()->getLocale()) }}</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="lang-dropdown"
                                class="absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-md min-w-20 hidden z-50">
                            </div>
                        </div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="text-xs bg-gray-100 hover:bg-red-50 hover:text-red-600 text-gray-500 px-3 py-1 rounded transition">
                                {{ __('panel/dashboard.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </nav>

            <script>
                (function() {
                    const toggle = document.getElementById('lang-toggle');
                    const dropdown = document.getElementById('lang-dropdown');
                    const switchUrl = '{{ route('panel.language.switch') }}';
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const currentCode = document.getElementById('lang-current').textContent.trim().toLowerCase();

                    fetch(window.location.href, {
                            method: 'HEAD'
                        })
                        .then(function(r) {
                            const raw = r.headers.get('X-Languages');
                            const languages = raw ? JSON.parse(raw) : [];
                            renderDropdown(languages);
                        })
                        .catch(function() {});

                    function renderDropdown(languages) {
                        dropdown.innerHTML = languages.map(function(lang) {
                            const isActive = lang.code === currentCode;
                            return '<form action="' + switchUrl + '" method="POST" style="margin:0">' +
                                '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                                '<input type="hidden" name="code" value="' + lang.code + '">' +
                                '<button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold hover:bg-orange-50 transition ' +
                                (isActive ? 'text-orange-600' : 'text-gray-600 hover:text-orange-600') + '">' +
                                lang.code.toUpperCase() +
                                '</button>' +
                                '</form>';
                        }).join('');
                    }

                    toggle.addEventListener('click', function(e) {
                        e.stopPropagation();
                        dropdown.classList.toggle('hidden');
                    });

                    document.addEventListener('click', function() {
                        dropdown.classList.add('hidden');
                    });
                })();
            </script>

            {{-- Main content --}}
            <main class="flex-1 overflow-y-auto py-2 px-4">
                @yield('content')
            </main>

        </div>

    </div>

</body>

</html>
