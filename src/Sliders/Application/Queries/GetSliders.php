<?php

declare(strict_types=1);

namespace Source\Sliders\Application\Queries;

use Source\Sliders\Application\DTOs\SliderResponseDTO;
use Source\Sliders\Domain\Repository\SliderRepository;

readonly class GetSliders
{
    public function __construct(private SliderRepository $repository) {}

    /** @return list<SliderResponseDTO> */
    public function execute(): array
    {
        $sliders = $this->repository->listAll();

        return array_values(array_map(
            fn (array $slider) => SliderResponseDTO::createFromArray($slider),
            $sliders,
        ));
    }
}
