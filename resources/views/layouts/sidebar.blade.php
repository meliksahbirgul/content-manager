<aside class="w-56 shrink-0 bg-white border-r border-gray-100 flex flex-col h-full overflow-y-auto">

    {{-- Branding --}}
    <div class="px-4 py-5 border-b border-gray-100 flex items-center">
        <span class="text-sm font-bold text-orange-600">CMS Headless Editor</span>
    </div>

    <div class="py-6 px-3 flex-1">

        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-3 mb-2">
            {{ __('panel/sidebar.content') }}
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
                    $navItem('panel.dashboard', __('panel/sidebar.dashboard')),
                    $navItem('panel.pages', __('panel/sidebar.pages'), \Source\Pages\Domain\Models\Page::count()),
                    $navItem('panel.pages.create', __('panel/sidebar.page_editor')),
                    $navItem('panel.hero', __('panel/sidebar.hero')),
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

    </div>

</aside>
