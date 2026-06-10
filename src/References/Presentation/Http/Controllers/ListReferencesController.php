<?php

declare(strict_types=1);

namespace Source\References\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Source\References\Application\Queries\GetReferences;
use Throwable;

class ListReferencesController extends Controller
{
    public function __invoke(GetReferences $getReferences): JsonResponse
    {
        try {
            $references = $getReferences->execute();

            return response()->json(
                [
                    'status' => 'success',
                    'references' => $references,
                ],
                Response::HTTP_OK,
            );
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while fetching references.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }
    }
}
