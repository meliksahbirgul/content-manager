<?php

declare(strict_types=1);

namespace Source\References\Application\Services;

use DomainException;
use Source\References\Application\DTOs\CreateReferenceDTO;
use Source\References\Application\DTOs\UpdateReferenceDTO;
use Source\References\Domain\Repository\ReferenceRepository;
use Source\References\Domain\ValueObjects\CreateReference;
use Source\References\Domain\ValueObjects\UpdateReference;

readonly class ReferenceService
{
    public function __construct(private ReferenceRepository $repository) {}

    public function createReference(CreateReferenceDTO $dto): CreateReference
    {
        $payload = CreateReference::createFromArray($dto->toArray());

        return $this->repository->create($payload);
    }

    public function updateReference(UpdateReferenceDTO $dto): void
    {
        $payload = UpdateReference::createFromArray($dto->toArray());

        if (! $this->repository->findByUuid($payload->id())) {
            throw new DomainException('Reference not found.');
        }

        $this->repository->update($payload);
    }

    public function deleteReference(string $uuid): void
    {
        if (! $this->repository->findByUuid($uuid)) {
            throw new DomainException('Reference not found.');
        }

        $this->repository->delete($uuid);
    }
}
