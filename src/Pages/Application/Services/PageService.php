<?php

declare(strict_types=1);

namespace Source\Pages\Application\Services;

use DomainException;
use Source\Pages\Application\DTOs\CreatePageDTO;
use Source\Pages\Domain\Repository\Repository;
use Source\Pages\Domain\ValueObjects\CreatePage;

readonly class PageService
{
    public function __construct(
        private Repository $repository
    ) {}

    public function createPage(CreatePageDTO $dto): CreatePage
    {
        $pagePayload = CreatePage::createFromArray($dto->toArray());

        if (! $this->repository->isSlugUnique($pagePayload->slug())) {
            throw new DomainException('This slug is already taken.');
        }

        return $this->repository->create($pagePayload);
    }
}
