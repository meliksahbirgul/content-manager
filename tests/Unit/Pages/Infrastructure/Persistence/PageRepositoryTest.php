<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\Infrastructure\Persistence;

use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Source\Pages\Domain\Entity\PageEntity;
use Source\Pages\Domain\Enums\PageStatus;
use Source\Pages\Domain\Models\Page as EloquentPage;
use Source\Pages\Domain\ValueObjects\CreatePage;
use Source\Pages\Domain\ValueObjects\UpdatePage;
use Source\Pages\Infrastructure\Persistence\PageRepository;
use Tests\TestCase;

#[Group('infrastructure')]
class PageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PageRepository;
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    private function makeCreatePage(array $overrides = []): CreatePage
    {
        return new CreatePage(
            id: $overrides['id'] ?? Uuid::uuid7()->toString(),
            title: $overrides['title'] ?? ['en' => 'Test Page', 'tr' => 'Test Sayfa'],
            content: $overrides['content'] ?? ['en' => 'Content', 'tr' => 'İçerik'],
            slug: $overrides['slug'] ?? ['en' => 'test-page', 'tr' => 'test-sayfa'],
            parentId: $overrides['parentId'] ?? null,
            parentOriginalId: $overrides['parentOriginalId'] ?? null,
            order: $overrides['order'] ?? 0,
            status: $overrides['status'] ?? PageStatus::PASSIVE,
        );
    }

    private function createPageInDb(array $overrides = []): CreatePage
    {
        $payload = $this->makeCreatePage($overrides);

        return $this->repository->create($payload);
    }

    // ─── create() ────────────────────────────────────────────────────

    /** @test */
    #[Test]
    public function it_creates_a_page_successfully(): void
    {
        // Arrange
        $uuid = Uuid::uuid7()->toString();
        $payload = $this->makeCreatePage(['id' => $uuid]);

        // Act
        $result = $this->repository->create($payload);

        // Assert
        $this->assertSame($payload, $result);
        $this->assertDatabaseHas('pages', ['uuid' => $uuid]);
    }

    /** @test */
    #[Test]
    public function it_persists_all_fields_on_create(): void
    {
        // Arrange
        $uuid = Uuid::uuid7()->toString();
        $payload = $this->makeCreatePage([
            'id' => $uuid,
            'title' => ['en' => 'My Title'],
            'content' => ['en' => 'My Content'],
            'slug' => ['en' => 'my-title'],
            'order' => 5,
            'status' => PageStatus::ACTIVE,
        ]);

        // Act
        $this->repository->create($payload);

        // Assert
        $model = EloquentPage::where('uuid', $uuid)->first();
        $this->assertNotNull($model);
        $this->assertEquals(['en' => 'My Title'], $model->title);
        $this->assertEquals(['en' => 'My Content'], $model->content);
        $this->assertEquals(['en' => 'my-title'], $model->slug);
        $this->assertEquals(5, $model->order);
        $this->assertEquals('active', $model->is_active);
    }

    /** @test */
    #[Test]
    public function it_creates_page_with_parent_id(): void
    {
        // Arrange – create parent first
        $parent = $this->createPageInDb(['slug' => ['en' => 'parent-page']]);
        $parentOriginalId = $this->repository->findOriginalIdByUuid($parent->id());

        $childUuid = Uuid::uuid7()->toString();
        $child = $this->makeCreatePage([
            'id' => $childUuid,
            'slug' => ['en' => 'child-page'],
            'parentId' => $parent->id(),
            'parentOriginalId' => $parentOriginalId,
        ]);

        // Act
        $this->repository->create($child);

        // Assert
        $model = EloquentPage::where('uuid', $childUuid)->first();
        $this->assertNotNull($model);
        $this->assertEquals($parentOriginalId, $model->parent_id);
    }

    /** @test */
    #[Test]
    public function it_creates_page_with_default_passive_status(): void
    {
        // Arrange & Act
        $page = $this->createPageInDb();

        // Assert
        $model = EloquentPage::where('uuid', $page->id())->first();
        $this->assertEquals('passive', $model->is_active);
    }

    // ─── findByUuid() ────────────────────────────────────────────────

    /** @test */
    #[Test]
    public function it_finds_by_uuid(): void
    {
        // Arrange
        $page = $this->createPageInDb();

        // Act
        $entity = $this->repository->findByUuid($page->id());

        // Assert
        $this->assertNotNull($entity);
        $this->assertInstanceOf(PageEntity::class, $entity);
        $this->assertEquals($page->id(), $entity->id());
    }

    /** @test */
    #[Test]
    public function it_returns_null_when_uuid_not_found(): void
    {
        // Act
        $entity = $this->repository->findByUuid(Uuid::uuid7()->toString());

        // Assert
        $this->assertNull($entity);
    }

    /** @test */
    #[Test]
    public function it_maps_all_fields_to_page_entity(): void
    {
        // Arrange
        $page = $this->createPageInDb([
            'title' => ['en' => 'Mapped Title'],
            'content' => ['en' => 'Mapped Content'],
            'slug' => ['en' => 'mapped-slug'],
            'order' => 3,
            'status' => PageStatus::ACTIVE,
        ]);

        // Act
        $entity = $this->repository->findByUuid($page->id());

        // Assert
        $this->assertEquals(['en' => 'Mapped Title'], $entity->title());
        $this->assertEquals(['en' => 'Mapped Content'], $entity->content());
        $this->assertEquals(['en' => 'mapped-slug'], $entity->slug());
        $this->assertEquals(3, $entity->order());
        $this->assertEquals(PageStatus::ACTIVE, $entity->status());
        $this->assertNull($entity->parentId());
    }

    /** @test */
    #[Test]
    public function it_maps_parent_uuid_on_find(): void
    {
        // Arrange
        $parent = $this->createPageInDb(['slug' => ['en' => 'parent']]);
        $parentOriginalId = $this->repository->findOriginalIdByUuid($parent->id());

        $child = $this->createPageInDb([
            'slug' => ['en' => 'child'],
            'parentId' => $parent->id(),
            'parentOriginalId' => $parentOriginalId,
        ]);

        // Act
        $entity = $this->repository->findByUuid($child->id());

        // Assert
        $this->assertEquals($parent->id(), $entity->parentId());
    }

    // ─── isSlugUnique() ──────────────────────────────────────────────

    /** @test */
    #[Test]
    public function it_returns_true_when_slug_is_unique(): void
    {
        // Act
        $result = $this->repository->isSlugUnique(['en' => 'unique-slug']);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    #[Test]
    public function it_returns_false_when_slug_already_exists(): void
    {
        // Arrange
        $this->createPageInDb(['slug' => ['en' => 'existing-slug']]);

        // Act
        $result = $this->repository->isSlugUnique(['en' => 'existing-slug']);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    #[Test]
    public function it_returns_true_when_slug_belongs_to_same_page(): void
    {
        // Arrange
        $page = $this->createPageInDb(['slug' => ['en' => 'my-slug']]);

        // Act – checking uniqueness excluding the page's own UUID
        $result = $this->repository->isSlugUnique(['en' => 'my-slug'], $page->id());

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    #[Test]
    public function it_returns_false_when_slug_belongs_to_different_page(): void
    {
        // Arrange
        $this->createPageInDb(['slug' => ['en' => 'taken-slug']]);
        $otherUuid = Uuid::uuid7()->toString();

        // Act – different page trying to use the same slug
        $result = $this->repository->isSlugUnique(['en' => 'taken-slug'], $otherUuid);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    #[Test]
    public function it_checks_multiple_language_slugs_for_uniqueness(): void
    {
        // Arrange
        $this->createPageInDb(['slug' => ['en' => 'hello', 'tr' => 'merhaba']]);

        // Act – only the Turkish slug collides
        $result = $this->repository->isSlugUnique(['en' => 'different', 'tr' => 'merhaba']);

        // Assert
        $this->assertFalse($result);
    }

    // ─── updatePage() ────────────────────────────────────────────────

    /** @test */
    #[Test]
    public function it_updates_page_title(): void
    {
        // Arrange
        $page = $this->createPageInDb();
        $update = new UpdatePage(
            id: $page->id(),
            title: ['en' => 'Updated Title'],
        );

        // Act
        $this->repository->updatePage($update);

        // Assert
        $entity = $this->repository->findByUuid($page->id());
        $this->assertEquals(['en' => 'Updated Title'], $entity->title());
    }

    /** @test */
    #[Test]
    public function it_updates_page_content(): void
    {
        // Arrange
        $page = $this->createPageInDb();
        $update = new UpdatePage(
            id: $page->id(),
            content: ['en' => 'Updated Content'],
        );

        // Act
        $this->repository->updatePage($update);

        // Assert
        $entity = $this->repository->findByUuid($page->id());
        $this->assertEquals(['en' => 'Updated Content'], $entity->content());
    }

    /** @test */
    #[Test]
    public function it_updates_page_slug(): void
    {
        // Arrange
        $page = $this->createPageInDb();
        $update = new UpdatePage(
            id: $page->id(),
            slug: ['en' => 'updated-slug'],
        );

        // Act
        $this->repository->updatePage($update);

        // Assert
        $entity = $this->repository->findByUuid($page->id());
        $this->assertEquals(['en' => 'updated-slug'], $entity->slug());
    }

    /** @test */
    #[Test]
    public function it_updates_page_order(): void
    {
        // Arrange
        $page = $this->createPageInDb();
        $update = new UpdatePage(
            id: $page->id(),
            order: 10,
        );

        // Act
        $this->repository->updatePage($update);

        // Assert
        $entity = $this->repository->findByUuid($page->id());
        $this->assertEquals(10, $entity->order());
    }

    /** @test */
    #[Test]
    public function it_updates_page_status(): void
    {
        // Arrange
        $page = $this->createPageInDb();
        $update = new UpdatePage(
            id: $page->id(),
            status: PageStatus::ACTIVE,
        );

        // Act
        $this->repository->updatePage($update);

        // Assert
        $entity = $this->repository->findByUuid($page->id());
        $this->assertEquals(PageStatus::ACTIVE, $entity->status());
    }

    /** @test */
    #[Test]
    public function it_updates_multiple_fields_at_once(): void
    {
        // Arrange
        $page = $this->createPageInDb();
        $update = new UpdatePage(
            id: $page->id(),
            title: ['en' => 'New Title'],
            content: ['en' => 'New Content'],
            slug: ['en' => 'new-slug'],
            order: 99,
            status: PageStatus::ACTIVE,
        );

        // Act
        $this->repository->updatePage($update);

        // Assert
        $entity = $this->repository->findByUuid($page->id());
        $this->assertEquals(['en' => 'New Title'], $entity->title());
        $this->assertEquals(['en' => 'New Content'], $entity->content());
        $this->assertEquals(['en' => 'new-slug'], $entity->slug());
        $this->assertEquals(99, $entity->order());
        $this->assertEquals(PageStatus::ACTIVE, $entity->status());
    }

    /** @test */
    #[Test]
    public function it_does_not_change_fields_not_included_in_update(): void
    {
        // Arrange
        $page = $this->createPageInDb([
            'title' => ['en' => 'Original Title'],
            'content' => ['en' => 'Original Content'],
            'slug' => ['en' => 'original-slug'],
            'order' => 1,
        ]);

        $update = new UpdatePage(
            id: $page->id(),
            title: ['en' => 'Changed Title'],
        );

        // Act
        $this->repository->updatePage($update);

        // Assert – only title should change
        $entity = $this->repository->findByUuid($page->id());
        $this->assertEquals(['en' => 'Changed Title'], $entity->title());
        $this->assertEquals(['en' => 'Original Content'], $entity->content());
        $this->assertEquals(['en' => 'original-slug'], $entity->slug());
        $this->assertEquals(1, $entity->order());
    }

    /** @test */
    #[Test]
    public function it_skips_update_when_no_fields_provided(): void
    {
        // Arrange
        $page = $this->createPageInDb([
            'title' => ['en' => 'Unchanged'],
        ]);
        $update = new UpdatePage(id: $page->id());

        // Act – should not throw, just return
        $this->repository->updatePage($update);

        // Assert
        $entity = $this->repository->findByUuid($page->id());
        $this->assertEquals(['en' => 'Unchanged'], $entity->title());
    }

    /** @test */
    #[Test]
    public function it_throws_exception_when_updating_nonexistent_page(): void
    {
        // Arrange
        $update = new UpdatePage(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Nope'],
        );

        // Assert
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Page not found.');

        // Act
        $this->repository->updatePage($update);
    }

    // ─── listPages() ─────────────────────────────────────────────────

    /** @test */
    #[Test]
    public function it_returns_empty_array_when_no_pages_exist(): void
    {
        // Act
        $result = $this->repository->listPages();

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function it_lists_all_pages(): void
    {
        // Arrange
        $page1 = $this->createPageInDb(['slug' => ['en' => 'page-1'], 'order' => 1]);
        $page2 = $this->createPageInDb(['slug' => ['en' => 'page-2'], 'order' => 2]);

        // Act
        $result = $this->repository->listPages();

        // Assert
        $this->assertCount(2, $result);
    }

    /** @test */
    #[Test]
    public function it_lists_pages_ordered_by_order_field(): void
    {
        // Arrange
        $page2 = $this->createPageInDb(['slug' => ['en' => 'second'], 'order' => 2]);
        $page1 = $this->createPageInDb(['slug' => ['en' => 'first'], 'order' => 1]);
        $page3 = $this->createPageInDb(['slug' => ['en' => 'third'], 'order' => 3]);

        // Act
        $result = $this->repository->listPages();

        // Assert
        $this->assertEquals($page1->id(), $result[0]['id']);
        $this->assertEquals($page2->id(), $result[1]['id']);
        $this->assertEquals($page3->id(), $result[2]['id']);
    }

    /** @test */
    #[Test]
    public function it_list_pages_maps_correct_structure(): void
    {
        // Arrange
        $page = $this->createPageInDb([
            'title' => ['en' => 'Listed Page'],
            'slug' => ['en' => 'listed'],
            'order' => 5,
            'status' => PageStatus::ACTIVE,
        ]);

        // Act
        $result = $this->repository->listPages();

        // Assert
        $this->assertCount(1, $result);
        $item = $result[0];
        $this->assertEquals($page->id(), $item['id']);
        $this->assertEquals(['en' => 'Listed Page'], $item['title']);
        $this->assertEquals('active', $item['status']);
        $this->assertEquals(5, $item['order']);
        $this->assertNull($item['parentId']);
    }

    /** @test */
    #[Test]
    public function it_list_pages_includes_parent_uuid(): void
    {
        // Arrange
        $parent = $this->createPageInDb(['slug' => ['en' => 'parent-list'], 'order' => 1]);
        $parentOriginalId = $this->repository->findOriginalIdByUuid($parent->id());
        $child = $this->createPageInDb([
            'slug' => ['en' => 'child-list'],
            'order' => 2,
            'parentId' => $parent->id(),
            'parentOriginalId' => $parentOriginalId,
        ]);

        // Act
        $result = $this->repository->listPages();

        // Assert
        $childItem = collect($result)->firstWhere('id', $child->id());
        $this->assertEquals($parent->id(), $childItem['parentId']);
    }

    // ─── findOriginalIdByUuid() ──────────────────────────────────────

    /** @test */
    #[Test]
    public function it_finds_original_id_by_uuid(): void
    {
        // Arrange
        $page = $this->createPageInDb();

        // Act
        $originalId = $this->repository->findOriginalIdByUuid($page->id());

        // Assert
        $this->assertIsInt($originalId);
        $model = EloquentPage::where('uuid', $page->id())->first();
        $this->assertEquals($model->id, $originalId);
    }

    /** @test */
    #[Test]
    public function it_returns_null_for_nonexistent_uuid(): void
    {
        // Act
        $result = $this->repository->findOriginalIdByUuid(Uuid::uuid7()->toString());

        // Assert
        $this->assertNull($result);
    }
}
