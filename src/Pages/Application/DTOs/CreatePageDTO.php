<?php

declare(strict_types=1);

namespace Source\Pages\Application\DTOs;

use Source\Pages\Domain\Enums\PageStatus;

readonly class CreatePageDTO
{
    /** @param array<string,string> $title
     * @param array<string,string> $content
     * @param array<string,string> $slug
     */
    public function __construct(
        private array $title,
        private array $content,
        private array $slug,
        private string|null $parentId = null,
        private int $order = 0,
        private string $isActive = 'passive'
    ) {}

    /** @param array<string,mixed> $data */
    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            slug: $data['slug'],
            parentId: $data['parentId'] ?? null,
            order: (int) ($data['order'] ?? 0),
            isActive: ($data['isActive'] ?? PageStatus::PASSIVE->value),
        );
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'parentId' => $this->parentId,
            'order' => $this->order,
            'isActive' => $this->isActive,
        ];
    }
}
