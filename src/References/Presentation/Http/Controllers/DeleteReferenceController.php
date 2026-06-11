<?php

declare(strict_types=1);

namespace Source\References\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Source\References\Application\Services\ReferenceService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class DeleteReferenceController extends Controller
{
    public function __invoke(ReferenceService $referenceService, string $referenceId): JsonResponse
    {
        try {
            $referenceService->deleteReference($referenceId);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while deleting the reference.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }
    }
}
