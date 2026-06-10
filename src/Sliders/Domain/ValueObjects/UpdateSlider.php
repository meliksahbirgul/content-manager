<?php

declare(strict_types=1);

namespace Source\Sliders\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Source\Sliders\Domain\Enums\SliderStatus;

readonly class UpdateSlider
{
    /**
     * @param  array<string, string>|null  $title
     */
    public function __construct(
        private string $id,
        private ?array $title = null,
        private ?string $href = null,
        private ?int $order = null,
        private ?SliderStatus $status = null,
    ) {
        if (! Uuid::isValid($this->id)) {
            throw new InvalidArgumentException('Invalid UUID format for id.');
        }
    }

    /** @param array<string, mixed> $data */
    public static function createFromArray(array $data): self
    {
        if (! array_key_exists('id', $data)) {
            throw new InvalidArgumentException('The "id" field is required.');
        }

        return new self(
            id: $data['id'],
            title: $data['title'] ?? null,
            href: $data['href'] ?? null,
            order: $data['order'] ?? null,
            status: isset($data['status']) ? SliderStatus::from($data['status']) : null,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    /** @return array<string, string>|null */
    public function title(): ?array
    {
        return $this->title;
    }

    public function href(): ?string
    {
        return $this->href;
    }

    public function order(): ?int
    {
        return $this->order;
    }

    public function status(): ?SliderStatus
    {
        return $this->status;
    }
}
