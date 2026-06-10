<?php

declare(strict_types=1);

namespace Tests\Unit\References\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\References\Application\DTOs\UpdateReferenceDTO;

class UpdateReferenceDTOTest extends TestCase
{
    /** @test */
    #[Test]
    public function from_request_maps_all_fields(): void
    {
        $id = Uuid::uuid7()->toString();
        $dto = UpdateReferenceDTO::fromRequest(['id' => $id, 'name' => 'New Corp', 'order' => 5]);

        $this->assertSame($id, $dto->id());
    }

    /** @test */
    #[Test]
    public function from_request_leaves_optional_fields_null(): void
    {
        $id = Uuid::uuid7()->toString();
        $dto = UpdateReferenceDTO::fromRequest(['id' => $id]);

        $this->assertNull($dto->toArray()['name']);
        $this->assertNull($dto->toArray()['order']);
    }

    /** @test */
    #[Test]
    public function to_array_includes_id(): void
    {
        $id = Uuid::uuid7()->toString();
        $dto = UpdateReferenceDTO::fromRequest(['id' => $id]);

        $this->assertSame($id, $dto->toArray()['id']);
    }
}
