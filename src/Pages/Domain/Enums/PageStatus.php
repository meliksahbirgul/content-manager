<?php

declare(strict_types=1);

namespace Source\Pages\Domain\Enums;

use InvalidArgumentException;

enum PageStatus: string
{
    case ACTIVE = 'active';
    case PASSIVE = 'passive';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'active' => self::ACTIVE,
            'passive' => self::PASSIVE,
            default => throw new InvalidArgumentException("Invalid page status: $value"),
        };
    }
}
