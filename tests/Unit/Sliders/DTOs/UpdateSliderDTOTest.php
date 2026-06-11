<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Sliders\Application\DTOs\UpdateSliderDTO;

class UpdateSliderDTOTest extends TestCase
{
    /** @test */
    #[Test]
    public function from_request_maps_all_fields(): void
    {
        $id = Uuid::uuid7()->toString();

        $dto = UpdateSliderDTO::fromRequest([
            'id'     => $id,
            'title'  => ['en' => 'New Title'],
            'href'   => ['en' => 'https://example.com/new'],
            'order'  => 5,
            'status' => 'passive',
        ]);

        $this->assertSame($id, $dto->id());
        $this->assertSame(['en' => 'New Title'], $dto->title());
        $this->assertSame(['en' => 'https://example.com/new'], $dto->href());
    }

    /** @test */
    #[Test]
    public function from_request_leaves_optional_fields_null(): void
    {
        $id = Uuid::uuid7()->toString();
        $dto = UpdateSliderDTO::fromRequest(['id' => $id]);

        $this->assertNull($dto->title());
        $this->assertNull($dto->href());
    }

    /** @test */
    #[Test]
    public function to_array_includes_id(): void
    {
        $id = Uuid::uuid7()->toString();
        $dto = UpdateSliderDTO::fromRequest(['id' => $id]);

        $this->assertSame($id, $dto->toArray()['id']);
    }
}
