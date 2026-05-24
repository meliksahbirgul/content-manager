<?php

declare(strict_types=1);

namespace Source\Dashboard\Domain\Entity;

use DateTimeImmutable;

class ActivityLogEntity
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function __construct(
        private int $id,
        private ?string $logName,
        private string $description,
        private ?string $event,
        private array $properties,
        private ?int $causerId,
        private DateTimeImmutable $createdAt,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function logName(): ?string
    {
        return $this->logName;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function event(): ?string
    {
        return $this->event;
    }

    /** @return array<string, mixed> */
    public function properties(): array
    {
        return $this->properties;
    }

    public function causerId(): ?int
    {
        return $this->causerId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
