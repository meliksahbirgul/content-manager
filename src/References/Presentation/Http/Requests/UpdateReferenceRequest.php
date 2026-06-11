<?php

declare(strict_types=1);

namespace Source\References\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReferenceRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'order' => 'nullable|integer|min:0',
        ];
    }
}
