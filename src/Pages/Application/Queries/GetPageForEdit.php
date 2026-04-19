<?php

declare(strict_types=1);

namespace Source\Pages\Application\Queries;

use DomainException;
use Source\Pages\Domain\Repository\Repository;
use Source\Pages\Application\DTOs\PageEditResponseDTO;

readonly class GetPageForEdit
{
    public function __construct(
        private Repository $repository
    ) {}

    public function execute(string $uuid): PageEditResponseDTO
    {
        $page = $this->repository->findByUuid($uuid);

        if (! $page) {
            throw new DomainException('Page not found.');
        }

        return PageEditResponseDTO::fromEntity($page);
    }
}
