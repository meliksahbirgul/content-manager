<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-orange-50 min-h-screen">

    <!-- Navigasyon Bar (Basic) -->
    <nav class="bg-white shadow-sm border-b border-orange-100 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold text-orange-600">{{ __('panel/dashboard.dashboard') }}</h1>
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

    <!-- İçerik Alanı -->
    <main class="container mx-auto mt-10 p-6">
        <div class="bg-white rounded-2xl shadow-sm border border-orange-100 p-8">
            <!-- Page Status Counts -->
            <div class="mt-8">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ($dashboard->pageStatusCounts() as $statusCount)
                        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-orange-600">{{ $statusCount->count() }}</p>
                            <p class="text-sm text-gray-500 mt-1 capitalize">{{ $statusCount->status() }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Activity Logs -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ __('panel/dashboard.recent_activities') }}</h3>
                <ul class="divide-y divide-orange-100">
                    @forelse($dashboard->recentActivityLogs() as $log)
                        <li class="py-3 flex justify-between items-start gap-4">
                            <div>
                                <p class="text-sm text-gray-800">{{ $log->description() }}</p>
                                @if ($log->event())
                                    <span
                                        class="inline-block mt-1 text-xs bg-orange-100 text-orange-700 rounded px-2 py-0.5">{{ $log->event() }}</span>
                                @endif
                            </div>
                            <span
                                class="text-xs text-gray-400 whitespace-nowrap">{{ $log->createdAt()->format('d.m.Y H:i') }}</span>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-gray-400">{{ __('panel/dashboard.no_activity') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </main>

</body>

</html>
