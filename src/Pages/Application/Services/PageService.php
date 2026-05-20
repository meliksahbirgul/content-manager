<?php

declare(strict_types=1);

namespace Source\Pages\Application\Services;

use DomainException;
use Source\Pages\Application\Contracts\ActivityLogger;
use Source\Pages\Application\DTOs\CreatePageDTO;
use Source\Pages\Application\DTOs\UpdatePageDTO;
use Source\Pages\Domain\Repository\Repository;
use Source\Pages\Domain\ValueObjects\CreatePage;
use Source\Pages\Domain\ValueObjects\UpdatePage;

readonly class PageService
{
    public function __construct(
        private Repository $repository,
        private ActivityLogger $activityLogger,
    ) {}

    public function createPage(CreatePageDTO $dto): CreatePage
    {
        $pagePayload = CreatePage::createFromArray($dto->toArray());

        if (! $this->repository->isSlugUnique($pagePayload->slug())) {
            throw new DomainException('This slug is already taken.');
        }

        if ($pagePayload->parentId() !== null) {
            $parentPageId = $this->repository->findOriginalIdByUuid($pagePayload->parentId());
            if (! $parentPageId) {
                throw new DomainException('Parent page not found.');
            }

            $pagePayload->setParentOriginalId($parentPageId);
        }

        $result = $this->repository->create($pagePayload);
        $this->activityLogger->logPageCreated($pagePayload->id(), $pagePayload->status()->value);

        return $result;
    }

    public function updatePage(UpdatePageDTO $dto): void
    {
        $payload = UpdatePage::createFromArray($dto->toArray());
        $page = $this->repository->findByUuid($payload->id());
        if (! $page) {
            throw new DomainException('Page not found.');
        }

        if ($payload->slug() !== null && ! $this->repository->isSlugUnique($payload->slug(), $payload->id())) {
            throw new DomainException('This slug is already taken.');
        }

        $this->repository->updatePage($payload);

        if ($payload->status() !== null) {
            $oldStatus = $page->status();
            if ($payload->status() !== $oldStatus) {
                $this->activityLogger->logPageStatusChanged(
                    $payload->id(),
                    $oldStatus->value,
                    $payload->status()->value,
                );
            }
        }
    }
}
