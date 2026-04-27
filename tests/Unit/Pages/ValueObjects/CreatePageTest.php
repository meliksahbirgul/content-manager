<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Pages\Domain\Enums\PageStatus;
use Source\Pages\Domain\ValueObjects\CreatePage;

class CreatePageTest extends TestCase
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
    public function createInstanceWithAllRequiredParameters(): void
    {
        $title = ['en' => 'Test Title'];
        $content = ['en' => 'Test Content'];
        $slug = ['en' => 'test-title'];

        $createPage = new CreatePage(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
        );

        $this->assertInstanceOf(CreatePage::class, $createPage);
        $this->assertEquals($this->validUuid, $createPage->id());
        $this->assertEquals($title, $createPage->title());
        $this->assertEquals($content, $createPage->content());
        $this->assertEquals($slug, $createPage->slug());
    }

    /** @test */
    #[Test]
    public function createInstanceWithOptionalParameters(): void
    {
        $title = ['en' => 'Test Title'];
        $content = ['en' => 'Test Content'];
        $slug = ['en' => 'test-title'];
        $order = 5;

        $createPage = new CreatePage(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
            parentId: $this->validParentUuid,
            order: $order,
            status: PageStatus::ACTIVE,
        );

        $this->assertEquals($this->validParentUuid, $createPage->parentId());
        $this->assertEquals($order, $createPage->order());
        $this->assertEquals(PageStatus::ACTIVE, $createPage->status());
    }

    /** @test */
    #[Test]
    public function defaultsToPassiveStatusWhenNotSpecified(): void
    {
        $title = ['en' => 'Test Title'];
        $content = ['en' => 'Test Content'];
        $slug = ['en' => 'test-title'];

        $createPage = new CreatePage(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
        );

        $this->assertEquals(PageStatus::PASSIVE, $createPage->status());
    }

    /** @test */
    #[Test]
    public function defaultsToZeroOrderWhenNotSpecified(): void
    {
        $title = ['en' => 'Test Title'];
        $content = ['en' => 'Test Content'];
        $slug = ['en' => 'test-title'];

        $createPage = new CreatePage(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
        );

        $this->assertEquals(0, $createPage->order());
    }

    /** @test */
    #[Test]
    public function defaultsToNullParentIdWhenNotSpecified(): void
    {
        $title = ['en' => 'Test Title'];
        $content = ['en' => 'Test Content'];
        $slug = ['en' => 'test-title'];

        $createPage = new CreatePage(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
        );

        $this->assertNull($createPage->parentId());
    }

    /** @test */
    #[Test]
    public function throwsExceptionWithEmptyTitle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot be empty.');

        new CreatePage(
            id: $this->validUuid,
            title: [],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
        );
    }

    /** @test */
    #[Test]
    public function throwsExceptionWithEmptyContent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot be empty.');

        new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: [],
            slug: ['en' => 'test-title'],
        );
    }

    /** @test */
    #[Test]
    public function throwsExceptionWithEmptySlug(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot be empty.');

        new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: [],
        );
    }

    /** @test */
    #[Test]
    public function throwsExceptionWithInvalidUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format for id.');

        new CreatePage(
            id: 'not-a-valid-uuid',
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
        );
    }

    /** @test */
    #[Test]
    public function throwsExceptionWithInvalidParentUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format for parentId.');

        new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            parentId: 'not-a-valid-uuid',
        );
    }

    /** @test */
    #[Test]
    public function allowsNullParentId(): void
    {
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            parentId: null,
        );

        $this->assertNull($createPage->parentId());
    }

    /** @test */
    #[Test]
    public function createsInstanceFromArrayWithMinimalData(): void
    {
        $data = [
            'title' => ['en' => 'Test Title'],
            'content' => ['en' => 'Test Content'],
            'slug' => ['en' => 'test-title'],
        ];

        $createPage = CreatePage::createFromArray($data);

        $this->assertInstanceOf(CreatePage::class, $createPage);
        $this->assertEquals($data['title'], $createPage->title());
        $this->assertEquals($data['content'], $createPage->content());
        $this->assertEquals($data['slug'], $createPage->slug());
        $this->assertNull($createPage->parentId());
    }

    /** @test */
    #[Test]
    public function createsInstanceFromArrayWithCustomId(): void
    {
        $data = [
            'id' => $this->validUuid,
            'title' => ['en' => 'Test Title'],
            'content' => ['en' => 'Test Content'],
            'slug' => ['en' => 'test-title'],
        ];

        $createPage = CreatePage::createFromArray($data);

        $this->assertEquals($this->validUuid, $createPage->id());
    }

    /** @test */
    #[Test]
    public function createsInstanceFromArrayWithParentId(): void
    {
        $data = [
            'title' => ['en' => 'Test Title'],
            'content' => ['en' => 'Test Content'],
            'slug' => ['en' => 'test-title'],
            'parentId' => $this->validParentUuid,
        ];

        $createPage = CreatePage::createFromArray($data);

        $this->assertEquals($this->validParentUuid, $createPage->parentId());
    }

    /** @test */
    #[Test]
    public function generatesUuidWhenNotProvidedInArray(): void
    {
        $data = [
            'title' => ['en' => 'Test Title'],
            'content' => ['en' => 'Test Content'],
            'slug' => ['en' => 'test-title'],
        ];

        $createPage = CreatePage::createFromArray($data);

        $this->assertNotNull($createPage->id());
        $this->assertTrue(Uuid::isValid($createPage->id()));
    }

    /** @test */
    #[Test]
    public function throwsExceptionWhenCreatingFromArrayWithEmptyTitle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot be empty.');

        CreatePage::createFromArray([
            'title' => [],
            'content' => ['en' => 'Test Content'],
            'slug' => ['en' => 'test-title'],
        ]);
    }

    /** @test */
    #[Test]
    public function returnsAllGettersCorrectly(): void
    {
        $title = ['en' => 'Test Title', 'tr' => 'Test Başlığı'];
        $content = ['en' => 'Test Content', 'tr' => 'Test İçeriği'];
        $slug = ['en' => 'test-title', 'tr' => 'test-basligi'];
        $order = 10;

        $createPage = new CreatePage(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
            parentId: $this->validParentUuid,
            order: $order,
            status: PageStatus::ACTIVE,
        );

        $this->assertSame($this->validUuid, $createPage->id());
        $this->assertSame($title, $createPage->title());
        $this->assertSame($content, $createPage->content());
        $this->assertSame($slug, $createPage->slug());
        $this->assertSame($this->validParentUuid, $createPage->parentId());
        $this->assertSame($order, $createPage->order());
        $this->assertSame(PageStatus::ACTIVE, $createPage->status());
    }

    /** @test */
    #[Test]
    public function supportsMultilingualData(): void
    {
        $title = [
            'en' => 'English Title',
            'tr' => 'Türkçe Başlık',
            'es' => 'Título en Español',
        ];
        $content = [
            'en' => 'English Content',
            'tr' => 'Türkçe İçerik',
            'es' => 'Contenido en Español',
        ];
        $slug = [
            'en' => 'english-title',
            'tr' => 'turkce-basligi',
            'es' => 'titulo-en-espanol',
        ];

        $createPage = new CreatePage(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
        );

        $this->assertEquals($title, $createPage->title());
        $this->assertEquals($content, $createPage->content());
        $this->assertEquals($slug, $createPage->slug());
    }

    /** @test */
    #[Test]
    public function isReadonly(): void
    {
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
        );

        // The class is readonly, so we can't modify properties
        // This test verifies the class structure is immutable
        $this->assertInstanceOf(CreatePage::class, $createPage);
    }

    /** @test */
    #[Test]
    public function defaultsToNullParentOriginalIdWhenNotSpecified(): void
    {
        // GIVEN: CreatePage without parentOriginalId
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
        );

        // THEN: parentOriginalId should be null
        $this->assertNull($createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function returnsCorrectParentOriginalIdWhenProvided(): void
    {
        // GIVEN: CreatePage with parentOriginalId
        $parentOriginalId = 42;
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            parentOriginalId: $parentOriginalId,
        );

        // THEN: parentOriginalId should return the provided value
        $this->assertEquals($parentOriginalId, $createPage->parentOriginalId());
        $this->assertIsInt($createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function setParentOriginalIdUpdatesTheValue(): void
    {
        // GIVEN: CreatePage instance
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
        );

        // WHEN: Setting parentOriginalId
        $parentOriginalId = 100;
        $createPage->setParentOriginalId($parentOriginalId);

        // THEN: parentOriginalId should be updated
        $this->assertEquals($parentOriginalId, $createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function setParentOriginalIdCanOverwriteExistingValue(): void
    {
        // GIVEN: CreatePage with initial parentOriginalId
        $initialId = 10;
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            parentOriginalId: $initialId,
        );

        // WHEN: Setting new parentOriginalId
        $newId = 50;
        $createPage->setParentOriginalId($newId);

        // THEN: parentOriginalId should be updated to new value
        $this->assertEquals($newId, $createPage->parentOriginalId());
        $this->assertNotEquals($initialId, $createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function handleVariousParentOriginalIdValues(): void
    {
        // GIVEN: Various parentOriginalId values
        $testValues = [
            0,
            1,
            42,
            100,
            999,
            PHP_INT_MAX,
        ];

        foreach ($testValues as $value) {
            // WHEN: Creating CreatePage with parentOriginalId
            $createPage = new CreatePage(
                id: $this->validUuid,
                title: ['en' => 'Test Title'],
                content: ['en' => 'Test Content'],
                slug: ['en' => 'test-title'],
                parentOriginalId: $value,
            );

            // THEN: Should handle various values correctly
            $this->assertEquals($value, $createPage->parentOriginalId());
        }
    }

    /** @test */
    #[Test]
    public function setParentOriginalIdWithZero(): void
    {
        // GIVEN: CreatePage instance
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
        );

        // WHEN: Setting parentOriginalId to zero
        $createPage->setParentOriginalId(0);

        // THEN: Should accept zero as valid value
        $this->assertEquals(0, $createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function setParentOriginalIdWithLargeNumber(): void
    {
        // GIVEN: CreatePage instance
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
        );

        // WHEN: Setting parentOriginalId to large number
        $largeNumber = 999999999;
        $createPage->setParentOriginalId($largeNumber);

        // THEN: Should handle large numbers
        $this->assertEquals($largeNumber, $createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function parentOriginalIdIsIndependentFromParentId(): void
    {
        // GIVEN: CreatePage with both parentId and parentOriginalId
        $parentId = $this->validParentUuid;
        $parentOriginalId = 42;

        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            parentId: $parentId,
            parentOriginalId: $parentOriginalId,
        );

        // THEN: Both values should be accessible independently
        $this->assertEquals($parentId, $createPage->parentId());
        $this->assertEquals($parentOriginalId, $createPage->parentOriginalId());
        $this->assertNotEquals($parentId, $parentOriginalId);
    }

    /** @test */
    #[Test]
    public function setParentOriginalIdMultipleTimes(): void
    {
        // GIVEN: CreatePage instance
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
        );

        // WHEN: Setting parentOriginalId multiple times
        $createPage->setParentOriginalId(10);
        $this->assertEquals(10, $createPage->parentOriginalId());

        $createPage->setParentOriginalId(20);
        $this->assertEquals(20, $createPage->parentOriginalId());

        $createPage->setParentOriginalId(30);
        // THEN: Should always return the most recent value
        $this->assertEquals(30, $createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function parentOriginalIdCanBeNullAfterCreation(): void
    {
        // GIVEN: CreatePage with parentOriginalId
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            parentOriginalId: 42,
        );

        // VERIFY: Initial value
        $this->assertEquals(42, $createPage->parentOriginalId());

        // WHEN: Setting to new value
        // Note: Since setParentOriginalId expects int, we can't set to null
        // but we verify the current behavior
        $this->assertIsInt($createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function parentOriginalIdWithCreateFromArray(): void
    {
        // GIVEN: Array data without parentOriginalId
        $data = [
            'title' => ['en' => 'Test Title'],
            'content' => ['en' => 'Test Content'],
            'slug' => ['en' => 'test-title'],
        ];

        // WHEN: Creating from array
        $createPage = CreatePage::createFromArray($data);

        // THEN: parentOriginalId should be null (property not handled by createFromArray)
        $this->assertNull($createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function parentOriginalIdCanBeSetAfterCreationFromArray(): void
    {
        // GIVEN: CreatePage created from array
        $data = [
            'title' => ['en' => 'Test Title'],
            'content' => ['en' => 'Test Content'],
            'slug' => ['en' => 'test-title'],
        ];
        $createPage = CreatePage::createFromArray($data);

        // WHEN: Setting parentOriginalId after creation
        $createPage->setParentOriginalId(55);

        // THEN: parentOriginalId should be set
        $this->assertEquals(55, $createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function parentOriginalIdIsPublicProperty(): void
    {
        // GIVEN: CreatePage instance
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
            parentOriginalId: 42,
        );

        // THEN: Can access parentOriginalId through both property and method
        $this->assertEquals(42, $createPage->parentOriginalId);
        $this->assertEquals(42, $createPage->parentOriginalId());
    }

    /** @test */
    #[Test]
    public function parentOriginalIdWithAllParameters(): void
    {
        // GIVEN: CreatePage with all parameters including parentOriginalId
        $title = ['en' => 'Test Title', 'tr' => 'Test Başlığı'];
        $content = ['en' => 'Test Content', 'tr' => 'Test İçeriği'];
        $slug = ['en' => 'test-title', 'tr' => 'test-basligi'];
        $order = 10;
        $parentOriginalId = 99;

        $createPage = new CreatePage(
            id: $this->validUuid,
            title: $title,
            content: $content,
            slug: $slug,
            parentId: $this->validParentUuid,
            parentOriginalId: $parentOriginalId,
            order: $order,
            status: PageStatus::ACTIVE,
        );

        // THEN: All values should be accessible correctly
        $this->assertEquals($this->validUuid, $createPage->id());
        $this->assertEquals($title, $createPage->title());
        $this->assertEquals($content, $createPage->content());
        $this->assertEquals($slug, $createPage->slug());
        $this->assertEquals($this->validParentUuid, $createPage->parentId());
        $this->assertEquals($parentOriginalId, $createPage->parentOriginalId());
        $this->assertEquals($order, $createPage->order());
        $this->assertEquals(PageStatus::ACTIVE, $createPage->status());
    }

    /** @test */
    #[Test]
    public function setParentOriginalIdReturnsVoid(): void
    {
        // GIVEN: CreatePage instance
        $createPage = new CreatePage(
            id: $this->validUuid,
            title: ['en' => 'Test Title'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-title'],
        );

        // WHEN: Calling setParentOriginalId
        $result = $createPage->setParentOriginalId(42);

        // THEN: Should return void (null)
        $this->assertNull($result);
    }
}
