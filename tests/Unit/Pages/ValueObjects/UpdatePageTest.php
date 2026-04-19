<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\ValueObjects;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Pages\Domain\Enums\PageStatus;
use Source\Pages\Domain\ValueObjects\UpdatePage;

class UpdatePageTest extends TestCase
{
    #[Test]
    public function shouldCreateInstanceWithAllOptionalParameters(): void
    {
        $title = ['en' => 'Updated Title', 'tr' => 'Güncellenmiş Başlık'];
        $content = ['en' => 'Updated Content', 'tr' => 'Güncellenmiş İçerik'];
        $slug = ['en' => 'updated-title', 'tr' => 'guncellenmis-basligi'];
        $order = 5;
        $status = PageStatus::ACTIVE;

        $updatePage = new UpdatePage(
            title: $title,
            content: $content,
            slug: $slug,
            order: $order,
            status: $status,
        );

        $this->assertInstanceOf(UpdatePage::class, $updatePage);
        $this->assertEquals($title, $updatePage->title());
        $this->assertEquals($content, $updatePage->content());
        $this->assertEquals($slug, $updatePage->slug());
        $this->assertEquals($order, $updatePage->order());
        $this->assertEquals($status, $updatePage->status());
    }

    #[Test]
    public function shouldCreateInstanceWithOnlyTitle(): void
    {
        $title = ['en' => 'New Title'];

        $updatePage = new UpdatePage(title: $title);

        $this->assertEquals($title, $updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function shouldCreateInstanceWithOnlyContent(): void
    {
        $content = ['en' => 'New Content'];

        $updatePage = new UpdatePage(content: $content);

        $this->assertNull($updatePage->title());
        $this->assertEquals($content, $updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function shouldCreateInstanceWithOnlySlug(): void
    {
        $slug = ['en' => 'new-slug'];

        $updatePage = new UpdatePage(slug: $slug);

        $this->assertNull($updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertEquals($slug, $updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function shouldCreateInstanceWithOnlyOrder(): void
    {
        $order = 10;

        $updatePage = new UpdatePage(order: $order);

        $this->assertNull($updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertEquals($order, $updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function shouldCreateInstanceWithOnlyStatus(): void
    {
        $status = PageStatus::PASSIVE;

        $updatePage = new UpdatePage(status: $status);

        $this->assertNull($updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertEquals($status, $updatePage->status());
    }

    #[Test]
    public function shouldCreateEmptyInstance(): void
    {
        $updatePage = new UpdatePage();

        $this->assertNull($updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function shouldCreateInstanceWithMultipleFields(): void
    {
        $title = ['en' => 'Title', 'tr' => 'Başlık'];
        $content = ['en' => 'Content', 'tr' => 'İçerik'];
        $order = 3;

        $updatePage = new UpdatePage(
            title: $title,
            content: $content,
            order: $order,
        );

        $this->assertEquals($title, $updatePage->title());
        $this->assertEquals($content, $updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertEquals($order, $updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function shouldCreateFromArrayWithAllFields(): void
    {
        $data = [
            'title' => ['en' => 'Array Title'],
            'content' => ['en' => 'Array Content'],
            'slug' => ['en' => 'array-slug'],
            'order' => 7,
            'status' => PageStatus::ACTIVE->value,
        ];

        $updatePage = UpdatePage::createFromArray($data);

        $this->assertEquals($data['title'], $updatePage->title());
        $this->assertEquals($data['content'], $updatePage->content());
        $this->assertEquals($data['slug'], $updatePage->slug());
        $this->assertEquals($data['order'], $updatePage->order());
        $this->assertEquals(PageStatus::ACTIVE, $updatePage->status());
    }

    #[Test]
    public function shouldCreateFromArrayWithPartialFields(): void
    {
        $data = [
            'title' => ['en' => 'Only Title'],
            'order' => 2,
        ];

        $updatePage = UpdatePage::createFromArray($data);

        $this->assertEquals($data['title'], $updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertEquals($data['order'], $updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function shouldCreateFromEmptyArray(): void
    {
        $updatePage = UpdatePage::createFromArray([]);

        $this->assertNull($updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function shouldHandleMultilingualTitleData(): void
    {
        $title = [
            'en' => 'English Title',
            'tr' => 'Türkçe Başlık',
            'es' => 'Título en Español',
            'fr' => 'Titre en Français',
        ];

        $updatePage = new UpdatePage(title: $title);

        $this->assertEquals($title, $updatePage->title());
        $this->assertCount(4, $updatePage->title());
        $this->assertEquals('English Title', $updatePage->title()['en']);
        $this->assertEquals('Türkçe Başlık', $updatePage->title()['tr']);
    }

    #[Test]
    public function shouldHandleMultilingualContentData(): void
    {
        $content = [
            'en' => 'English Content',
            'tr' => 'Türkçe İçeriği',
            'es' => 'Contenido en Español',
        ];

        $updatePage = new UpdatePage(content: $content);

        $this->assertEquals($content, $updatePage->content());
        $this->assertCount(3, $updatePage->content());
        $this->assertEquals('Türkçe İçeriği', $updatePage->content()['tr']);
    }

    #[Test]
    public function shouldHandleMultilingualSlugData(): void
    {
        $slug = [
            'en' => 'english-slug',
            'tr' => 'turkce-slug',
            'es' => 'slug-espanol',
        ];

        $updatePage = new UpdatePage(slug: $slug);

        $this->assertEquals($slug, $updatePage->slug());
        $this->assertCount(3, $updatePage->slug());
    }

    #[Test]
    public function shouldHandleActivePageStatus(): void
    {
        $updatePage = new UpdatePage(status: PageStatus::ACTIVE);

        $this->assertEquals(PageStatus::ACTIVE, $updatePage->status());
        $this->assertTrue($updatePage->status() === PageStatus::ACTIVE);
    }

    #[Test]
    public function shouldHandlePassivePageStatus(): void
    {
        $updatePage = new UpdatePage(status: PageStatus::PASSIVE);

        $this->assertEquals(PageStatus::PASSIVE, $updatePage->status());
        $this->assertTrue($updatePage->status() === PageStatus::PASSIVE);
    }

    #[Test]
    public function shouldHandleVariousOrderValues(): void
    {
        $orders = [0, 1, 5, 10, 100, 999];

        foreach ($orders as $order) {
            $updatePage = new UpdatePage(order: $order);
            $this->assertEquals($order, $updatePage->order());
        }
    }

    #[Test]
    public function shouldReturnNullForUnsetOrder(): void
    {
        $updatePage = new UpdatePage(order: null);

        $this->assertNull($updatePage->order());
    }

    #[Test]
    public function shouldPreserveImmutability(): void
    {
        $title = ['en' => 'Original'];
        $updatePage = new UpdatePage(title: $title);

        $firstAccess = $updatePage->title();
        $secondAccess = $updatePage->title();

        $this->assertEquals($firstAccess, $secondAccess);
        $this->assertSame($firstAccess, $secondAccess);
    }

    #[Test]
    public function shouldCreateFromArrayWithActiveStatus(): void
    {
        $data = [
            'title' => ['en' => 'Title'],
            'status' => PageStatus::ACTIVE->value,
        ];

        $updatePage = UpdatePage::createFromArray($data);

        $this->assertEquals(PageStatus::ACTIVE, $updatePage->status());
    }

    #[Test]
    public function shouldCreateFromArrayWithPassiveStatus(): void
    {
        $data = [
            'title' => ['en' => 'Title'],
            'status' => PageStatus::PASSIVE->value,
        ];

        $updatePage = UpdatePage::createFromArray($data);

        $this->assertEquals(PageStatus::PASSIVE, $updatePage->status());
    }

    #[Test]
    public function shouldHandleComplexUpdateScenario(): void
    {
        $updateData = [
            'title' => ['en' => 'Updated Title', 'tr' => 'Güncellenmiş Başlık'],
            'slug' => ['en' => 'updated-slug', 'tr' => 'guncellenmis-slug'],
            'order' => 3,
            'status' => PageStatus::ACTIVE->value,
        ];

        $updatePage = UpdatePage::createFromArray($updateData);

        $this->assertCount(2, $updatePage->title());
        $this->assertCount(2, $updatePage->slug());
        $this->assertEquals(3, $updatePage->order());
        $this->assertEquals(PageStatus::ACTIVE, $updatePage->status());
        $this->assertNull($updatePage->content());
    }

    #[Test]
    public function shouldHandlePartialUpdateWithNullValues(): void
    {
        $updatePage = new UpdatePage(
            title: null,
            content: ['en' => 'Content'],
            slug: null,
            order: null,
            status: PageStatus::PASSIVE,
        );

        $this->assertNull($updatePage->title());
        $this->assertEquals(['en' => 'Content'], $updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertEquals(PageStatus::PASSIVE, $updatePage->status());
    }

    #[Test]
    public function shouldCreateFromArrayWithMissingOptionalFields(): void
    {
        $data = [
            'content' => ['en' => 'Only Content'],
        ];

        $updatePage = UpdatePage::createFromArray($data);

        $this->assertNull($updatePage->title());
        $this->assertEquals($data['content'], $updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function shouldHandleZeroOrder(): void
    {
        $updatePage = new UpdatePage(order: 0);

        $this->assertEquals(0, $updatePage->order());
        $this->assertIsInt($updatePage->order());
    }

    #[Test]
    public function shouldHandleSingleLanguageUpdate(): void
    {
        $updatePage = new UpdatePage(
            title: ['en' => 'Single Language'],
            slug: ['en' => 'single-language'],
        );

        $this->assertCount(1, $updatePage->title());
        $this->assertCount(1, $updatePage->slug());
        $this->assertEquals('Single Language', $updatePage->title()['en']);
    }
}
