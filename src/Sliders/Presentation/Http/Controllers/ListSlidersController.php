<?php

declare(strict_types=1);

namespace Source\Sliders\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Source\Sliders\Application\Queries\GetSliders;
use Throwable;

class ListSlidersController extends Controller
{
    public function __invoke(GetSliders $getSliders): JsonResponse
    {
        try {
            $sliders = $getSliders->execute();

            return response()->json(
                [
                    'status' => 'success',
                    'sliders' => $sliders,
                ],
                Response::HTTP_OK,
            );
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while fetching sliders.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }
    }
}
