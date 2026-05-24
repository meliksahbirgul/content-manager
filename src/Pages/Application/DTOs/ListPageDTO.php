<?php

declare(strict_types=1);

namespace Source\Pages\Application\DTOs;

use Source\Pages\Domain\Enums\PageStatus;

readonly class ListPageDTO
{
    public function __construct(
        private ?string $search,
        private ?PageStatus $status,
    ) {}

    /** @param array<string,mixed> $data */
    public static function fromRequest(array $data): self
    {
        $status = null;
        if (array_key_exists('status', $data) && $data['status'] !== null && $data['status'] !== '') {
            $status = PageStatus::tryFrom($data['status']);
        }

        return new self(
            search: $data['search'] ?? null,
            status: $status,
        );
    }

    public function search(): ?string
    {
        return $this->search;
    }

    public function status(): ?PageStatus
    {
        return $this->status;
    }
}
