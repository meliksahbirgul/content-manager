<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePageRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => 'required|array|min:1',
            'title.*' => 'required|string|max:255',

            'slug' => 'required|array',
            'slug.*' => 'required|string|max:255',

            'content' => 'nullable|array',
            'content.*' => 'nullable|string',

            'parentId' => 'nullable|uuid|exists:pages,uuid',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|string',
        ];
    }
}
