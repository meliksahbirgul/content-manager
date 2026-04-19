<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Source\Pages\Application\DTOs\UpdatePageDTO;
use Source\Pages\Application\Services\PageService;
use Source\Pages\Presentation\Http\Requests\UpdatePageRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class ChangePageDetailsController extends Controller
{
    public function __invoke(UpdatePageRequest $request, PageService $pageService, string $pageId): JsonResponse
    {
        try {
            $data       = $request->validated();
            $data['id'] = $pageId;
            $dto        = UpdatePageDTO::fromRequest($data);
            $pageService->updatePage($dto);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while updating the page.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
