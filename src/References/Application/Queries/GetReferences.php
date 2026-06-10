<?php

declare(strict_types=1);

namespace Source\References\Application\Queries;

use Source\References\Application\DTOs\ReferenceResponseDTO;
use Source\References\Domain\Repository\ReferenceRepository;

readonly class GetReferences
{
    public function __construct(private ReferenceRepository $repository) {}

    /** @return list<ReferenceResponseDTO> */
    public function execute(): array
    {
        $references = $this->repository->listAll();

        return array_values(array_map(
            fn (array $reference) => ReferenceResponseDTO::createFromArray($reference),
            $references,
        ));
    }
}
