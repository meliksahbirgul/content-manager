<?php

declare(strict_types=1);

namespace Source\References\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Source\References\Application\DTOs\CreateReferenceDTO;
use Source\References\Application\Services\ReferenceService;
use Source\References\Presentation\Http\Requests\CreateReferenceRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class CreateReferenceController extends Controller
{
    public function __invoke(CreateReferenceRequest $request, ReferenceService $referenceService): JsonResponse
    {
        try {
            $dto = CreateReferenceDTO::fromRequest($request->validated());
            $reference = $referenceService->createReference($dto);

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Successfully created reference.',
                    'reference' => ['id' => $reference->id()],
                ],
                Response::HTTP_CREATED,
            );
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while creating the reference.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }
    }
}
