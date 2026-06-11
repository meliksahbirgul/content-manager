<?php

declare(strict_types=1);

namespace Tests\Unit\References\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\References\Application\DTOs\ReferenceResponseDTO;

class ReferenceResponseDTOTest extends TestCase
{
    private function makeData(): array
    {
        return [
            'id'    => Uuid::uuid7()->toString(),
            'name'  => 'Acme Corp',
            'order' => 2,
        ];
    }

    /** @test */
    #[Test]
    public function create_from_array_maps_fields(): void
    {
        $data = $this->makeData();
        $dto = ReferenceResponseDTO::createFromArray($data);

        $this->assertSame($data['id'], $dto->id());
        $this->assertSame($data['name'], $dto->name());
        $this->assertSame($data['order'], $dto->order());
        $this->assertSame([], $dto->images());
    }

    /** @test */
    #[Test]
    public function json_serialize_returns_all_keys(): void
    {
        $dto = ReferenceResponseDTO::createFromArray($this->makeData());
        $json = $dto->jsonSerialize();

        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('order', $json);
        $this->assertArrayHasKey('images', $json);
    }
}
