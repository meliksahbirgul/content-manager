<?php

declare(strict_types=1);

namespace Source\Media\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg|mimetypes:image/jpeg,image/png,image/gif,image/webp,image/svg+xml',
            'collection' => 'required|string|in:images,avatar,thumbnail,default',
            'alt_text' => 'nullable|string|max:255',
            'link_page_uuid' => 'nullable|uuid|exists:pages,uuid',
            'order' => 'nullable|integer|min:0',
        ];
    }
}
