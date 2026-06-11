<?php

declare(strict_types=1);

namespace Source\Sliders\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Source\Sliders\Domain\Enums\SliderStatus;

class CreateSlider
{
    /**
     * @param  array<string, string>  $title
     * @param  array<string, string>  $href
     */
    public function __construct(
        private string $id,
        private array $title,
        private array $href,
        private int $order,
        private SliderStatus $status,
    ) {
        $this->validate();
    }

    /** @param array<string, mixed> $data */
    public static function createFromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? Uuid::uuid7()->toString(),
            title: $data['title'],
            href: $data['href'],
            order: $data['order'] ?? 0,
            status: isset($data['status']) ? SliderStatus::from($data['status']) : SliderStatus::Active,
        );
    }

    private function validate(): void
    {
        if (! Uuid::isValid($this->id)) {
            throw new InvalidArgumentException('Invalid UUID format for id.');
        }

        if (empty($this->title)) {
            throw new InvalidArgumentException('Title cannot be empty.');
        }

        if (empty($this->href)) {
            throw new InvalidArgumentException('Href cannot be empty.');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    /** @return array<string, string> */
    public function title(): array
    {
        return $this->title;
    }

    /** @return array<string, string> */
    public function href(): array
    {
        return $this->href;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function status(): SliderStatus
    {
        return $this->status;
    }
}
