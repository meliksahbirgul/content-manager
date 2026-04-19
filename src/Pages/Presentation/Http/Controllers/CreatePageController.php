<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Source\Pages\Application\DTOs\CreatePageDTO;
use Source\Pages\Application\Services\PageService;
use Source\Pages\Presentation\Http\Requests\CreatePageRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class CreatePageController extends Controller
{
    public function __invoke(CreatePageRequest $request, PageService $pageService): JsonResponse
    {
        try {
            $dto = CreatePageDTO::fromRequest($request->validated());

            $page = $pageService->createPage($dto);

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Successfully created page.',
                    'page' => ['id' => $page->id()],
                ],
                Response::HTTP_CREATED,
            );
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while creating the page.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
