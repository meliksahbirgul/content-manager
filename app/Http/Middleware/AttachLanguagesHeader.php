<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Source\Languages\Application\Services\LanguageService;
use Symfony\Component\HttpFoundation\Response;

class AttachLanguagesHeader
{
    public function __construct(
        private LanguageService $languageService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $languages = array_map(
            fn ($dto) => $dto->jsonSerialize(),
            $this->languageService->listActive(),
        );

        $response->headers->set('X-Languages', json_encode($languages));

        return $response;
    }
}
