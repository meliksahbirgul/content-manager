<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Application\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Media\Application\DTOs\MediaResponseDTO;
use Source\Media\Domain\Entity\MediaEntity;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;

class MediaResponseDTOTest extends TestCase
{
    private string $uuid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uuid = Uuid::uuid7()->toString();
    }

    private function makeEntity(array $overrides = []): MediaEntity
    {
        return new MediaEntity(
            uuid: $overrides['uuid'] ?? $this->uuid,
            mediableType: 'Source\\Pages\\Domain\\Models\\Page',
            mediableId: 1,
            collection: $overrides['collection'] ?? MediaCollection::Images,
            disk: MediaDisk::Public,
            path: 'page/1/images/'.$this->uuid.'.jpg',
            url: $overrides['url'] ?? '/storage/page/1/images/'.$this->uuid.'.jpg',
            originalName: 'photo.jpg',
            mimeType: 'image/jpeg',
            size: 2048,
            altText: $overrides['altText'] ?? null,
            linkPageUuid: $overrides['linkPageUuid'] ?? null,
            linkSlugs: $overrides['linkSlugs'] ?? null,
            order: $overrides['order'] ?? 0,
        );
    }

    /** @test */
    #[Test]
    public function should_map_from_entity_correctly(): void
    {
        $entity = $this->makeEntity(['altText' => 'Product image', 'order' => 2]);

        $dto = MediaResponseDTO::fromEntity($entity);

        $this->assertSame($this->uuid, $dto->uuid());
        $this->assertSame('/storage/page/1/images/'.$this->uuid.'.jpg', $dto->url());
        $this->assertSame('photo.jpg', $dto->originalName());
        $this->assertSame('image/jpeg', $dto->mimeType());
        $this->assertSame(2048, $dto->size());
        $this->assertSame('images', $dto->collection());
        $this->assertSame(2, $dto->order());
        $this->assertSame('Product image', $dto->altText());
        $this->assertNull($dto->linkPageUuid());
        $this->assertNull($dto->linkSlugs());
    }

    /** @test */
    #[Test]
    public function should_map_link_fields_from_entity(): void
    {
        $linkUuid = Uuid::uuid7()->toString();
        $entity = $this->makeEntity([
            'linkPageUuid' => $linkUuid,
            'linkSlugs' => ['en' => 'products', 'tr' => 'urunler'],
        ]);

        $dto = MediaResponseDTO::fromEntity($entity);

        $this->assertSame($linkUuid, $dto->linkPageUuid());
        $this->assertSame(['en' => 'products', 'tr' => 'urunler'], $dto->linkSlugs());
    }

    /** @test */
    #[Test]
    public function json_serialize_should_contain_all_required_keys(): void
    {
        $dto = MediaResponseDTO::fromEntity($this->makeEntity());
        $result = $dto->jsonSerialize();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('original_name', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('collection', $result);
        $this->assertArrayHasKey('order', $result);
        $this->assertArrayHasKey('alt_text', $result);
        $this->assertArrayHasKey('link_page_uuid', $result);
        $this->assertArrayHasKey('link_slugs', $result);
    }

    /** @test */
    #[Test]
    public function json_serialize_should_map_entity_values_correctly(): void
    {
        $linkUuid = Uuid::uuid7()->toString();
        $entity = $this->makeEntity([
            'altText' => 'My alt',
            'linkPageUuid' => $linkUuid,
            'linkSlugs' => ['en' => 'home'],
        ]);

        $result = MediaResponseDTO::fromEntity($entity)->jsonSerialize();

        $this->assertSame($this->uuid, $result['id']);
        $this->assertSame('My alt', $result['alt_text']);
        $this->assertSame($linkUuid, $result['link_page_uuid']);
        $this->assertSame(['en' => 'home'], $result['link_slugs']);
        $this->assertNull($result['link_slugs']['tr'] ?? null);
    }

    /** @test */
    #[Test]
    public function should_be_json_encodable(): void
    {
        $dto = MediaResponseDTO::fromEntity($this->makeEntity());
        $json = json_encode($dto);

        $this->assertNotFalse($json);
        $decoded = json_decode((string) $json, true);
        $this->assertIsArray($decoded);
        $this->assertSame($this->uuid, $decoded['id']);
    }
}
