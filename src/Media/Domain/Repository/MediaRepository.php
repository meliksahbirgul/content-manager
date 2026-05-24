<?php

declare(strict_types=1);

namespace Source\Media\Domain\Repository;

use Source\Media\Domain\Entity\MediaEntity;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\ValueObjects\UploadMedia;

interface MediaRepository
{
    public function save(UploadMedia $payload): MediaEntity;

    public function findByUuid(string $uuid): ?MediaEntity;

    /** @return list<MediaEntity> */
    public function findForModel(string $mediableType, int $mediableId, ?MediaCollection $collection = null): array;

    public function delete(string $uuid): void;
}
