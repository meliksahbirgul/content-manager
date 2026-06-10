<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Sliders\Domain\Entity\SliderEntity;
use Source\Sliders\Domain\Enums\SliderStatus;

class SliderEntityTest extends TestCase
{
    /** @test */
    #[Test]
    public function getters_return_correct_values(): void
    {
        // GIVEN
        $id = Uuid::uuid7()->toString();
        $title = ['en' => 'Welcome', 'tr' => 'Hoşgeldiniz'];
        $href = ['en' => 'https://example.com/en', 'tr' => 'https://example.com/tr'];

        // WHEN
        $entity = new SliderEntity(
            id: $id,
            title: $title,
            href: $href,
            order: 3,
            isActive: SliderStatus::Active,
        );

        // THEN
        $this->assertSame($id, $entity->id());
        $this->assertSame($title, $entity->title());
        $this->assertSame($href, $entity->href());
        $this->assertSame(3, $entity->order());
        $this->assertSame(SliderStatus::Active, $entity->isActive());
        $this->assertSame([], $entity->images());
    }

    /** @test */
    #[Test]
    public function images_default_to_empty_array(): void
    {
        $entity = new SliderEntity(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Test'],
            href: ['en' => 'https://example.com'],
            order: 0,
            isActive: SliderStatus::Passive,
        );

        $this->assertIsArray($entity->images());
        $this->assertEmpty($entity->images());
    }
}
