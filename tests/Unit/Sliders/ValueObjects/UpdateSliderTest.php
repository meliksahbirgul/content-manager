<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Sliders\Domain\Enums\SliderStatus;
use Source\Sliders\Domain\ValueObjects\UpdateSlider;

class UpdateSliderTest extends TestCase
{
    private string $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->id = Uuid::uuid7()->toString();
    }

    /** @test */
    #[Test]
    public function creates_with_id_only_all_fields_nullable(): void
    {
        $slider = new UpdateSlider(id: $this->id);

        $this->assertSame($this->id, $slider->id());
        $this->assertNull($slider->title());
        $this->assertNull($slider->href());
        $this->assertNull($slider->order());
        $this->assertNull($slider->status());
    }

    /** @test */
    #[Test]
    public function create_from_array_maps_all_fields(): void
    {
        $slider = UpdateSlider::createFromArray([
            'id'     => $this->id,
            'title'  => ['en' => 'New Title'],
            'href'   => ['en' => 'https://example.com/new'],
            'order'  => 5,
            'status' => 'passive',
        ]);

        $this->assertSame($this->id, $slider->id());
        $this->assertSame(['en' => 'New Title'], $slider->title());
        $this->assertSame(['en' => 'https://example.com/new'], $slider->href());
        $this->assertSame(5, $slider->order());
        $this->assertSame(SliderStatus::Passive, $slider->status());
    }

    /** @test */
    #[Test]
    public function create_from_array_leaves_fields_null_when_absent(): void
    {
        $slider = UpdateSlider::createFromArray(['id' => $this->id]);

        $this->assertNull($slider->title());
        $this->assertNull($slider->href());
        $this->assertNull($slider->order());
        $this->assertNull($slider->status());
    }

    /** @test */
    #[Test]
    public function throws_on_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format for id.');

        new UpdateSlider(id: 'bad-uuid');
    }

    /** @test */
    #[Test]
    public function create_from_array_throws_when_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "id" field is required.');

        UpdateSlider::createFromArray(['title' => ['en' => 'No ID']]);
    }
}
