<?php

declare(strict_types=1);

namespace Source\Sliders\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Source\Media\Application\DTOs\UploadMediaDTO;
use Source\Media\Application\Services\MediaService;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;
use Source\Media\Presentation\Http\Requests\UploadMediaRequest;
use Source\Sliders\Domain\Models\Slider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class UploadSliderMediaController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function __invoke(UploadMediaRequest $request, string $sliderId): JsonResponse
    {
        /** @var Slider $slider */
        $slider = Slider::where('uuid', $sliderId)->firstOrFail();

        $validated = $request->validated();

        /** @var UploadedFile $file */
        $file = $validated['file'];

        $disk = MediaDisk::tryFrom((string) config('filesystems.default', 'public')) ?? MediaDisk::Public;

        $dto = new UploadMediaDTO(
            mediableType: Slider::class,
            mediableId: (int) $slider->id,
            file: $file,
            collection: MediaCollection::from((string) $validated['collection']),
            disk: $disk,
            altText: isset($validated['alt_text']) ? (string) $validated['alt_text'] : null,
            linkPageUuid: isset($validated['link_page_uuid']) ? (string) $validated['link_page_uuid'] : null,
            order: isset($validated['order']) ? (int) $validated['order'] : 0,
        );

        try {
            $result = $this->mediaService->upload($dto);

            return response()->json($result->jsonSerialize(), Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
