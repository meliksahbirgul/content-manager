<?php

declare(strict_types=1);

namespace Source\Sliders\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Source\Sliders\Application\DTOs\UpdateSliderDTO;
use Source\Sliders\Application\Services\SliderService;
use Source\Sliders\Presentation\Http\Requests\UpdateSliderRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class UpdateSliderController extends Controller
{
    public function __invoke(UpdateSliderRequest $request, SliderService $sliderService, string $sliderId): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['id'] = $sliderId;
            $dto = UpdateSliderDTO::fromRequest($data);
            $sliderService->updateSlider($dto);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while updating the slider.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }
    }
}
