<?php

declare(strict_types=1);

namespace Source\References\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Source\Media\Application\DTOs\UploadMediaDTO;
use Source\Media\Application\Services\MediaService;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;
use Source\Media\Presentation\Http\Requests\UploadMediaRequest;
use Source\References\Domain\Models\Reference;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class UploadReferenceMediaController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function __invoke(UploadMediaRequest $request, string $referenceId): JsonResponse
    {
        /** @var Reference $reference */
        $reference = Reference::where('uuid', $referenceId)->firstOrFail();

        $validated = $request->validated();

        /** @var UploadedFile $file */
        $file = $validated['file'];

        $disk = MediaDisk::tryFrom((string) config('filesystems.default', 'public')) ?? MediaDisk::Public;

        $dto = new UploadMediaDTO(
            mediableType: Reference::class,
            mediableId: (int) $reference->id,
            file: $file,
            collection: MediaCollection::from((string) $validated['collection']),
            disk: $disk,
            altText: isset($validated['alt_text']) ? (string) $validated['alt_text'] : null,
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
