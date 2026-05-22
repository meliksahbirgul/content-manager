<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Source\Pages\Application\DTOs\ListPageDTO;
use Source\Pages\Application\Queries\GetPageTree;
use Throwable;

class ListPagesController extends Controller
{
    public function __invoke(Request $request, GetPageTree $getPageTree): JsonResponse
    {
        try {
            $pages = $getPageTree->execute(ListPageDTO::fromRequest($request->all()));

            return response()->json(
                [
                    'status' => 'success',
                    'pages' => $pages,
                ],
                Response::HTTP_OK,
            );
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while fetching pages.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
