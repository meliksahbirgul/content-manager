<?php

declare(strict_types=1);

namespace Source\Pages\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Source\Pages\Domain\Enums\PageStatus;

readonly class CreatePage
{
    /**
     * @param array<string,string> $title
     * @param array<string,string> $content
     * @param array<string,string> $slug
     */
    public function __construct(
        private string $id,
        private array $title,
        private array $content,
        private array $slug,
        private string|null $parentId = null,
        private int $order = 0,
        private PageStatus $isActive = PageStatus::PASSIVE,
    ) {
        $this->validate();
    }

    /** @param array<string,mixed> $data */
    public static function createFromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? Uuid::uuid7()->toString(),
            title: $data['title'],
            content: $data['content'],
            slug: $data['slug'],
            parentId: $data['parentId'] ?? null,
        );
    }

    private function validate(): void
    {
        if (empty($this->title) || empty($this->content) || empty($this->slug)) {
            throw new InvalidArgumentException('Title cannot be empty.');
        }

        if (! Uuid::isValid($this->id)) {
            throw new InvalidArgumentException('Invalid UUID format for id.');
        }

        if ($this->parentId !== null && ! Uuid::isValid($this->parentId)) {
            throw new InvalidArgumentException('Invalid UUID format for parentId.');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    /** @return array<string,string> */
    public function title(): array
    {
        return $this->title;
    }

    /** @return array<string,string> */
    public function content(): array
    {
        return $this->content;
    }

    /** @return array<string,string> */
    public function slug(): array
    {
        return $this->slug;
    }

    public function parentId(): string|null
    {
        return $this->parentId;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function isActive(): PageStatus
    {
        return $this->isActive;
    }
}
