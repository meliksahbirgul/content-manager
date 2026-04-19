<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|array|min:1',
            'title.*' => 'required_with:title|string|max:255',

            'slug' => 'sometimes|array',
            'slug.*' => 'required_with:slug|string|max:255',

            'content' => 'sometimes|array',
            'content.*' => 'required_with:content|string',
            'order' => 'nullable|integer|min:0',
            'status' => 'sometimes|string',
        ];
    }
}
