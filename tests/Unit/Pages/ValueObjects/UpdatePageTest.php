<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Pages\Domain\Enums\PageStatus;
use Source\Pages\Domain\ValueObjects\UpdatePage;

class UpdatePageTest extends TestCase
{
    #[Test]
    public function should_create_instance_with_all_optional_parameters(): void
    {
        $id = Uuid::uuid7()->toString();
        $title = ['en' => 'Updated Title', 'tr' => 'Güncellenmiş Başlık'];
        $content = ['en' => 'Updated Content', 'tr' => 'Güncellenmiş İçerik'];
        $slug = ['en' => 'updated-title', 'tr' => 'guncellenmis-basligi'];
        $order = 5;
        $status = PageStatus::ACTIVE;

        $updatePage = new UpdatePage(
            id: $id,
            title: $title,
            content: $content,
            slug: $slug,
            order: $order,
            status: $status,
        );

        $this->assertInstanceOf(UpdatePage::class, $updatePage);
        $this->assertEquals($id, $updatePage->id());
        $this->assertEquals($title, $updatePage->title());
        $this->assertEquals($content, $updatePage->content());
        $this->assertEquals($slug, $updatePage->slug());
        $this->assertEquals($order, $updatePage->order());
        $this->assertEquals($status, $updatePage->status());
    }

