<?php

declare(strict_types=1);

namespace Source\References\Domain\Repository;

use Source\References\Domain\Entity\ReferenceEntity;
use Source\References\Domain\ValueObjects\CreateReference;
use Source\References\Domain\ValueObjects\UpdateReference;

interface ReferenceRepository
{
    public function create(CreateReference $payload): CreateReference;

    public function findByUuid(string $uuid, bool $withImages = false): ?ReferenceEntity;

    public function update(UpdateReference $payload): void;

    public function delete(string $uuid): void;

    /** @return array<string, mixed> */
    public function listAll(): array;
}
