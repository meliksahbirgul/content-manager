<?php

declare(strict_types=1);

namespace Source\Sliders\Domain\Repository;

use Source\Sliders\Domain\Entity\SliderEntity;
use Source\Sliders\Domain\ValueObjects\CreateSlider;
use Source\Sliders\Domain\ValueObjects\UpdateSlider;

interface SliderRepository
{
    public function create(CreateSlider $payload): CreateSlider;

    public function findByUuid(string $uuid, bool $withImages = false): ?SliderEntity;

    public function update(UpdateSlider $payload): void;

    public function delete(string $uuid): void;

    /** @return array<string, mixed> */
    public function listAll(): array;
}