    #[Test]
    public function should_create_instance_with_only_title(): void
    {
        $id = Uuid::uuid7()->toString();
        $title = ['en' => 'New Title'];

        $updatePage = new UpdatePage(id: $id, title: $title);

        $this->assertEquals($id, $updatePage->id());
        $this->assertEquals($title, $updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function should_create_instance_with_only_content(): void
    {
        $id = Uuid::uuid7()->toString();
        $content = ['en' => 'New Content'];

        $updatePage = new UpdatePage(id: $id, content: $content);

        $this->assertEquals($id, $updatePage->id());
        $this->assertNull($updatePage->title());
        $this->assertEquals($content, $updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function should_create_instance_with_only_slug(): void
    {
        $id = Uuid::uuid7()->toString();
        $slug = ['en' => 'new-slug'];

        $updatePage = new UpdatePage(id: $id, slug: $slug);

        $this->assertEquals($id, $updatePage->id());
        $this->assertNull($updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertEquals($slug, $updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function should_create_instance_with_only_order(): void
    {
        $id = Uuid::uuid7()->toString();
        $order = 10;

        $updatePage = new UpdatePage(id: $id, order: $order);

        $this->assertEquals($id, $updatePage->id());
        $this->assertNull($updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertEquals($order, $updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function should_create_instance_with_only_status(): void
    {
        $id = Uuid::uuid7()->toString();
        $status = PageStatus::PASSIVE;

        $updatePage = new UpdatePage(id: $id, status: $status);

        $this->assertEquals($id, $updatePage->id());
        $this->assertNull($updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertEquals($status, $updatePage->status());
    }

    #[Test]
    public function should_create_instance_with_only_id(): void
    {
        $id = Uuid::uuid7()->toString();

        $updatePage = new UpdatePage(id: $id);

        $this->assertEquals($id, $updatePage->id());
        $this->assertNull($updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function should_create_instance_with_multiple_fields(): void
    {
        $id = Uuid::uuid7()->toString();
        $title = ['en' => 'Title', 'tr' => 'Başlık'];
        $content = ['en' => 'Content', 'tr' => 'İçerik'];
        $order = 3;

        $updatePage = new UpdatePage(
            id: $id,
            title: $title,
            content: $content,
            order: $order,
        );

        $this->assertEquals($id, $updatePage->id());
        $this->assertEquals($title, $updatePage->title());
        $this->assertEquals($content, $updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertEquals($order, $updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function should_throw_exception_with_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format for id.');

        new UpdatePage(id: 'invalid-uuid');
    }

    #[Test]
    public function should_throw_exception_with_malformed_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format for id.');

        new UpdatePage(id: '123-456-789');
    }

    #[Test]
    public function should_throw_exception_with_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdatePage(id: '');
    }

    #[Test]
    public function should_create_from_array_with_all_fields(): void
    {
        $id = Uuid::uuid7()->toString();
        $data = [
            'id' => $id,
            'title' => ['en' => 'Array Title'],
            'content' => ['en' => 'Array Content'],
            'slug' => ['en' => 'array-slug'],
            'order' => 7,
            'status' => PageStatus::ACTIVE->value,
        ];

        $updatePage = UpdatePage::createFromArray($data);

        $this->assertEquals($id, $updatePage->id());
        $this->assertEquals($data['title'], $updatePage->title());
        $this->assertEquals($data['content'], $updatePage->content());
        $this->assertEquals($data['slug'], $updatePage->slug());
        $this->assertEquals($data['order'], $updatePage->order());
        $this->assertEquals(PageStatus::ACTIVE, $updatePage->status());
    }

    #[Test]
    public function should_create_from_array_with_partial_fields(): void
    {
        $id = Uuid::uuid7()->toString();
        $data = [
            'id' => $id,
            'title' => ['en' => 'Only Title'],
            'order' => 2,
        ];

        $updatePage = UpdatePage::createFromArray($data);

        $this->assertEquals($id, $updatePage->id());
        $this->assertEquals($data['title'], $updatePage->title());
        $this->assertNull($updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertEquals($data['order'], $updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function should_throw_exception_when_creating_from_array_without_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdatePage::createFromArray([
            'title' => ['en' => 'Title'],
        ]);
    }

    #[Test]
    public function should_throw_exception_when_creating_from_array_with_invalid_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format for id.');

        UpdatePage::createFromArray([
            'id' => 'invalid-id',
            'title' => ['en' => 'Title'],
        ]);
    }

    #[Test]
    public function should_handle_multilingual_title_data(): void
    {
        $id = Uuid::uuid7()->toString();
        $title = [
            'en' => 'English Title',
            'tr' => 'Türkçe Başlık',
            'es' => 'Título en Español',
            'fr' => 'Titre en Français',
        ];

        $updatePage = new UpdatePage(id: $id, title: $title);

        $this->assertEquals($title, $updatePage->title());
        $this->assertCount(4, $updatePage->title());
        $this->assertEquals('English Title', $updatePage->title()['en']);
        $this->assertEquals('Türkçe Başlık', $updatePage->title()['tr']);
    }

    #[Test]
    public function should_handle_multilingual_content_data(): void
    {
        $id = Uuid::uuid7()->toString();
        $content = [
            'en' => 'English Content',
            'tr' => 'Türkçe İçeriği',
            'es' => 'Contenido en Español',
        ];

        $updatePage = new UpdatePage(id: $id, content: $content);

        $this->assertEquals($content, $updatePage->content());
        $this->assertCount(3, $updatePage->content());
        $this->assertEquals('Türkçe İçeriği', $updatePage->content()['tr']);
    }

    #[Test]
    public function should_handle_multilingual_slug_data(): void
    {
        $id = Uuid::uuid7()->toString();
        $slug = [
            'en' => 'english-slug',
            'tr' => 'turkce-slug',
            'es' => 'slug-espanol',
        ];

        $updatePage = new UpdatePage(id: $id, slug: $slug);

        $this->assertEquals($slug, $updatePage->slug());
        $this->assertCount(3, $updatePage->slug());
    }

    #[Test]
    public function should_handle_active_page_status(): void
    {
        $id = Uuid::uuid7()->toString();
        $updatePage = new UpdatePage(id: $id, status: PageStatus::ACTIVE);

        $this->assertEquals(PageStatus::ACTIVE, $updatePage->status());
        $this->assertTrue($updatePage->status() === PageStatus::ACTIVE);
    }

    #[Test]
    public function should_handle_passive_page_status(): void
    {
        $id = Uuid::uuid7()->toString();
        $updatePage = new UpdatePage(id: $id, status: PageStatus::PASSIVE);

        $this->assertEquals(PageStatus::PASSIVE, $updatePage->status());
        $this->assertTrue($updatePage->status() === PageStatus::PASSIVE);
    }

    #[Test]
    public function should_handle_various_order_values(): void
    {
        $id = Uuid::uuid7()->toString();
        $orders = [0, 1, 5, 10, 100, 999];

        foreach ($orders as $order) {
            $updatePage = new UpdatePage(id: $id, order: $order);
            $this->assertEquals($order, $updatePage->order());
        }
    }

    #[Test]
    public function should_return_null_for_unset_order(): void
    {
        $id = Uuid::uuid7()->toString();
        $updatePage = new UpdatePage(id: $id, order: null);

        $this->assertNull($updatePage->order());
    }

    #[Test]
    public function should_preserve_immutability(): void
    {
        $id = Uuid::uuid7()->toString();
        $title = ['en' => 'Original'];
        $updatePage = new UpdatePage(id: $id, title: $title);

        $firstAccess = $updatePage->title();
        $secondAccess = $updatePage->title();

        $this->assertEquals($firstAccess, $secondAccess);
        $this->assertSame($firstAccess, $secondAccess);
    }

    #[Test]
    public function should_create_from_array_with_active_status(): void
    {
        $id = Uuid::uuid7()->toString();
        $data = [
            'id' => $id,
            'title' => ['en' => 'Title'],
            'status' => PageStatus::ACTIVE->value,
        ];

        $updatePage = UpdatePage::createFromArray($data);

        $this->assertEquals(PageStatus::ACTIVE, $updatePage->status());
    }

    #[Test]
    public function should_create_from_array_with_passive_status(): void
    {
        $id = Uuid::uuid7()->toString();
        $data = [
            'id' => $id,
            'title' => ['en' => 'Title'],
            'status' => PageStatus::PASSIVE->value,
        ];

        $updatePage = UpdatePage::createFromArray($data);

        $this->assertEquals(PageStatus::PASSIVE, $updatePage->status());
    }

    #[Test]
    public function should_handle_complex_update_scenario(): void
    {
        $id = Uuid::uuid7()->toString();
        $updateData = [
            'id' => $id,
            'title' => ['en' => 'Updated Title', 'tr' => 'Güncellenmiş Başlık'],
            'slug' => ['en' => 'updated-slug', 'tr' => 'guncellenmis-slug'],
            'order' => 3,
            'status' => PageStatus::ACTIVE->value,
        ];

        $updatePage = UpdatePage::createFromArray($updateData);

        $this->assertEquals($id, $updatePage->id());
        $this->assertCount(2, $updatePage->title());
        $this->assertCount(2, $updatePage->slug());
        $this->assertEquals(3, $updatePage->order());
        $this->assertEquals(PageStatus::ACTIVE, $updatePage->status());
        $this->assertNull($updatePage->content());
    }

    #[Test]
    public function should_handle_partial_update_with_null_values(): void
    {
        $id = Uuid::uuid7()->toString();
        $updatePage = new UpdatePage(
            id: $id,
            title: null,
            content: ['en' => 'Content'],
            slug: null,
            order: null,
            status: PageStatus::PASSIVE,
        );

        $this->assertEquals($id, $updatePage->id());
        $this->assertNull($updatePage->title());
        $this->assertEquals(['en' => 'Content'], $updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertEquals(PageStatus::PASSIVE, $updatePage->status());
    }

    #[Test]
    public function should_create_from_array_with_missing_optional_fields(): void
    {
        $id = Uuid::uuid7()->toString();
        $data = [
            'id' => $id,
            'content' => ['en' => 'Only Content'],
        ];

        $updatePage = UpdatePage::createFromArray($data);

        $this->assertEquals($id, $updatePage->id());
        $this->assertNull($updatePage->title());
        $this->assertEquals($data['content'], $updatePage->content());
        $this->assertNull($updatePage->slug());
        $this->assertNull($updatePage->order());
        $this->assertNull($updatePage->status());
    }

    #[Test]
    public function should_handle_zero_order(): void
    {
        $id = Uuid::uuid7()->toString();
        $updatePage = new UpdatePage(id: $id, order: 0);

        $this->assertEquals(0, $updatePage->order());
        $this->assertIsInt($updatePage->order());
    }

    #[Test]
    public function should_handle_single_language_update(): void
    {
        $id = Uuid::uuid7()->toString();
        $updatePage = new UpdatePage(
            id: $id,
            title: ['en' => 'Single Language'],
            slug: ['en' => 'single-language'],
        );

        $this->assertEquals($id, $updatePage->id());
        $this->assertCount(1, $updatePage->title());
        $this->assertCount(1, $updatePage->slug());
        $this->assertEquals('Single Language', $updatePage->title()['en']);
    }

    #[Test]
    public function should_return_valid_uuid(): void
    {
        $id = Uuid::uuid7()->toString();
        $updatePage = new UpdatePage(id: $id);

        $this->assertTrue(Uuid::isValid($updatePage->id()));
        $this->assertEquals($id, $updatePage->id());
    }
}
