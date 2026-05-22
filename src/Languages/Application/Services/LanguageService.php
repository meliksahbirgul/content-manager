<?php

declare(strict_types=1);

namespace Source\Languages\Application\Services;

use Source\Languages\Application\DTOs\LanguageResponseDTO;
use Source\Languages\Domain\Repository\LanguageRepository;

class LanguageService
{
    public function __construct(
        private LanguageRepository $repository,
    ) {}

    /** @return list<LanguageResponseDTO> */
    public function listAll(): array
    {
        return array_map(
            fn($entity) => LanguageResponseDTO::fromEntity($entity),
            $this->repository->all(),
        );
    }
}
