@extends('layouts.header')

@section('tab-title', 'Hero')
@section('page-title', 'Hero')

@section('content')
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Hero / Main page editor</h2>
    </div>

    @php
        $defaultLang = app()->getLocale();
        $langCodes = array_map(fn($l) => $l->code(), $languages);
        if (!in_array($defaultLang, $langCodes)) {
            $defaultLang = $langCodes[0] ?? 'en';
        }
    @endphp

    {{-- Language tabs --}}
    <div class="flex gap-2 mb-5">
        @foreach ($languages as $lang)
            <button type="button"
                    class="lang-tab px-4 py-1.5 rounded-lg text-sm font-medium transition"
                    data-lang="{{ $lang->code() }}"
                    onclick="switchLang('{{ $lang->code() }}')">
                {{ strtoupper($lang->code()) }}
            </button>
        @endforeach
    </div>

    <div class="space-y-5">

        {{-- Image slider section --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-700">Image slider</p>
                <button type="button" onclick="openAddSlideModal()"
                        class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg bg-white hover:bg-gray-50 transition text-gray-600">
                    Add slide
                </button>
            </div>

            <div id="slider-list">
                @forelse ($sliders as $slider)
                    @php
                        $firstImage = $slider->images->first();
                        $displayName = $firstImage ? $firstImage->original_name : ($slider->title[$defaultLang] ?? 'Slide');
                    @endphp
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0 slider-row"
                         data-slider-id="{{ $slider->uuid }}"
                         data-href="{{ json_encode($slider->href ?? []) }}"
                         data-title="{{ json_encode($slider->title ?? []) }}">

                        {{-- Thumbnail --}}
                        <div class="relative w-14 h-14 rounded-xl overflow-hidden bg-gradient-to-br from-orange-200 to-orange-100 shrink-0">
                            @if ($firstImage)
                                <img src="{{ $firstImage->url }}" alt="{{ $firstImage->original_name }}" class="w-full h-full object-cover">
                            @endif
                            <button type="button"
                                    onclick="deleteSlider('{{ $slider->uuid }}')"
                                    class="absolute top-1 right-1 w-4 h-4 bg-gray-400 hover:bg-red-500 rounded-full flex items-center justify-center transition">
                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 font-medium truncate mb-1 slider-display-name">{{ $displayName }}</p>
                            <input type="text"
                                   class="slider-href-input w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-600 focus:outline-none focus:ring-1 focus:ring-orange-300"
                                   value="{{ $slider->href[$defaultLang] ?? '' }}"
                                   placeholder="https://"
                                   onblur="updateSliderHref('{{ $slider->uuid }}', this)">
                        </div>
                    </div>
                @empty
                    <p id="slider-empty-hint" class="px-4 py-6 text-sm text-gray-400 text-center">No slides yet. Click "Add slide" to get started.</p>
                @endforelse
            </div>
        </div>

        {{-- References section --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-700">References</p>
                <button type="button" onclick="document.getElementById('ref-file-input').click()"
                        class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg bg-white hover:bg-gray-50 transition text-gray-600">
                    Add
                </button>
            </div>

            <input type="file" id="ref-file-input" class="hidden"
                   accept=".jpg,.jpeg,.png,.gif,.webp,.svg"
                   onchange="handleReferenceFile(this.files[0]); this.value=''">

            <div class="p-4">
                <div id="references-grid" class="flex flex-wrap gap-3">
                    @foreach ($references as $reference)
                        @php $refImage = $reference->images->first(); @endphp
                        <div class="relative w-20 h-20 rounded-xl overflow-hidden bg-gradient-to-br from-orange-200 to-orange-100 shrink-0 ref-item"
                             data-ref-id="{{ $reference->uuid }}">
                            @if ($refImage)
                                <img src="{{ $refImage->url }}" alt="{{ $reference->name }}" class="w-full h-full object-cover">
                            @endif
                            <button type="button"
                                    onclick="deleteReference('{{ $reference->uuid }}')"
                                    class="absolute top-1 right-1 w-4 h-4 bg-gray-400 hover:bg-red-500 rounded-full flex items-center justify-center transition">
                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach

                    {{-- Empty add slot --}}
                    <div id="ref-add-slot"
                         class="w-20 h-20 rounded-xl border-2 border-dashed border-orange-200 cursor-pointer hover:border-orange-400 transition"
                         onclick="document.getElementById('ref-file-input').click()">
                    </div>
                </div>
                <p id="ref-upload-status" class="hidden text-xs text-gray-400 mt-2">Uploading…</p>
                <p id="ref-upload-error" class="hidden text-xs text-red-500 mt-2"></p>
            </div>
        </div>

    </div>

    {{-- Add slide modal --}}
    <div id="add-slide-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40"
         onclick="handleModalBackdropClick(event)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6" onclick="event.stopPropagation()">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-800">Add slide</h3>
                <button type="button" onclick="closeAddSlideModal()"
                        class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-gray-100 transition text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Image drop zone --}}
            <div id="modal-drop-zone"
                 class="border-2 border-dashed border-orange-300 rounded-xl px-6 py-8 text-center cursor-pointer hover:bg-orange-50 transition mb-4"
                 onclick="document.getElementById('modal-file-input').click()"
                 ondragover="event.preventDefault(); this.classList.add('bg-orange-50')"
                 ondragleave="this.classList.remove('bg-orange-50')"
                 ondrop="handleModalDrop(event)">
                <div id="modal-drop-placeholder">
                    <p class="text-sm text-gray-500">Drag &amp; drop or <span class="text-orange-500 font-medium">browse</span></p>
                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG — max 10 MB</p>
                </div>
                <div id="modal-image-preview" class="hidden">
                    <img id="modal-preview-img" src="" alt="" class="mx-auto max-h-32 rounded-xl object-contain">
                    <p id="modal-preview-name" class="text-xs text-gray-500 mt-2 truncate"></p>
                </div>
            </div>
            <input type="file" id="modal-file-input" class="hidden"
                   accept=".jpg,.jpeg,.png,.gif,.webp,.svg"
                   onchange="handleModalFile(this.files[0]); this.value=''">

            {{-- Href inputs per language --}}
            <div class="space-y-3 mb-5">
                @foreach ($languages as $lang)
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ strtoupper($lang->code()) }} link</label>
                        <input type="text"
                               id="modal-href-{{ $lang->code() }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-orange-300"
                               placeholder="https://">
                    </div>
                @endforeach
            </div>

            <p id="modal-error" class="hidden text-xs text-red-500 mb-4"></p>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeAddSlideModal()"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button" id="modal-submit-btn" onclick="submitAddSlide()"
                        class="px-4 py-2 text-sm font-medium bg-orange-500 text-white rounded-xl hover:bg-orange-600 transition">
                    Add slide
                </button>
            </div>
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const routes = {
            slidersStore:          @json(route('panel.hero.sliders.store')),
            slidersUpdate:         @json(route('panel.hero.sliders.update', ['sliderId' => '__ID__'])),
            slidersDestroy:        @json(route('panel.hero.sliders.destroy', ['sliderId' => '__ID__'])),
            slidersMediaUpload:    @json(route('panel.hero.sliders.media.upload', ['sliderId' => '__ID__'])),
            referencesStore:       @json(route('panel.hero.references.store')),
            referencesDestroy:     @json(route('panel.hero.references.destroy', ['referenceId' => '__ID__'])),
            referencesMediaUpload: @json(route('panel.hero.references.media.upload', ['referenceId' => '__ID__'])),
        };

        let currentLang = @json($defaultLang);
        let modalSelectedFile = null;

        // ── Language switching ─────────────────────────────────────────────

        function switchLang(lang) {
            currentLang = lang;

            document.querySelectorAll('.lang-tab').forEach(function (btn) {
                if (btn.dataset.lang === lang) {
                    btn.className = 'lang-tab px-4 py-1.5 rounded-lg text-sm font-medium transition bg-orange-500 text-white';
                } else {
                    btn.className = 'lang-tab px-4 py-1.5 rounded-lg text-sm font-medium transition border border-gray-200 text-gray-600 hover:bg-gray-50';
                }
            });

            document.querySelectorAll('.slider-row').forEach(function (row) {
                var hrefs = JSON.parse(row.dataset.href || '{}');
                var input = row.querySelector('.slider-href-input');
                if (input) input.value = hrefs[lang] || '';
            });
        }

        // initialise active tab style
        switchLang(currentLang);

        // ── Slider CRUD ────────────────────────────────────────────────────

        function updateSliderHref(sliderId, input) {
            var row = document.querySelector('[data-slider-id="' + sliderId + '"]');
            var hrefs = JSON.parse(row.dataset.href || '{}');
            var val = input.value.trim();

            if (val) {
                hrefs[currentLang] = val;
            } else {
                delete hrefs[currentLang];
            }
            row.dataset.href = JSON.stringify(hrefs);

            fetch(routes.slidersUpdate.replace('__ID__', sliderId), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ href: hrefs }),
            });
        }

        function deleteSlider(sliderId) {
            fetch(routes.slidersDestroy.replace('__ID__', sliderId), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            }).then(function (r) {
                if (r.ok || r.status === 204) {
                    var row = document.querySelector('[data-slider-id="' + sliderId + '"]');
                    if (row) row.remove();
                    maybeShowSliderEmptyHint();
                }
            });
        }

        function maybeShowSliderEmptyHint() {
            var list = document.getElementById('slider-list');
            var hint = document.getElementById('slider-empty-hint');
            if (!hint) return;
            hint.classList.toggle('hidden', list.querySelectorAll('.slider-row').length > 0);
        }

        function appendSliderRow(sliderId, displayName, hrefs, imageUrl) {
            var hint = document.getElementById('slider-empty-hint');
            if (hint) hint.classList.add('hidden');

            var list = document.getElementById('slider-list');

            var imgTag = imageUrl
                ? '<img src="' + imageUrl + '" alt="" class="w-full h-full object-cover">'
                : '';

            var hrefVal = hrefs[currentLang] || '';

            var html = '<div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0 slider-row"' +
                ' data-slider-id="' + sliderId + '"' +
                ' data-href=\'' + JSON.stringify(hrefs) + '\'' +
                ' data-title=\'{}\'>' +
                '<div class="relative w-14 h-14 rounded-xl overflow-hidden bg-gradient-to-br from-orange-200 to-orange-100 shrink-0">' +
                imgTag +
                '<button type="button" onclick="deleteSlider(\'' + sliderId + '\')"' +
                ' class="absolute top-1 right-1 w-4 h-4 bg-gray-400 hover:bg-red-500 rounded-full flex items-center justify-center transition">' +
                '<svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">' +
                '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></div>' +
                '<div class="flex-1 min-w-0">' +
                '<p class="text-sm text-gray-700 font-medium truncate mb-1 slider-display-name">' + displayName + '</p>' +
                '<input type="text" class="slider-href-input w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-600 focus:outline-none focus:ring-1 focus:ring-orange-300"' +
                ' value="' + hrefVal + '" placeholder="https://"' +
                ' onblur="updateSliderHref(\'' + sliderId + '\', this)">' +
                '</div></div>';

            list.insertAdjacentHTML('beforeend', html);
        }

        // ── Add slide modal ────────────────────────────────────────────────

        function openAddSlideModal() {
            modalSelectedFile = null;
            document.getElementById('modal-drop-placeholder').classList.remove('hidden');
            document.getElementById('modal-image-preview').classList.add('hidden');
            document.getElementById('modal-error').classList.add('hidden');
            document.getElementById('modal-error').textContent = '';
            document.querySelectorAll('[id^="modal-href-"]').forEach(function (el) {
                el.value = '';
            });

            var modal = document.getElementById('add-slide-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeAddSlideModal() {
            var modal = document.getElementById('add-slide-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function handleModalBackdropClick(e) {
            if (e.target === document.getElementById('add-slide-modal')) {
                closeAddSlideModal();
            }
        }

        function handleModalDrop(e) {
            e.preventDefault();
            document.getElementById('modal-drop-zone').classList.remove('bg-orange-50');
            var file = e.dataTransfer.files[0];
            if (file) handleModalFile(file);
        }

        function handleModalFile(file) {
            if (!file) return;
            modalSelectedFile = file;
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('modal-preview-img').src = e.target.result;
                document.getElementById('modal-preview-name').textContent = file.name;
                document.getElementById('modal-drop-placeholder').classList.add('hidden');
                document.getElementById('modal-image-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        function submitAddSlide() {
            var errorEl = document.getElementById('modal-error');
            errorEl.classList.add('hidden');

            var hrefs = {};
            document.querySelectorAll('[id^="modal-href-"]').forEach(function (el) {
                var lang = el.id.replace('modal-href-', '');
                var val = el.value.trim();
                if (val) hrefs[lang] = val;
            });

            if (Object.keys(hrefs).length === 0) {
                errorEl.textContent = 'Please enter at least one link.';
                errorEl.classList.remove('hidden');
                return;
            }

            var submitBtn = document.getElementById('modal-submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding…';

            var filename = modalSelectedFile ? modalSelectedFile.name : 'Slide';
            var title = {};
            Object.keys(hrefs).forEach(function (lang) { title[lang] = filename; });

            fetch(routes.slidersStore, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ title: title, href: hrefs }),
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error(res.data.message || 'Failed to create slide.');
                }

                var sliderId = res.data.slider.id;

                if (!modalSelectedFile) {
                    appendSliderRow(sliderId, filename, hrefs, null);
                    closeAddSlideModal();
                    resetModalBtn(submitBtn);
                    return;
                }

                var form = new FormData();
                form.append('file', modalSelectedFile);
                form.append('collection', 'images');

                return fetch(routes.slidersMediaUpload.replace('__ID__', sliderId), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: form,
                })
                .then(function (r) { return r.json(); })
                .then(function (media) {
                    appendSliderRow(sliderId, media.original_name || filename, hrefs, media.url || null);
                    closeAddSlideModal();
                    resetModalBtn(submitBtn);
                });
            })
            .catch(function (err) {
                errorEl.textContent = err.message || 'An error occurred.';
                errorEl.classList.remove('hidden');
                resetModalBtn(submitBtn);
            });
        }

        function resetModalBtn(btn) {
            btn.disabled = false;
            btn.textContent = 'Add slide';
        }

        // ── References CRUD ────────────────────────────────────────────────

        async function handleReferenceFile(file) {
            if (!file) return;

            var statusEl = document.getElementById('ref-upload-status');
            var errorEl  = document.getElementById('ref-upload-error');
            statusEl.classList.remove('hidden');
            errorEl.classList.add('hidden');

            try {
                var createRes = await fetch(routes.referencesStore, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ name: file.name }),
                });
                var createData = await createRes.json();
                if (!createRes.ok) throw new Error(createData.message || 'Failed to create reference.');

                var refId = createData.reference.id;

                var form = new FormData();
                form.append('file', file);
                form.append('collection', 'images');

                var uploadRes = await fetch(routes.referencesMediaUpload.replace('__ID__', refId), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: form,
                });
                var media = await uploadRes.json();
                if (!uploadRes.ok) throw new Error(media.message || 'Failed to upload image.');

                appendReferenceThumb(refId, media.url, file.name);
            } catch (err) {
                errorEl.textContent = err.message || 'An error occurred.';
                errorEl.classList.remove('hidden');
            } finally {
                statusEl.classList.add('hidden');
            }
        }

        function appendReferenceThumb(refId, imageUrl, altText) {
            var slot = document.getElementById('ref-add-slot');
            var div = document.createElement('div');
            div.className = 'relative w-20 h-20 rounded-xl overflow-hidden bg-gradient-to-br from-orange-200 to-orange-100 shrink-0 ref-item';
            div.dataset.refId = refId;
            div.innerHTML =
                '<img src="' + imageUrl + '" alt="' + (altText || '') + '" class="w-full h-full object-cover">' +
                '<button type="button" onclick="deleteReference(\'' + refId + '\')"' +
                ' class="absolute top-1 right-1 w-4 h-4 bg-gray-400 hover:bg-red-500 rounded-full flex items-center justify-center transition">' +
                '<svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">' +
                '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>';
            slot.insertAdjacentElement('beforebegin', div);
        }

        function deleteReference(refId) {
            fetch(routes.referencesDestroy.replace('__ID__', refId), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            }).then(function (r) {
                if (r.ok || r.status === 204) {
                    var el = document.querySelector('[data-ref-id="' + refId + '"]');
                    if (el) el.remove();
                }
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAddSlideModal();
        });
    </script>
@endsection
