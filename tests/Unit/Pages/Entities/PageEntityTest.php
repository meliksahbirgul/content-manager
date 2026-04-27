<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Pages\Domain\Entity\PageEntity;
use Source\Pages\Domain\Enums\PageStatus;

class PageEntityTest extends TestCase
{
    private string $validUuid;

    private string $validParentUuid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validUuid = Uuid::uuid7()->toString();
        $this->validParentUuid = Uuid::uuid7()->toString();
    }

    /** @test */
    #[Test]
    public function shouldCreateInstanceWithRequiredParameters(): void
    {
        // GIVEN: Required parameters
        $id = $this->validUuid;
        $title = ['en' => 'Test Title'];
        $content = ['en' => 'Test Content'];
        $slug = ['en' => 'test-title'];
        $order = 0;
        $status = PageStatus::ACTIVE;

        // WHEN: Creating PageEntity
        $entity = new PageEntity(
            id: $id,
            title: $title,
            content: $content,
            slug: $slug,
            order: $order,
            status: $status,
        );

        // THEN: Should create instance successfully
        $this->assertInstanceOf(PageEntity::class, $entity);
        $this->assertEquals($id, $entity->id());
        $this->assertEquals($title, $entity->title());
        $this->assertEquals($content, $entity->content());
        $this->assertEquals($slug, $entity->slug());
        $this->assertEquals($order, $entity->order());
        $this->assertEquals($status, $entity->status());
    }

    /** @test */
    #[Test]
    public function shouldCreateInstanceWithAllParameters(): void
    {
        // GIVEN: All parameters including optional ones
        $id = $this->validUuid;
        $title = ['en' => 'Test Title'];
        $content = ['en' => 'Test Content'];
        $slug = ['en' => 'test-title'];
        $order = 5;
        $status = PageStatus::ACTIVE;
        $parentId = $this->validParentUuid;
        $metadata = ['color' => 'blue', 'featured' => true];

        // WHEN: Creating PageEntity with all parameters
        $entity = new PageEntity(
            id: $id,
            title: $title,
            content: $content,
            slug: $slug,
            order: $order,
            status: $status,
            parentId: $parentId,
            metadata: $metadata,
        );

        // THEN: Should create instance with all data
        $this->assertInstanceOf(PageEntity::class, $entity);
        $this->assertEquals($id, $entity->id());
        $this->assertEquals($title, $entity->title());
        $this->assertEquals($content, $entity->content());
        $this->assertEquals($slug, $entity->slug());
        $this->assertEquals($order, $entity->order());
        $this->assertEquals($status, $entity->status());
        $this->assertEquals($parentId, $entity->parentId());
        $this->assertEquals($metadata, $entity->metadata());
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectId(): void
    {
        // GIVEN: PageEntity with specific id
        $id = $this->validUuid;
        $entity = new PageEntity(
            id: $id,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // WHEN: Calling id()
        $result = $entity->id();

        // THEN: Should return correct id
        $this->assertEquals($id, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectTitle(): void
    {
        // GIVEN: PageEntity with specific title
        $title = ['en' => 'English Title', 'tr' => 'Türkçe Başlık'];
        $entity = new PageEntity(
            id: $this->validUuid,
            title: $title,
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // WHEN: Calling title()
        $result = $entity->title();

        // THEN: Should return correct title array
        $this->assertEquals($title, $result);
        $this->assertIsArray($result);
        $this->assertEquals('English Title', $result['en']);
        $this->assertEquals('Türkçe Başlık', $result['tr']);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectContent(): void
    {
        // GIVEN: PageEntity with specific content
        $content = ['en' => 'English Content', 'tr' => 'Türkçe İçerik'];
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: $content,
            slug: ['en' => 'test-title'],
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // WHEN: Calling content()
        $result = $entity->content();

        // THEN: Should return correct content array
        $this->assertEquals($content, $result);
        $this->assertIsArray($result);
        $this->assertEquals('English Content', $result['en']);
        $this->assertEquals('Türkçe İçerik', $result['tr']);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectSlug(): void
    {
        // GIVEN: PageEntity with specific slug
        $slug = ['en' => 'english-title', 'tr' => 'turkce-basligi'];
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: $slug,
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // WHEN: Calling slug()
        $result = $entity->slug();

        // THEN: Should return correct slug array
        $this->assertEquals($slug, $result);
        $this->assertIsArray($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectParentId(): void
    {
        // GIVEN: PageEntity with specific parentId
        $parentId = $this->validParentUuid;
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 0,
            status: PageStatus::PASSIVE,
            parentId: $parentId,
        );

        // WHEN: Calling parentId()
        $result = $entity->parentId();

        // THEN: Should return correct parentId
        $this->assertEquals($parentId, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnNullParentIdWhenNotProvided(): void
    {
        // GIVEN: PageEntity without parentId
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // WHEN: Calling parentId()
        $result = $entity->parentId();

        // THEN: Should return null
        $this->assertNull($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectOrder(): void
    {
        // GIVEN: PageEntity with specific order
        $order = 10;
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: $order,
            status: PageStatus::PASSIVE,
        );

        // WHEN: Calling order()
        $result = $entity->order();

        // THEN: Should return correct order
        $this->assertEquals($order, $result);
        $this->assertIsInt($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectStatus(): void
    {
        // GIVEN: PageEntity with specific status
        $status = PageStatus::ACTIVE;
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 0,
            status: $status,
        );

        // WHEN: Calling status()
        $result = $entity->status();

        // THEN: Should return correct status
        $this->assertEquals($status, $result);
        $this->assertInstanceOf(PageStatus::class, $result);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectMetadata(): void
    {
        // GIVEN: PageEntity with specific metadata
        $metadata = [
            'color' => 'blue',
            'featured' => true,
            'views' => 100,
            'tags' => ['important', 'featured'],
        ];
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 0,
            status: PageStatus::PASSIVE,
            metadata: $metadata,
        );

        // WHEN: Calling metadata()
        $result = $entity->metadata();

        // THEN: Should return correct metadata
        $this->assertEquals($metadata, $result);
        $this->assertIsArray($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnNullMetadataWhenNotProvided(): void
    {
        // GIVEN: PageEntity without metadata
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // WHEN: Calling metadata()
        $result = $entity->metadata();

        // THEN: Should return null
        $this->assertNull($result);
    }

    /** @test */
    #[Test]
    public function shouldSupportMultilingualData(): void
    {
        // GIVEN: PageEntity with multilingual content
        $title = [
            'en' => 'English Title',
            'tr' => 'Türkçe Başlık',
            'es' => 'Título en Español',
            'fr' => 'Titre en Français',
        ];
        $content = [
            'en' => 'English Content',
            'tr' => 'Türkçe İçerik',
            'es' => 'Contenido en Español',
            'fr' => 'Contenu en Français',
        ];
        $slug = [
            'en' => 'english-title',
            'tr' => 'turkce-basligi',
            'es' => 'titulo-en-espanol',
            'fr' => 'titre-en-francais',
        ];

        // WHEN: Creating PageEntity with multilingual data
        $entity = new PageEntity(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // THEN: Should support all languages
        $this->assertEquals($title, $entity->title());
        $this->assertEquals($content, $entity->content());
        $this->assertEquals($slug, $entity->slug());
        $this->assertCount(4, $entity->title());
        $this->assertCount(4, $entity->content());
        $this->assertCount(4, $entity->slug());
    }

    /** @test */
    #[Test]
    public function shouldHandleVariousOrderValues(): void
    {
        // GIVEN: Various order values
        $orderValues = [0, 1, 10, 100, 999, PHP_INT_MAX];

        foreach ($orderValues as $order) {
            // WHEN: Creating PageEntity with different order
            $entity = new PageEntity(
                id: Uuid::uuid7()->toString(),
                title: ['en' => 'Test Title'],
                content: ['en' => 'Test Content'],
                slug: ['en' => 'test-title'],
                order: $order,
                status: PageStatus::PASSIVE,
            );

            // THEN: Should handle various order values
            $this->assertEquals($order, $entity->order());
        }
    }

    /** @test */
    #[Test]
    public function shouldHandleAllPageStatuses(): void
    {
        // GIVEN: All PageStatus enum values
        $statuses = [PageStatus::ACTIVE, PageStatus::PASSIVE];

        foreach ($statuses as $status) {
            // WHEN: Creating PageEntity with different status
            $entity = new PageEntity(
                id: Uuid::uuid7()->toString(),
                title: ['en' => 'Test Title'],
                content: ['en' => 'Test Content'],
                slug: ['en' => 'test-title'],
                order: 0,
                status: $status,
            );

            // THEN: Should handle all statuses
            $this->assertEquals($status, $entity->status());
        }
    }

    /** @test */
    #[Test]
    public function shouldPreserveComplexMetadata(): void
    {
        // GIVEN: Complex nested metadata
        $metadata = [
            'seo' => [
                'title' => 'SEO Title',
                'description' => 'SEO Description',
                'keywords' => ['keyword1', 'keyword2'],
            ],
            'social' => [
                'facebook' => 'facebook_id',
                'twitter' => 'twitter_id',
            ],
            'settings' => [
                'commentable' => true,
                'shareable' => false,
                'views' => 1250,
            ],
        ];

        // WHEN: Creating PageEntity with complex metadata
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 0,
            status: PageStatus::PASSIVE,
            metadata: $metadata,
        );

        // THEN: Should preserve complex metadata structure
        $result = $entity->metadata();
        $this->assertEquals($metadata, $result);
        $this->assertEquals('SEO Title', $result['seo']['title']);
        $this->assertTrue($result['settings']['commentable']);
        $this->assertFalse($result['settings']['shareable']);
    }

    /** @test */
    #[Test]
    public function shouldCreateMultipleIndependentInstances(): void
    {
        // GIVEN: Two different PageEntity instances
        $entity1 = new PageEntity(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Title One'],
            content: ['en' => 'Content One'],
            slug: ['en' => 'slug-one'],
            order: 1,
            status: PageStatus::ACTIVE,
            parentId: null,
            metadata: ['type' => 'page1'],
        );

        $entity2 = new PageEntity(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Title Two'],
            content: ['en' => 'Content Two'],
            slug: ['en' => 'slug-two'],
            order: 2,
            status: PageStatus::PASSIVE,
            parentId: $this->validParentUuid,
            metadata: ['type' => 'page2'],
        );

        // WHEN: Accessing their data
        $id1 = $entity1->id();
        $title1 = $entity1->title();
        $id2 = $entity2->id();
        $title2 = $entity2->title();

        // THEN: Each instance should have independent data
        $this->assertNotEquals($id1, $id2);
        $this->assertNotEquals($title1, $title2);
        $this->assertEquals('Title One', $title1['en']);
        $this->assertEquals('Title Two', $title2['en']);
        $this->assertEquals(PageStatus::ACTIVE, $entity1->status());
        $this->assertEquals(PageStatus::PASSIVE, $entity2->status());
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyStringArrays(): void
    {
        // GIVEN: PageEntity with empty language entries
        $title = ['en' => ''];
        $content = ['en' => ''];
        $slug = ['en' => ''];

        // WHEN: Creating PageEntity
        $entity = new PageEntity(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // THEN: Should preserve empty strings
        $this->assertEquals('', $entity->title()['en']);
        $this->assertEquals('', $entity->content()['en']);
        $this->assertEquals('', $entity->slug()['en']);
    }

    /** @test */
    #[Test]
    public function shouldHandleSpecialCharactersInData(): void
    {
        // GIVEN: Data with special characters
        $title = ['en' => 'Title with Special Characters: @#$%^&*()'];
        $content = ['en' => 'Content with <html> & "quoted" text'];
        $slug = ['en' => 'slug-with-special-chars'];

        // WHEN: Creating PageEntity
        $entity = new PageEntity(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // THEN: Should preserve special characters
        $this->assertEquals($title['en'], $entity->title()['en']);
        $this->assertEquals($content['en'], $entity->content()['en']);
        $this->assertEquals($slug['en'], $entity->slug()['en']);
    }

    /** @test */
    #[Test]
    public function shouldHandleLongTextContent(): void
    {
        // GIVEN: Long text content
        $longText = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 100);
        $title = ['en' => 'Long Title'];
        $content = ['en' => $longText];
        $slug = ['en' => 'long-title'];

        // WHEN: Creating PageEntity
        $entity = new PageEntity(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // THEN: Should handle long content
        $this->assertStringContainsString('Lorem ipsum', $entity->content()['en']);
        $this->assertGreaterThan(5000, strlen($entity->content()['en']));
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectTypeForAllProperties(): void
    {
        // GIVEN: PageEntity instance
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 5,
            status: PageStatus::ACTIVE,
            parentId: $this->validParentUuid,
            metadata: ['key' => 'value'],
        );

        // THEN: All properties should have correct types
        $this->assertIsString($entity->id());
        $this->assertIsArray($entity->title());
        $this->assertIsArray($entity->content());
        $this->assertIsArray($entity->slug());
        $this->assertIsString($entity->parentId());
        $this->assertIsInt($entity->order());
        $this->assertInstanceOf(PageStatus::class, $entity->status());
        $this->assertIsArray($entity->metadata());
    }

    /** @test */
    #[Test]
    public function shouldBeImmutableAfterConstruction(): void
    {
        // GIVEN: PageEntity instance
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 0,
            status: PageStatus::PASSIVE,
        );

        // VERIFY: Initial values
        $initialId = $entity->id();
        $initialTitle = $entity->title();

        // THEN: Subsequent calls should return same values (class properties are private)
        $this->assertEquals($initialId, $entity->id());
        $this->assertEquals($initialTitle, $entity->title());
        $this->assertSame($initialId, $entity->id());
    }

    /** @test */
    #[Test]
    public function shouldHandleNullableParentIdAndMetadata(): void
    {
        // GIVEN: PageEntity with explicitly null optional parameters
        $entity = new PageEntity(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            order: 0,
            status: PageStatus::PASSIVE,
            parentId: null,
            metadata: null,
        );

        // THEN: Optional parameters should be null
        $this->assertNull($entity->parentId());
        $this->assertNull($entity->metadata());
    }
}
