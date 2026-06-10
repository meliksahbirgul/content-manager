<?php

declare(strict_types=1);

namespace Source\Sliders\Application\DTOs;

readonly class UpdateSliderDTO
{
    /**
     * @param  array<string, string>|null  $title
     */
    public function __construct(
        private string $id,
        private ?array $title = null,
        private ?string $href = null,
        private ?int $order = null,
        private ?string $status = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'] ?? null,
            href: $data['href'] ?? null,
            order: isset($data['order']) ? (int) $data['order'] : null,
            status: $data['status'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'title'  => $this->title,
            'href'   => $this->href,
            'order'  => $this->order,
            'status' => $this->status,
        ];
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
}
