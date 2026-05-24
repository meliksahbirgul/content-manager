<?php

declare(strict_types=1);

namespace Source\Media\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Source\Media\Application\DTOs\UploadMediaDTO;
use Source\Media\Application\Services\MediaService;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;
use Source\Media\Presentation\Http\Requests\UploadMediaRequest;
use Source\Pages\Domain\Models\Page;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class UploadMediaController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function __invoke(UploadMediaRequest $request, string $pageId): JsonResponse
    {
        /** @var Page $page */
        $page = Page::where('uuid', $pageId)->firstOrFail();

        $validated = $request->validated();

        /** @var UploadedFile $file */
        $file = $validated['file'];

        $disk = MediaDisk::tryFrom((string) config('filesystems.default', 'public')) ?? MediaDisk::Public;

        $dto = new UploadMediaDTO(
            mediableType: Page::class,
            mediableId: (int) $page->id,
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
