<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Sliders\Application\DTOs\CreateSliderDTO;
use Source\Sliders\Domain\Enums\SliderStatus;

class CreateSliderDTOTest extends TestCase
{
    /** @test */
    #[Test]
    public function from_request_maps_all_fields(): void
    {
        $dto = CreateSliderDTO::fromRequest([
            'title'  => ['en' => 'Banner', 'tr' => 'Afiş'],
            'href'   => ['en' => 'https://example.com/en', 'tr' => 'https://example.com/tr'],
            'order'  => 2,
            'status' => 'passive',
        ]);

        $this->assertSame(['en' => 'Banner', 'tr' => 'Afiş'], $dto->title());
        $this->assertSame(['en' => 'https://example.com/en', 'tr' => 'https://example.com/tr'], $dto->href());
        $this->assertSame(2, $dto->order());
        $this->assertSame('passive', $dto->status());
    }

    /** @test */
    #[Test]
    public function from_request_defaults_order_and_status(): void
    {
        $dto = CreateSliderDTO::fromRequest([
            'title' => ['en' => 'Test'],
            'href'  => ['en' => 'https://example.com'],
        ]);

        $this->assertSame(0, $dto->order());
        $this->assertSame(SliderStatus::Active->value, $dto->status());
    }

    /** @test */
    #[Test]
    public function to_array_contains_all_keys(): void
    {
        $dto = CreateSliderDTO::fromRequest([
            'title'  => ['en' => 'Test'],
            'href'   => ['en' => 'https://example.com'],
            'order'  => 1,
            'status' => 'active',
        ]);

        $array = $dto->toArray();

        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('href', $array);
        $this->assertArrayHasKey('order', $array);
        $this->assertArrayHasKey('status', $array);
    }
}
