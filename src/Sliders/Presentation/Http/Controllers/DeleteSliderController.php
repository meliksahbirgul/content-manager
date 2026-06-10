<?php

declare(strict_types=1);

namespace Source\Sliders\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Source\Sliders\Application\Services\SliderService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class DeleteSliderController extends Controller
{
    public function __invoke(SliderService $sliderService, string $sliderId): JsonResponse
    {
        try {
            $sliderService->deleteSlider($sliderId);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while deleting the slider.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }
    }
}
