<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Sliders\Application\DTOs\SliderResponseDTO;

class SliderResponseDTOTest extends TestCase
{
    private function makeData(): array
    {
        return [
            'id'       => Uuid::uuid7()->toString(),
            'title'    => ['en' => 'Hero', 'tr' => 'Kahraman'],
            'href'     => ['en' => 'https://example.com/en', 'tr' => 'https://example.com/tr'],
            'order'    => 1,
            'isActive' => 'active',
        ];
    }

    /** @test */
    #[Test]
    public function create_from_array_maps_fields(): void
    {
        $data = $this->makeData();
        $dto = SliderResponseDTO::createFromArray($data);

        $this->assertSame($data['id'], $dto->id());
        $this->assertSame($data['title'], $dto->title());
        $this->assertSame($data['href'], $dto->href());
        $this->assertSame($data['order'], $dto->order());
        $this->assertSame($data['isActive'], $dto->isActive());
        $this->assertSame([], $dto->images());
    }

    /** @test */
    #[Test]
    public function json_serialize_returns_all_keys(): void
    {
        $dto = SliderResponseDTO::createFromArray($this->makeData());
        $json = $dto->jsonSerialize();

        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('title', $json);
        $this->assertArrayHasKey('href', $json);
        $this->assertArrayHasKey('order', $json);
        $this->assertArrayHasKey('isActive', $json);
        $this->assertArrayHasKey('images', $json);
    }
}
