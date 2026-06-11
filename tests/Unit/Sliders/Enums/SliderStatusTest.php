<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\Enums;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Sliders\Domain\Enums\SliderStatus;

class SliderStatusTest extends TestCase
{
    /** @test */
    #[Test]
    public function from_string_returns_active(): void
    {
        $this->assertSame(SliderStatus::Active, SliderStatus::fromString('active'));
    }

    /** @test */
    #[Test]
    public function from_string_returns_passive(): void
    {
        $this->assertSame(SliderStatus::Passive, SliderStatus::fromString('passive'));
    }

    /** @test */
    #[Test]
    public function enum_values_are_correct(): void
    {
        $this->assertSame('active', SliderStatus::Active->value);
        $this->assertSame('passive', SliderStatus::Passive->value);
    }

    /** @test */
    #[Test]
    public function from_string_throws_on_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid slider status: unknown');

        SliderStatus::fromString('unknown');
    }
}
