<?php

declare(strict_types=1);

namespace Tests\Unit\References\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\References\Domain\Entity\ReferenceEntity;

class ReferenceEntityTest extends TestCase
{
    /** @test */
    #[Test]
    public function getters_return_correct_values(): void
    {
        // GIVEN
        $id = Uuid::uuid7()->toString();

        // WHEN
        $entity = new ReferenceEntity(id: $id, name: 'Acme Corp', order: 2);

        // THEN
        $this->assertSame($id, $entity->id());
        $this->assertSame('Acme Corp', $entity->name());
        $this->assertSame(2, $entity->order());
        $this->assertSame([], $entity->images());
    }

    /** @test */
    #[Test]
    public function images_default_to_empty_array(): void
    {
        $entity = new ReferenceEntity(id: Uuid::uuid7()->toString(), name: 'Test', order: 0);

        $this->assertIsArray($entity->images());
        $this->assertEmpty($entity->images());
    }
}
