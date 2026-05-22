@extends('layouts.header')

@section('tab-title', __('panel/pages.title'))
@section('page-title', __('panel/pages.title'))

@section('content')
    @if (session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-orange-100 p-6">

        {{-- Search + Filter --}}
        <div class="flex gap-3 mb-6">
            <form method="GET" action="{{ route('panel.pages') }}" class="flex flex-1 gap-3">
                <input
                    type="text"
                    id="search-input"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ __('panel/pages.search_placeholder') }}"
                    class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-200 placeholder-gray-400"
                    autocomplete="off"
                >
                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-200"
                >
                    <option value="">{{ __('panel/pages.all_statuses') }}</option>
                    <option value="active" @selected(request('status') === 'active')>
                        {{ __('panel/pages.status_published') }}
                    </option>
                    <option value="passive" @selected(request('status') === 'passive')>
                        {{ __('panel/pages.status_draft') }}
                    </option>
                </select>
            </form>
            <a href="{{ route('panel.pages.create') }}" class="inline-flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition whitespace-nowrap">
                + {{ __('panel/pages.new_page') }}
            </a>
        </div>

        {{-- Table --}}
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left">
                    <th class="pb-3 text-sm font-medium text-gray-400 pl-4">{{ __('panel/pages.col_title') }}</th>
                    <th class="pb-3 text-sm font-medium text-gray-400">{{ __('panel/pages.col_status') }}</th>
                    <th class="pb-3 text-sm font-medium text-gray-400">{{ __('panel/pages.col_last_modified') }}</th>
                    <th class="pb-3 text-sm font-medium text-gray-400">{{ __('panel/pages.col_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php
                    // Flatten the tree into [{item, depth}] while preserving order
                    $flatPages = [];
                    $stack = array_map(fn($p) => [$p, 0], array_reverse($pages));
                    while (!empty($stack)) {
                        [$node, $depth] = array_pop($stack);
                        $flatPages[] = ['item' => $node, 'depth' => $depth];
                        foreach (array_reverse($node->children()) as $child) {
                            $stack[] = [$child, $depth + 1];
                        }
                    }
                    $locale = app()->getLocale();
                @endphp
                @forelse($flatPages as $row)
                    @php
                        $page  = $row['item'];
                        $depth = $row['depth'];
                        $title = $page->title()[$locale] ?? $page->title()['en'] ?? array_values($page->title())[0] ?? '-';

                        [$badgeClass, $statusLabel] = match($page->status()) {
                            'active' => ['bg-orange-100 text-orange-700', __('panel/pages.status_published')],
                            default  => ['bg-yellow-100 text-yellow-700', __('panel/pages.status_draft')],
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-4 text-sm font-medium text-gray-800"
                            style="padding-left: {{ 16 + $depth * 24 }}px">
                            @if ($depth > 0)
                                <span class="text-gray-300 mr-1">{{ str_repeat('—', $depth) }}</span>
                            @endif
                            {{ $title }}
                        </td>
                        <td class="py-4">
                            <span class="inline-block text-xs font-medium px-3 py-1 rounded-full {{ $badgeClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="py-4 text-sm text-gray-500">
                            {{ $page->updatedAt()?->diffForHumans() ?? '—' }}
                        </td>
                        <td class="py-4 text-sm">
                            <a href="#" class="text-orange-600 hover:underline">{{ __('panel/pages.action_edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-10 text-center text-sm text-gray-400">
                            {{ __('panel/pages.no_pages') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <script>
        let searchTimer;
        document.getElementById('search-input').addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                document.getElementById('search-input').closest('form').submit();
            }, 350);
        });
    </script>
@endsection
