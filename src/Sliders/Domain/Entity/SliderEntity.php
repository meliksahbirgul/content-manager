<?php

declare(strict_types=1);

namespace Source\Sliders\Domain\Entity;

use Source\Media\Domain\Entity\MediaEntity;
use Source\Sliders\Domain\Enums\SliderStatus;

class SliderEntity
{
    /**
     * @param  array<string, string>  $title
     * @param  list<MediaEntity>  $images
     */
    public function __construct(
        private string $id,
        private array $title,
        private string $href,
        private int $order,
        private SliderStatus $isActive,
        private array $images = [],
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    /** @return array<string, string> */
    public function title(): array
    {
        return $this->title;
    }

    public function href(): string
    {
        return $this->href;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function isActive(): SliderStatus
    {
        return $this->isActive;
    }

    /** @return list<MediaEntity> */
    public function images(): array
    {
        return $this->images;
    }
}
