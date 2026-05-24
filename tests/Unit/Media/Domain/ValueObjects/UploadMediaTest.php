<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Domain\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;
use Source\Media\Domain\ValueObjects\UploadMedia;

class UploadMediaTest extends TestCase
{
    private string $uuid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uuid = Uuid::uuid7()->toString();
    }

    private function make(array $overrides = []): UploadMedia
    {
        return new UploadMedia(
            uuid: $overrides['uuid'] ?? $this->uuid,
            mediableType: $overrides['mediableType'] ?? 'Source\\Pages\\Domain\\Models\\Page',
            mediableId: $overrides['mediableId'] ?? 1,
            collection: $overrides['collection'] ?? MediaCollection::Images,
            disk: $overrides['disk'] ?? MediaDisk::Public,
            path: $overrides['path'] ?? 'page/1/images/'.$this->uuid.'.jpg',
            url: $overrides['url'] ?? '/storage/page/1/images/'.$this->uuid.'.jpg',
            originalName: $overrides['originalName'] ?? 'photo.jpg',
            mimeType: $overrides['mimeType'] ?? 'image/jpeg',
            size: $overrides['size'] ?? 1024,
            altText: $overrides['altText'] ?? null,
            linkPageUuid: $overrides['linkPageUuid'] ?? null,
            order: $overrides['order'] ?? 0,
        );
    }

    /** @test */
    #[Test]
    public function should_construct_with_valid_data(): void
    {
        $vo = $this->make();

        $this->assertSame($this->uuid, $vo->uuid());
        $this->assertSame(1, $vo->mediableId());
        $this->assertSame(MediaCollection::Images, $vo->collection());
        $this->assertSame(MediaDisk::Public, $vo->disk());
        $this->assertSame(1024, $vo->size());
        $this->assertNull($vo->altText());
        $this->assertNull($vo->linkPageUuid());
        $this->assertSame(0, $vo->order());
    }

    /** @test */
    #[Test]
    public function should_throw_on_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->make(['uuid' => 'not-a-uuid']);
    }

    /** @test */
    #[Test]
    public function should_throw_when_size_is_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->make(['size' => 0]);
    }

    /** @test */
    #[Test]
    public function should_throw_when_size_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->make(['size' => -1]);
    }

    /** @test */
    #[Test]
    public function should_throw_on_invalid_link_page_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->make(['linkPageUuid' => 'not-a-uuid']);
    }

    /** @test */
    #[Test]
    public function should_accept_valid_link_page_uuid(): void
    {
        $linkUuid = Uuid::uuid7()->toString();
        $vo = $this->make(['linkPageUuid' => $linkUuid]);

        $this->assertSame($linkUuid, $vo->linkPageUuid());
    }

    /** @test */
    #[Test]
    public function should_return_all_getters(): void
    {
        $linkUuid = Uuid::uuid7()->toString();
        $vo = $this->make([
            'altText' => 'Alt text here',
            'linkPageUuid' => $linkUuid,
            'order' => 5,
        ]);

        $this->assertSame('Source\\Pages\\Domain\\Models\\Page', $vo->mediableType());
        $this->assertSame('photo.jpg', $vo->originalName());
        $this->assertSame('image/jpeg', $vo->mimeType());
        $this->assertSame('Alt text here', $vo->altText());
        $this->assertSame(5, $vo->order());
    }
}
