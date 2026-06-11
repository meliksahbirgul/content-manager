<?php

declare(strict_types=1);

namespace Source\Sliders\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Source\Sliders\Application\DTOs\CreateSliderDTO;
use Source\Sliders\Application\Services\SliderService;
use Source\Sliders\Presentation\Http\Requests\CreateSliderRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class CreateSliderController extends Controller
{
    public function __invoke(CreateSliderRequest $request, SliderService $sliderService): JsonResponse
    {
        try {
            $dto = CreateSliderDTO::fromRequest($request->validated());
            $slider = $sliderService->createSlider($dto);

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Successfully created slider.',
                    'slider' => ['id' => $slider->id()],
                ],
                Response::HTTP_CREATED,
            );
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while creating the slider.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }
    }
}
