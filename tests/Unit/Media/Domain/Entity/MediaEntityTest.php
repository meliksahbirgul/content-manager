<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Domain\Entity;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Media\Domain\Entity\MediaEntity;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;

class MediaEntityTest extends TestCase
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
            mediableType: $overrides['mediableType'] ?? 'Source\\Pages\\Domain\\Models\\Page',
            mediableId: $overrides['mediableId'] ?? 1,
            collection: $overrides['collection'] ?? MediaCollection::Images,
            disk: $overrides['disk'] ?? MediaDisk::Public,
            path: $overrides['path'] ?? 'page/1/images/'.$this->uuid.'.jpg',
            url: $overrides['url'] ?? '/storage/page/1/images/'.$this->uuid.'.jpg',
            originalName: $overrides['originalName'] ?? 'photo.jpg',
            mimeType: $overrides['mimeType'] ?? 'image/jpeg',
            size: $overrides['size'] ?? 102400,
            altText: $overrides['altText'] ?? null,
            linkPageUuid: $overrides['linkPageUuid'] ?? null,
            linkSlugs: $overrides['linkSlugs'] ?? null,
            order: $overrides['order'] ?? 0,
        );
    }

    /** @test */
    #[Test]
    public function should_return_all_getters(): void
    {
        $linkUuid = Uuid::uuid7()->toString();
        $entity = $this->makeEntity([
            'altText' => 'A photo',
            'linkPageUuid' => $linkUuid,
            'linkSlugs' => ['en' => 'products', 'tr' => 'urunler'],
            'order' => 3,
        ]);

        $this->assertSame($this->uuid, $entity->uuid());
        $this->assertSame('Source\\Pages\\Domain\\Models\\Page', $entity->mediableType());
        $this->assertSame(1, $entity->mediableId());
        $this->assertSame(MediaCollection::Images, $entity->collection());
        $this->assertSame(MediaDisk::Public, $entity->disk());
        $this->assertStringContainsString($this->uuid, $entity->path());
        $this->assertStringContainsString($this->uuid, $entity->url());
        $this->assertSame('photo.jpg', $entity->originalName());
        $this->assertSame('image/jpeg', $entity->mimeType());
        $this->assertSame(102400, $entity->size());
        $this->assertSame('A photo', $entity->altText());
        $this->assertSame($linkUuid, $entity->linkPageUuid());
        $this->assertSame(['en' => 'products', 'tr' => 'urunler'], $entity->linkSlugs());
        $this->assertSame(3, $entity->order());
    }

    /** @test */
    #[Test]
    public function should_return_nulls_for_optional_fields(): void
    {
        $entity = $this->makeEntity();

        $this->assertNull($entity->altText());
        $this->assertNull($entity->linkPageUuid());
        $this->assertNull($entity->linkSlugs());
    }

    /** @test */
    #[Test]
    public function with_link_slugs_should_return_new_instance_with_slugs(): void
    {
        $entity = $this->makeEntity();
        $slugs = ['en' => 'home', 'tr' => 'anasayfa'];

        $updated = $entity->withLinkSlugs($slugs);

        $this->assertNotSame($entity, $updated);
        $this->assertNull($entity->linkSlugs());
        $this->assertSame($slugs, $updated->linkSlugs());
        $this->assertSame($entity->uuid(), $updated->uuid());
    }

    /** @test */
    #[Test]
    public function with_link_slugs_should_accept_null(): void
    {
        $entity = $this->makeEntity(['linkSlugs' => ['en' => 'old']]);

        $updated = $entity->withLinkSlugs(null);

        $this->assertNull($updated->linkSlugs());
    }

    /** @test */
    #[Test]
    public function should_support_all_collection_values(): void
    {
        foreach (MediaCollection::cases() as $collection) {
            $entity = $this->makeEntity(['collection' => $collection]);
            $this->assertSame($collection, $entity->collection());
        }
    }

    /** @test */
    #[Test]
    public function should_support_all_disk_values(): void
    {
        foreach (MediaDisk::cases() as $disk) {
            $entity = $this->makeEntity(['disk' => $disk]);
            $this->assertSame($disk, $entity->disk());
        }
    }
}
