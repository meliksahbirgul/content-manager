@extends('layouts.header')

@section('tab-title', __('panel/pages.title'))
@section('page-title', __('panel/pages.title'))

@section('content')
    @if (session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-orange-200 p-6">

        {{-- Search + Filter --}}
        <div class="flex gap-3 mb-6">
            <form method="GET" action="{{ route('panel.pages') }}" class="flex flex-1 gap-3">
                <input type="text" id="search-input" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('panel/pages.search_placeholder') }}"
                    class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-200 placeholder-gray-400"
                    autocomplete="off">
                <select name="status" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-200">
                    <option value="">{{ __('panel/pages.all_statuses') }}</option>
                    <option value="active" @selected(request('status') === 'active')>
                        {{ __('panel/pages.status_published') }}
                    </option>
                    <option value="passive" @selected(request('status') === 'passive')>
                        {{ __('panel/pages.status_draft') }}
                    </option>
                </select>
            </form>
            <a href="{{ route('panel.pages.create') }}"
                class="inline-flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition whitespace-nowrap">
                + {{ __('panel/pages.new_page') }}
            </a>
        </div>

        {{-- Table --}}
        <table class="w-full table-fixed">
            <colgroup>
                <col class="w-1/2">
                <col class="w-28">
                <col class="w-36">
                <col class="w-20">
            </colgroup>
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
                    $flatPages = [];
                    $stack = array_map(fn($p) => [$p, 0, null], array_reverse($pages));
                    while (!empty($stack)) {
                        [$node, $depth, $parentId] = array_pop($stack);
                        $flatPages[] = ['item' => $node, 'depth' => $depth, 'parentId' => $parentId];
                        foreach (array_reverse($node->children()) as $child) {
                            $stack[] = [$child, $depth + 1, $node->id()];
                        }
                    }
                    $locale = app()->getLocale();
                @endphp
                @forelse($flatPages as $row)
                    @php
                        $page = $row['item'];
                        $depth = $row['depth'];
                        $parentId = $row['parentId'];
                        $hasChildren = count($page->children()) > 0;
                        $title =
                            $page->title()[$locale] ??
                            ($page->title()['en'] ?? (array_values($page->title())[0] ?? '-'));

                        [$badgeClass, $statusLabel] = match ($page->status()) {
                            'active' => ['bg-orange-100 text-orange-700', __('panel/pages.status_published')],
                            default => ['bg-yellow-100 text-yellow-700', __('panel/pages.status_draft')],
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $depth > 0 ? 'hidden' : '' }}" data-id="{{ $page->id() }}"
                        @if ($depth > 0) data-parent-id="{{ $parentId }}" @endif>
                        <td class="py-4 text-sm font-medium text-gray-800 max-w-0" style="padding-left: {{ 16 + $depth * 24 }}px">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="truncate">{{ $title }}</span>
                                @if ($hasChildren)
                                    <button type="button" data-toggle="{{ $page->id() }}"
                                        onclick="toggleChildren('{{ $page->id() }}')"
                                        class="shrink-0 text-gray-400 hover:text-gray-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
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
                            <a href="{{ route('panel.pages.edit', $page->id()) }}"
                                class="text-orange-600 hover:underline">{{ __('panel/pages.action_edit') }}</a>
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
        document.getElementById('search-input').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                document.getElementById('search-input').closest('form').submit();
            }, 350);
        });

        const chevronDown =
            `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>`;
        const chevronUp =
            `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>`;

        function toggleChildren(pageId) {
            const btn = document.querySelector(`[data-toggle="${pageId}"]`);
            const children = document.querySelectorAll(`[data-parent-id="${pageId}"]`);
            const isExpanding = children.length > 0 && children[0].classList.contains('hidden');

            if (isExpanding) {
                children.forEach(row => row.classList.remove('hidden'));
                btn.innerHTML = chevronUp;
            } else {
                collapseAll(pageId);
                btn.innerHTML = chevronDown;
            }
        }

        function collapseAll(pageId) {
            document.querySelectorAll(`[data-parent-id="${pageId}"]`).forEach(row => {
                row.classList.add('hidden');
                const childId = row.dataset.id;
                if (childId) {
                    const childBtn = document.querySelector(`[data-toggle="${childId}"]`);
                    if (childBtn) childBtn.innerHTML = chevronDown;
                    collapseAll(childId);
                }
            });
        }
    </script>
@endsection
