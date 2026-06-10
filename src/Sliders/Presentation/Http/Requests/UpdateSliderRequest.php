<?php

declare(strict_types=1);

namespace Source\Sliders\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSliderRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|array|min:1',
            'title.*' => 'required_with:title|string|max:255',
            'href' => 'sometimes|url|max:2048',
            'order' => 'nullable|integer|min:0',
            'status' => 'sometimes|string',
        ];
    }
}
