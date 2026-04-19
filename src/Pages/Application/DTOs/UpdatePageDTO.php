<?php

declare(strict_types=1);

namespace Source\Pages\Application\DTOs;

readonly class UpdatePageDTO
{
    /** 
     * @param array<string, string>|null $title
     * @param array<string, string>|null $content
     * @param array<string, string>|null $slug
     */
    public function __construct(
        public readonly string $id,
        public readonly array|null $title = null,
        public readonly array|null $content = null,
        public readonly array|null $slug = null,
        private readonly int|null $order = null,
        private readonly string|null $status = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            slug: $data['slug'] ?? null,
            order: $data['order'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'order' => $this->order,
            'status' => $this->status,
        ];
    }
}
