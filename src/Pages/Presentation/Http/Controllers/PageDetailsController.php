<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Source\Pages\Application\Queries\GetPageForEdit;
use Throwable;

class PageDetailsController extends Controller
{
    public function __invoke(GetPageForEdit $getPageForEdit, string $pageId): JsonResponse
    {
        try {
            $page = $getPageForEdit->execute($pageId);
            return response()->json(
                [
                    'status' => 'success',
                    'page' => $page,
                ],
                Response::HTTP_OK,
            );
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Page could not get.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }
    }
}
