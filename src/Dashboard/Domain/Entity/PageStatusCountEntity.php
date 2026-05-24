<?php

declare(strict_types=1);

namespace Source\Dashboard\Domain\Entity;

class PageStatusCountEntity
{
    public function __construct(
        private string $status,
        private int $count,
    ) {}

    public function status(): string
    {
        return $this->status;
    }

    public function count(): int
    {
        return $this->count;
    }
}
