<?php

declare(strict_types=1);

namespace Source\Sliders\Domain\Enums;

use InvalidArgumentException;

enum SliderStatus: string
{
    case Active = 'active';
    case Passive = 'passive';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'active'  => self::Active,
            'passive' => self::Passive,
            default   => throw new InvalidArgumentException("Invalid slider status: $value"),
        };
    }
}
