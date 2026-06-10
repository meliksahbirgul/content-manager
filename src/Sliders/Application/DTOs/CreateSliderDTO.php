<?php

declare(strict_types=1);

namespace Source\Sliders\Application\DTOs;

use Source\Sliders\Domain\Enums\SliderStatus;

readonly class CreateSliderDTO
{
    /**
     * @param  array<string, string>  $title
     */
    public function __construct(
        private array $title,
        private string $href,
        private int $order,
        private string $status,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'],
            href: $data['href'],
            order: (int) ($data['order'] ?? 0),
            status: $data['status'] ?? SliderStatus::Active->value,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'title'  => $this->title,
            'href'   => $this->href,
            'order'  => $this->order,
            'status' => $this->status,
        ];
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

    public function status(): string
    {
        return $this->status;
    }
}
