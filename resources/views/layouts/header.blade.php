<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('tab-title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-orange-50 min-h-screen">

    <nav class="bg-white shadow-sm border-b border-orange-100 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold text-orange-600">@yield('page-title')</h1>
            <div class="flex items-center gap-4">
                <span class="text-gray-600 text-sm">{{ __('panel/dashboard.hello') }}<strong>{{ Auth::user()->name }}</strong></span>
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

    <main class="container mx-auto mt-10 p-6">
        @yield('content')
    </main>

</body>

</html>
