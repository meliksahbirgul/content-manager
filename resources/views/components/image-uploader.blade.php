@php
    /** @var string|null $pageUuid */
    /** @var list<\Source\Media\Application\DTOs\MediaResponseDTO> $images */
    $pageUuid ??= null;
    $images   ??= [];
    $disabled = $pageUuid === null;
@endphp

<div class="bg-white border border-gray-200 rounded-xl p-4">
    <p class="text-sm text-gray-500 mb-3">Images</p>

    {{-- Existing thumbnails --}}
    <div id="media-thumbnails" class="flex flex-wrap gap-3 mb-4">
        @foreach ($images as $image)
            <div class="relative w-28 h-28 rounded-xl overflow-hidden bg-gradient-to-br from-orange-200 to-orange-100 shrink-0"
                 id="thumb-{{ $image->uuid() }}"
                 data-media-uuid="{{ $image->uuid() }}">
                <img src="{{ $image->url() }}"
                     alt="{{ $image->altText() ?? $image->originalName() }}"
                     class="w-full h-full object-cover">
                <button type="button"
                        onclick="deleteMedia('{{ $image->uuid() }}')"
                        title="Remove"
                        class="absolute top-2 right-2 w-5 h-5 bg-gray-500 hover:bg-red-500 rounded-full flex items-center justify-center transition">
                    <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endforeach
    </div>

    @if ($disabled)
        {{-- Create page: no pageUuid yet --}}
        <div class="border-2 border-dashed border-orange-200 rounded-xl px-6 py-8 text-center select-none">
            <p class="text-sm text-gray-400">Save the page first to upload images</p>
        </div>
    @else
        {{-- Drop zone --}}
        <div id="media-drop-zone"
             class="border-2 border-dashed border-orange-300 rounded-xl px-6 py-8 text-center cursor-pointer transition hover:bg-orange-50"
             onclick="document.getElementById('media-file-input').click()"
             ondragover="event.preventDefault(); this.classList.add('bg-orange-50')"
             ondragleave="this.classList.remove('bg-orange-50')"
             ondrop="handleMediaDrop(event)">
            <p class="text-sm text-gray-500">
                Drag &amp; drop or
                <span class="text-orange-500 font-medium">browse files</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG &mdash; max 10 MB</p>
        </div>

        <input type="file"
               id="media-file-input"
               class="hidden"
               accept=".jpg,.jpeg,.png,.gif,.webp,.svg"
               multiple
               onchange="handleMediaFiles(this.files); this.value=''">

        <p id="media-upload-error" class="hidden text-xs text-red-500 mt-2"></p>
        <p id="media-uploading"    class="hidden text-xs text-gray-400 mt-2">Uploading…</p>

        <script>
            (function () {
                const uploadUrl  = @json(route('panel.pages.media.upload', $pageUuid));
                const deleteBase = '/panel/media/';
                const csrf       = document.querySelector('meta[name="csrf-token"]').content;

                window.handleMediaDrop = function (e) {
                    e.preventDefault();
                    document.getElementById('media-drop-zone').classList.remove('bg-orange-50');
                    handleMediaFiles(e.dataTransfer.files);
                };

                window.handleMediaFiles = function (files) {
                    Array.from(files).forEach(uploadFile);
                };

                function uploadFile(file) {
                    setUploading(true);

                    const body = new FormData();
                    body.append('file', file);
                    body.append('collection', 'images');

                    fetch(uploadUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body,
                    })
                    .then(function (r) {
                        return r.json().then(function (data) {
                            return { ok: r.ok, data };
                        });
                    })
                    .then(function ({ ok, data }) {
                        setUploading(false);
                        if (!ok) {
                            var msg = (data.errors && data.errors.file)
                                ? data.errors.file[0]
                                : (data.message || 'Upload failed.');
                            showError(msg);
                            return;
                        }
                        clearError();
                        appendThumb(data);
                    })
                    .catch(function () {
                        setUploading(false);
                        showError('Upload failed. Please try again.');
                    });
                }

                window.deleteMedia = function (uuid) {
                    fetch(deleteBase + uuid, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    })
                    .then(function (r) {
                        if (r.ok || r.status === 204) {
                            var el = document.getElementById('thumb-' + uuid);
                            if (el) el.remove();
                        }
                    });
                };

                function appendThumb(media) {
                    var container = document.getElementById('media-thumbnails');
                    var div = document.createElement('div');
                    div.className = 'relative w-28 h-28 rounded-xl overflow-hidden bg-gradient-to-br from-orange-200 to-orange-100 shrink-0';
                    div.id = 'thumb-' + media.id;
                    div.dataset.mediaUuid = media.id;
                    div.innerHTML =
                        '<img src="' + media.url + '" alt="' + (media.alt_text || media.original_name) + '" class="w-full h-full object-cover">' +
                        '<button type="button" onclick="deleteMedia(\'' + media.id + '\')" title="Remove"' +
                        ' class="absolute top-2 right-2 w-5 h-5 bg-gray-500 hover:bg-red-500 rounded-full flex items-center justify-center transition">' +
                        '<svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>' +
                        '</svg></button>';
                    container.appendChild(div);
                }

                function setUploading(active) {
                    document.getElementById('media-uploading').classList.toggle('hidden', !active);
                    document.getElementById('media-drop-zone').style.pointerEvents = active ? 'none' : '';
                }

                function showError(msg) {
                    var el = document.getElementById('media-upload-error');
                    el.textContent = msg;
                    el.classList.remove('hidden');
                }

                function clearError() {
                    document.getElementById('media-upload-error').classList.add('hidden');
                }
            })();
        </script>
    @endif
</div>
