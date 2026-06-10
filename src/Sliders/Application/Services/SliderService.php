<?php

declare(strict_types=1);

namespace Source\Sliders\Application\Services;

use DomainException;
use Source\Languages\Domain\Repository\LanguageRepository;
use Source\Sliders\Application\DTOs\CreateSliderDTO;
use Source\Sliders\Application\DTOs\UpdateSliderDTO;
use Source\Sliders\Domain\Repository\SliderRepository;
use Source\Sliders\Domain\ValueObjects\CreateSlider;
use Source\Sliders\Domain\ValueObjects\UpdateSlider;

readonly class SliderService
{
    public function __construct(
        private SliderRepository $repository,
        private LanguageRepository $languageRepository,
    ) {}

    public function createSlider(CreateSliderDTO $dto): CreateSlider
    {
        $this->assertValidLanguageCodes($dto->title());

        $payload = CreateSlider::createFromArray($dto->toArray());

        return $this->repository->create($payload);
    }

    public function updateSlider(UpdateSliderDTO $dto): void
    {
        $this->assertValidLanguageCodes($dto->title());

        $payload = UpdateSlider::createFromArray($dto->toArray());

        if (! $this->repository->findByUuid($payload->id())) {
            throw new DomainException('Slider not found.');
        }

        $this->repository->update($payload);
    }

    public function deleteSlider(string $uuid): void
    {
        if (! $this->repository->findByUuid($uuid)) {
            throw new DomainException('Slider not found.');
        }

        $this->repository->delete($uuid);
    }

    /** @param array<string, string>|null ...$payloads */
    private function assertValidLanguageCodes(?array ...$payloads): void
    {
        foreach ($payloads as $payload) {
            if ($payload === null) {
                continue;
            }

            foreach (array_keys($payload) as $code) {
                if (! $this->languageRepository->codeExists($code)) {
                    throw new DomainException("Invalid language code: \"{$code}\".");
                }
            }
        }
    }
}
