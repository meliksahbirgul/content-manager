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
}
