<?php

declare(strict_types=1);

namespace Source\Pages\Application\Queries;

use DomainException;
use Source\Pages\Application\DTOs\PageEditResponseDTO;
use Source\Pages\Domain\Repository\Repository;

readonly class GetPageForEdit
{
    public function __construct(
        private Repository $repository
    ) {}

    public function execute(string $uuid): PageEditResponseDTO
    {
        $page = $this->repository->findByUuid($uuid, withImages: true);

        if (! $page) {
            throw new DomainException('Page not found.');
        }

        return PageEditResponseDTO::fromEntity($page);
    }
}
