<?php

declare(strict_types=1);

namespace Tests\Unit\References\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\References\Application\DTOs\CreateReferenceDTO;

class CreateReferenceDTOTest extends TestCase
{
    /** @test */
    #[Test]
    public function from_request_maps_all_fields(): void
    {
        $dto = CreateReferenceDTO::fromRequest(['name' => 'Acme Corp', 'order' => 3]);

        $this->assertSame('Acme Corp', $dto->name());
        $this->assertSame(3, $dto->order());
    }

    /** @test */
    #[Test]
    public function from_request_defaults_order_to_zero(): void
    {
        $dto = CreateReferenceDTO::fromRequest(['name' => 'Acme Corp']);

        $this->assertSame(0, $dto->order());
    }

    /** @test */
    #[Test]
    public function to_array_contains_all_keys(): void
    {
        $dto = CreateReferenceDTO::fromRequest(['name' => 'Test', 'order' => 1]);
        $array = $dto->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('order', $array);
        $this->assertSame('Test', $array['name']);
    }
}
