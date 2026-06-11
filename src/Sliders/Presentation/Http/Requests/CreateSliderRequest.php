<?php

declare(strict_types=1);

namespace Source\Sliders\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSliderRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => 'required|array|min:1',
            'title.*' => 'required|string|max:255',
            'href' => 'required|array|min:1',
            'href.*' => 'required|url|max:2048',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|string',
        ];
    }
}
