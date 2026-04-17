<?php

declare(strict_types=1);

namespace Source\Pages\Domain\Repository;

use Source\Pages\Domain\ValueObjects\CreatePage;

interface Repository
{
    public function create(CreatePage $payload): CreatePage;

    /** @param array<string> $slugs */
    public function isSlugUnique(array $slugs): bool;

    public function findByUuid(string $uuid): CreatePage;
}
