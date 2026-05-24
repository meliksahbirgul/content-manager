<?php

declare(strict_types=1);

namespace Source\Languages\Presentation\Http\Controllers\View;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Source\Languages\Application\Services\LanguageService;

class SwitchLanguageController
{
    public function __construct(private LanguageService $languageService) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $code = (string) $request->input('code', '');

        $validCodes = array_map(
            fn ($dto) => $dto->code(),
            $this->languageService->listActive(),
        );

        if (in_array($code, $validCodes, true)) {
            $request->session()->put('locale', $code);
        }

        return back();
    }
}
