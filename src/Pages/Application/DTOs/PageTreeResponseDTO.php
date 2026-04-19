<?php

declare(strict_types=1);

namespace Source\Pages\Application\DTOs;

readonly class PageTreeResponseDTO
{
    /**
     * @param array<string, string> $title
     * @param self[] $children
     */
    public function __construct(
        private string $id,
        private array $title,
        private string $status,
        private int $order,
        private array $children = [],
    ) {}

    /** 
     * @param array<string, mixed> $data
     * @param self[] $children
     */
    public static function createFromArray(array $data, array $children = []): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'],
            status: $data['status'],
            order: $data['order'],
            children: $children
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    /** @return array<string,mixed> */
    public function title(): array
    {
        return $this->title;
    }

    public function status(): string
    {
        return $this->status;
    }

    /** @return array<string,mixed> */
    public function children(): array
    {
        return $this->children;
    }

    public function order(): int
    {
        return $this->order;
    }
}
