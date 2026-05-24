<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;
use Source\Media\Domain\Models\Media;
use Source\Media\Infrastructure\Persistence\EloquentMediaRepository;
use Source\Pages\Domain\Models\Page;
use Tests\TestCase;

#[Group('infrastructure')]
class LinkSlugResolutionTest extends TestCase
{
    use RefreshDatabase;

    private EloquentMediaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentMediaRepository;
    }

    #[Test]
    public function find_for_model_resolves_link_slugs_for_linked_page(): void
    {
        // GIVEN: A page that media links to, and an owner page with a media row
        $linkedPage = $this->createPage(['en' => 'products', 'tr' => 'urunlerimiz']);
        $ownerPage = $this->createPage(['en' => 'home']);
        $this->createMedia($ownerPage->id, linkPageUuid: $linkedPage->uuid);

        // WHEN: Loading media for the owner page
        $results = $this->repository->findForModel(Page::class, $ownerPage->id, MediaCollection::Images);

        // THEN: link_slugs should be resolved from the linked page
        $this->assertCount(1, $results);
        $this->assertEquals(['en' => 'products', 'tr' => 'urunlerimiz'], $results[0]->linkSlugs());
        $this->assertEquals($linkedPage->uuid, $results[0]->linkPageUuid());
    }

    #[Test]
    public function find_for_model_returns_null_link_slugs_when_no_link_page_uuid(): void
    {
        // GIVEN: A page with a media row that has no link
        $ownerPage = $this->createPage(['en' => 'home']);
        $this->createMedia($ownerPage->id, linkPageUuid: null);

        // WHEN: Loading media for the owner page
        $results = $this->repository->findForModel(Page::class, $ownerPage->id, MediaCollection::Images);

        // THEN: link_slugs and link_page_uuid should both be null
        $this->assertCount(1, $results);
        $this->assertNull($results[0]->linkSlugs());
        $this->assertNull($results[0]->linkPageUuid());
    }

    #[Test]
    public function find_for_model_returns_null_link_slugs_when_linked_page_is_soft_deleted(): void
    {
        // GIVEN: A soft-deleted linked page
        $linkedPage = $this->createPage(['en' => 'old-page']);
        $linkedPage->delete();

        $ownerPage = $this->createPage(['en' => 'home']);
        $this->createMedia($ownerPage->id, linkPageUuid: $linkedPage->uuid);

        // WHEN: Loading media for the owner page
        $results = $this->repository->findForModel(Page::class, $ownerPage->id, MediaCollection::Images);

        // THEN: link_page_uuid is preserved but link_slugs resolves to null
        $this->assertCount(1, $results);
        $this->assertNull($results[0]->linkSlugs());
        $this->assertEquals($linkedPage->uuid, $results[0]->linkPageUuid());
    }

    #[Test]
    public function find_for_model_batch_resolves_slugs_single_query(): void
    {
        // GIVEN: Multiple media rows each linking to different pages
        $pageA = $this->createPage(['en' => 'about']);
        $pageB = $this->createPage(['en' => 'contact']);
        $ownerPage = $this->createPage(['en' => 'home']);

        $this->createMedia($ownerPage->id, linkPageUuid: $pageA->uuid);
        $this->createMedia($ownerPage->id, linkPageUuid: $pageB->uuid);

        // WHEN: Loading media for the owner page
        $results = $this->repository->findForModel(Page::class, $ownerPage->id, MediaCollection::Images);

        // THEN: Both link_slugs should be resolved correctly
        $this->assertCount(2, $results);

        $slugsByLinkUuid = [];
        foreach ($results as $entity) {
            $slugsByLinkUuid[(string) $entity->linkPageUuid()] = $entity->linkSlugs();
        }

        $this->assertEquals(['en' => 'about'], $slugsByLinkUuid[$pageA->uuid]);
        $this->assertEquals(['en' => 'contact'], $slugsByLinkUuid[$pageB->uuid]);
    }

    #[Test]
    public function find_for_model_filters_collection_correctly(): void
    {
        // GIVEN: Two media rows on the same page in different collections
        $ownerPage = $this->createPage(['en' => 'home']);
        $this->createMedia($ownerPage->id, linkPageUuid: null, collection: MediaCollection::Images);
        $this->createMedia($ownerPage->id, linkPageUuid: null, collection: MediaCollection::Thumbnail);

        // WHEN: Loading only the Images collection
        $results = $this->repository->findForModel(Page::class, $ownerPage->id, MediaCollection::Images);

        // THEN: Only the Images row is returned
        $this->assertCount(1, $results);
        $this->assertEquals('images', $results[0]->collection()->value);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createPage(array $slug): Page
    {
        return Page::create([
            'uuid' => Uuid::uuid7()->toString(),
            'title' => ['en' => 'Test Page'],
            'content' => ['en' => ''],
            'slug' => $slug,
            'order' => 0,
            'is_active' => 'active',
        ]);
    }

    private function createMedia(
        int $mediableId,
        ?string $linkPageUuid,
        MediaCollection $collection = MediaCollection::Images,
    ): Media {
        return Media::create([
            'uuid' => Uuid::uuid7()->toString(),
            'mediable_type' => Page::class,
            'mediable_id' => $mediableId,
            'collection' => $collection->value,
            'disk' => MediaDisk::Public->value,
            'path' => 'page/'.$mediableId.'/images/test.jpg',
            'url' => '/storage/page/'.$mediableId.'/images/test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'link_page_uuid' => $linkPageUuid,
            'order' => 0,
        ]);
    }
}
