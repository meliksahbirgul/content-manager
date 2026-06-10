<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Sliders\Domain\Enums\SliderStatus;
use Source\Sliders\Domain\ValueObjects\CreateSlider;

class CreateSliderTest extends TestCase
{
    private string $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->id = Uuid::uuid7()->toString();
    }

    /** @test */
    #[Test]
    public function creates_with_valid_data(): void
    {
        // GIVEN / WHEN
        $slider = new CreateSlider(
            id: $this->id,
            title: ['en' => 'Banner', 'tr' => 'Afiş'],
            href: ['en' => 'https://example.com/en', 'tr' => 'https://example.com/tr'],
            order: 2,
            status: SliderStatus::Active,
        );

        // THEN
        $this->assertSame($this->id, $slider->id());
        $this->assertSame(['en' => 'Banner', 'tr' => 'Afiş'], $slider->title());
        $this->assertSame(['en' => 'https://example.com/en', 'tr' => 'https://example.com/tr'], $slider->href());
        $this->assertSame(2, $slider->order());
        $this->assertSame(SliderStatus::Active, $slider->status());
    }

    /** @test */
    #[Test]
    public function create_from_array_generates_uuid_when_absent(): void
    {
        // WHEN
        $slider = CreateSlider::createFromArray([
            'title' => ['en' => 'Test'],
            'href'  => ['en' => 'https://example.com'],
        ]);

        // THEN
        $this->assertTrue(Uuid::isValid($slider->id()));
    }

    /** @test */
    #[Test]
    public function create_from_array_defaults_order_and_status(): void
    {
        $slider = CreateSlider::createFromArray([
            'title' => ['en' => 'Test'],
            'href'  => ['en' => 'https://example.com'],
        ]);

        $this->assertSame(0, $slider->order());
        $this->assertSame(SliderStatus::Active, $slider->status());
    }

    /** @test */
    #[Test]
    public function throws_on_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format for id.');

        new CreateSlider(
            id: 'not-a-uuid',
            title: ['en' => 'Test'],
            href: ['en' => 'https://example.com'],
            order: 0,
            status: SliderStatus::Active,
        );
    }

    /** @test */
    #[Test]
    public function throws_on_empty_title(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot be empty.');

        new CreateSlider(
            id: $this->id,
            title: [],
            href: ['en' => 'https://example.com'],
            order: 0,
            status: SliderStatus::Active,
        );
    }

    /** @test */
    #[Test]
    public function throws_on_empty_href(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Href cannot be empty.');

        new CreateSlider(
            id: $this->id,
            title: ['en' => 'Test'],
            href: [],
            order: 0,
            status: SliderStatus::Active,
        );
    }
}
