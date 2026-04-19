<?php

declare(strict_types=1);

namespace Source\Pages\Domain\Repository;

use Source\Pages\Domain\Entity\PageEntity;
use Source\Pages\Domain\ValueObjects\CreatePage;
use Source\Pages\Domain\ValueObjects\UpdatePage;

interface Repository
{
    public function create(CreatePage $payload): CreatePage;

    /** @param array<string> $slugs */
    public function isSlugUnique(array $slugs): bool;

    public function findByUuid(string $uuid): PageEntity|null;

    public function updatePage(UpdatePage $payload): void;

    /** @return array<string, mixed> */
    public function listPages(): array;

    public function findOriginalIdByUuid(string $uuid): int|null;
}
