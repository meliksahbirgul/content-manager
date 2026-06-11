<?php

declare(strict_types=1);

namespace Source\References\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Source\References\Application\DTOs\UpdateReferenceDTO;
use Source\References\Application\Services\ReferenceService;
use Source\References\Presentation\Http\Requests\UpdateReferenceRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class UpdateReferenceController extends Controller
{
    public function __invoke(UpdateReferenceRequest $request, ReferenceService $referenceService, string $referenceId): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['id'] = $referenceId;
            $dto = UpdateReferenceDTO::fromRequest($data);
            $referenceService->updateReference($dto);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while updating the reference.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }
    }
}
