@extends('layouts.header')

@section('tab-title', __('panel/pages.editor_title'))
@section('page-title', __('panel/pages.title'))

@section('content')
    {{-- Editor header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">{{ __('panel/pages.editor_title') }}</h2>
        <div class="flex gap-3">
            <button type="button" id="btn-draft"
                class="px-5 py-2 text-sm font-medium border border-gray-300 rounded-xl bg-white hover:bg-gray-50 transition">
                {{ __('panel/pages.save_draft') }}
            </button>
            <button type="button" id="btn-publish"
                class="px-5 py-2 text-sm font-medium bg-orange-500 text-white rounded-xl hover:bg-orange-600 transition">
                {{ __('panel/pages.publish') }}
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form id="page-form" action="{{ route('panel.pages.update', $page->id()) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" id="status-input" value="{{ $page->status() }}">
        {{-- Hidden multi-lang fields submitted with form --}}
        <input type="hidden" name="title[en]" id="hidden-title-en">
        <input type="hidden" name="title[tr]" id="hidden-title-tr">
        <input type="hidden" name="content[en]" id="hidden-content-en">
        <input type="hidden" name="content[tr]" id="hidden-content-tr">
        <input type="hidden" name="slug[en]" id="hidden-slug-en">
        <input type="hidden" name="slug[tr]" id="hidden-slug-tr">

        <div class="grid grid-cols-3 gap-6 items-start">

            {{-- Left column --}}
            <div class="col-span-2 space-y-5">

                {{-- Page title --}}
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="block text-sm text-gray-500 mb-1.5">{{ __('panel/pages.page_title') }}</label>
                    <input type="text" id="title-input" autocomplete="off"
                        class="w-full border border-orange-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-200">
                </div>

                {{-- Content editor --}}
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="block text-sm text-gray-500 mb-1.5">{{ __('panel/pages.content') }}</label>
                    <div class="border border-orange-200 rounded-xl overflow-hidden bg-white">
                        <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 flex gap-4">
                            <button type="button" onclick="fmt('bold')"
                                class="text-sm font-bold text-gray-600 hover:text-gray-900 w-6 text-center">B</button>
                            <span class="w-px h-4 bg-gray-300"></span>
                            <button type="button" onclick="fmt('italic')"
                                class="text-sm italic text-gray-600 hover:text-gray-900 w-6 text-center">/</button>
                            <span class="w-px h-4 bg-gray-300"></span>
                            <button type="button" onclick="fmt('h2')"
                                class="text-sm font-semibold text-gray-600 hover:text-gray-900 w-6 text-center">H2</button>
                            <span class="w-px h-4 bg-gray-300"></span>
                            <button type="button" onclick="fmt('h3')"
                                class="text-sm font-semibold text-gray-600 hover:text-gray-900 w-6 text-center">H3</button>
                        </div>
                        <div id="content-editor" contenteditable="true"
                            class="min-h-52 p-4 text-sm text-gray-700 focus:outline-none empty:before:content-[attr(data-placeholder)] empty:before:text-gray-400"
                            data-placeholder="{{ __('panel/pages.content_placeholder') }}"></div>
                    </div>
                </div>

            </div>

            {{-- Right column --}}
            <div class="space-y-4">

                {{-- Slug --}}
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        {{ __('panel/pages.slug') }}
                    </p>
                    <input type="text" id="slug-input" autocomplete="off" placeholder="/page-slug"
                        class="w-full border border-orange-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-200">
                </div>

                {{-- Language --}}
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        {{ __('panel/pages.language') }}
                    </p>
                    <div class="flex gap-2">
                        <button type="button" id="lang-btn-en" onclick="switchLang('en')"
                            class="px-4 py-1.5 rounded-lg text-sm font-medium bg-orange-500 text-white transition">
                            EN
                        </button>
                        <button type="button" id="lang-btn-tr" onclick="switchLang('tr')"
                            class="px-4 py-1.5 rounded-lg text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            TR
                        </button>
                    </div>
                    <p id="lang-hint" class="text-xs text-gray-400 mt-2">{{ __('panel/pages.editing_lang_en') }}</p>
                </div>

                {{-- Parent page --}}
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        {{ __('panel/pages.parent_page') }}
                    </p>
                    <select name="parent_id"
                        class="w-full border border-orange-200 rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-200">
                        <option value="">{{ __('panel/pages.no_parent') }}</option>
                        @php
                            $locale = app()->getLocale();
                            $flatTree = [];
                            $stack = array_map(fn($p) => [$p, 0], array_reverse($pages));
                            while (!empty($stack)) {
                                [$node, $depth] = array_pop($stack);
                                if ($node->id() === $page->id()) {
                                    continue;
                                }
                                $flatTree[] = ['item' => $node, 'depth' => $depth];
                                foreach (array_reverse($node->children()) as $child) {
                                    $stack[] = [$child, $depth + 1];
                                }
                            }
                        @endphp
                        @foreach ($flatTree as $row)
                            @php
                                $p = $row['item'];
                                $label =
                                    $p->title()[$locale] ??
                                    ($p->title()['en'] ?? (array_values($p->title())[0] ?? $p->id()));
                                $prefix = str_repeat('— ', $row['depth']);
                            @endphp
                            <option value="{{ $p->id() }}" @selected($p->id() === $page->parentId())>
                                {{ $prefix }}{{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>
    </form>

    <script>
        const langHints = {
            en: @json(__('panel/pages.editing_lang_en')),
            tr: @json(__('panel/pages.editing_lang_tr')),
        };

        // Pre-populate store with existing page data
        const store = {
            en: {
                title: @json($page->title()['en'] ?? ''),
                content: @json($page->content()['en'] ?? ''),
                slug: @json($page->slug()['en'] ?? ''),
            },
            tr: {
                title: @json($page->title()['tr'] ?? ''),
                content: @json($page->content()['tr'] ?? ''),
                slug: @json($page->slug()['tr'] ?? ''),
            }
        };
        let currentLang = 'en';

        const titleInput = document.getElementById('title-input');
        const slugInput = document.getElementById('slug-input');
        const contentEditor = document.getElementById('content-editor');

        // Load initial EN values into visible inputs
        titleInput.value = store.en.title;
        contentEditor.innerHTML = store.en.content;
        slugInput.value = store.en.slug;

        // Format toolbar
        function fmt(cmd) {
            contentEditor.focus();
            if (cmd === 'h2' || cmd === 'h3') {
                document.execCommand('formatBlock', false, cmd);
            } else {
                document.execCommand(cmd);
            }
        }

        // Auto-slug from title (only when slug hasn't been manually edited)
        let slugManuallyEdited = store.en.slug !== '';
        slugInput.addEventListener('input', function() {
            slugManuallyEdited = true;
        });

        titleInput.addEventListener('input', function() {
            if (slugManuallyEdited) return;
            slugInput.value = '/' + this.value
                .toLowerCase()
                .replace(/[^\p{L}\p{N}\s-]/gu, '')
                .trim()
                .replace(/\s+/g, '-');
        });

        // Sync hidden inputs before submit
        function syncHiddenInputs() {
            store[currentLang].title = titleInput.value;
            store[currentLang].content = contentEditor.innerHTML;
            store[currentLang].slug = slugInput.value;

            ['en', 'tr'].forEach(function(lang) {
                document.getElementById('hidden-title-' + lang).value = store[lang].title;
                document.getElementById('hidden-content-' + lang).value = store[lang].content;
                document.getElementById('hidden-slug-' + lang).value = store[lang].slug;
            });
        }

        function switchLang(lang) {
            if (lang === currentLang) return;

            // Persist current lang data
            store[currentLang].title = titleInput.value;
            store[currentLang].content = contentEditor.innerHTML;
            store[currentLang].slug = slugInput.value;

            // Load new lang data
            currentLang = lang;
            titleInput.value = store[lang].title;
            contentEditor.innerHTML = store[lang].content;
            slugInput.value = store[lang].slug;
            slugManuallyEdited = store[lang].slug !== '';

            // Update button styles
            ['en', 'tr'].forEach(function(l) {
                const btn = document.getElementById('lang-btn-' + l);
                if (l === lang) {
                    btn.className =
                        'px-4 py-1.5 rounded-lg text-sm font-medium bg-orange-500 text-white transition';
                } else {
                    btn.className =
                        'px-4 py-1.5 rounded-lg text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition';
                }
            });

            document.getElementById('lang-hint').textContent = langHints[lang];
        }

        document.getElementById('btn-draft').addEventListener('click', function() {
            document.getElementById('status-input').value = 'passive';
            syncHiddenInputs();
            document.getElementById('page-form').submit();
        });

        document.getElementById('btn-publish').addEventListener('click', function() {
            document.getElementById('status-input').value = 'active';
            syncHiddenInputs();
            document.getElementById('page-form').submit();
        });
    </script>
@endsection
