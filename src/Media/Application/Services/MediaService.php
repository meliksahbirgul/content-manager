<?php

declare(strict_types=1);

namespace Source\Media\Application\Services;

use DomainException;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Source\Media\Application\Contracts\StorageDriver;
use Source\Media\Application\DTOs\MediaResponseDTO;
use Source\Media\Application\DTOs\UploadMediaDTO;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Repository\MediaRepository;
use Source\Media\Domain\ValueObjects\UploadMedia;

class MediaService
{
    public function __construct(
        private MediaRepository $repository,
        private StorageDriver $storageDriver,
    ) {}

    public function upload(UploadMediaDTO $dto): MediaResponseDTO
    {
        $uuid = Uuid::uuid7()->toString();

        $parts = explode('\\', $dto->mediableType);
        $modelSlug = strtolower((string) end($parts));

        $directory = "{$modelSlug}/{$dto->mediableId}/{$dto->collection->value}";

        $path = $this->storageDriver->storeAs($dto->file, $directory, $uuid);
        $url = $this->storageDriver->url($path);

        $sizeRaw = $dto->file->getSize();
        if ($sizeRaw === false) {
            throw new RuntimeException('Could not determine file size.');
        }

        $mimeType = $dto->file->getMimeType() ?? 'application/octet-stream';

        $payload = new UploadMedia(
            uuid: $uuid,
            mediableType: $dto->mediableType,
            mediableId: $dto->mediableId,
            collection: $dto->collection,
            disk: $dto->disk,
            path: $path,
            url: $url,
            originalName: $dto->file->getClientOriginalName(),
            mimeType: $mimeType,
            size: $sizeRaw,
            altText: $dto->altText,
            linkPageUuid: $dto->linkPageUuid,
            order: $dto->order,
        );

        $entity = $this->repository->save($payload);

        return MediaResponseDTO::fromEntity($entity);
    }

    public function delete(string $uuid): void
    {
        $entity = $this->repository->findByUuid($uuid);

        if ($entity === null) {
            throw new DomainException("Media with UUID {$uuid} not found.");
        }

        $this->storageDriver->delete($entity->path());
        $this->repository->delete($uuid);
    }

    /** @return list<MediaResponseDTO> */
    public function forModel(string $mediableType, int $mediableId, ?MediaCollection $collection = null): array
    {
        $entities = $this->repository->findForModel($mediableType, $mediableId, $collection);

        return array_map(fn ($entity) => MediaResponseDTO::fromEntity($entity), $entities);
    }
}
