<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('tab-title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-orange-50 min-h-screen">

    <nav class="bg-white border-b border-orange-100 px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold text-orange-600">@yield('page-title')</h1>
            <div class="flex items-center gap-4">
                <span
                    class="text-gray-600 text-sm">{{ __('panel/dashboard.hello') }}<strong>{{ Auth::user()->name }}</strong></span>
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

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="w-56 shrink-0 bg-white border-r border-gray-100 py-6 px-3">

            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-3 mb-2">
                Content
            </p>

            <nav class="space-y-0.5">
                @php
                    $navItem = fn(string $route, string $label, $badge = null) => [
                        'route' => $route,
                        'label' => $label,
                        'badge' => $badge,
                        'active' => request()->routeIs($route),
                    ];
                    $items = [
                        $navItem('panel.dashboard', 'Dashboard'),
                        $navItem('panel.pages', 'Pages', \Source\Pages\Domain\Models\Page::count()),
                        $navItem('panel.pages.create', 'Page editor'),
                    ];
                @endphp

                @foreach ($items as $item)
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition
                            {{ $item['active']
                                ? 'bg-orange-50 text-orange-600 font-medium'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span>{{ $item['label'] }}</span>
                        @if ($item['badge'] !== null)
                            <span
                                class="text-xs font-medium px-2 py-0.5 rounded-full
                                {{ $item['active'] ? 'bg-orange-100 text-orange-600' : 'bg-orange-100 text-orange-500' }}">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>

        </aside>

        {{-- Main content --}}
        <main class="flex-1 py-2 px-4">
            @yield('content')
        </main>

    </div>

</body>

</html>
