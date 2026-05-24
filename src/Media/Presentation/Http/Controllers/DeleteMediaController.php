<?php

declare(strict_types=1);

namespace Source\Media\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\Response;
use Source\Media\Application\Services\MediaService;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteMediaController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function __invoke(string $mediaId): JsonResponse|Response
    {
        try {
            $this->mediaService->delete($mediaId);

            return response()->noContent();
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
