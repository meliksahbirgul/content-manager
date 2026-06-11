<?php

declare(strict_types=1);

namespace Source\References\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReferenceRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ];
    }
}
