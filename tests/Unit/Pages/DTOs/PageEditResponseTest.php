<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Pages\Application\DTOs\PageEditResponseDTO;
use Source\Pages\Domain\Enums\PageStatus;

class PageEditResponseTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithAllRequiredParameters(): void
    {
        $id = Uuid::uuid7()->toString();
        $title = ['en' => 'Test Title', 'tr' => 'Test Başlığı'];
        $content = ['en' => 'Test Content', 'tr' => 'Test İçeriği'];
        $slug = ['en' => 'test-title', 'tr' => 'test-basligi'];
        $status = PageStatus::ACTIVE->value;
        $order = 1;

        $dto = new PageEditResponseDTO(
            id: $id,
            title: $title,
            content: $content,
            slug: $slug,
            status: $status,
            order: $order,
        );

        $this->assertInstanceOf(PageEditResponseDTO::class, $dto);
        $this->assertEquals($id, $dto->id());
        $this->assertEquals($title, $dto->title());
        $this->assertEquals($content, $dto->content());
        $this->assertEquals($slug, $dto->slug());
        $this->assertEquals($status, $dto->status());
        $this->assertEquals($order, $dto->order());
        $this->assertNull($dto->parentId());
    }

    /** @test */
    #[Test]
    public function shouldCreateInstanceWithParentId(): void
    {
        $id = Uuid::uuid7()->toString();
        $parentId = Uuid::uuid7()->toString();
        $title = ['en' => 'Child Page'];
        $content = ['en' => 'Child Content'];
        $slug = ['en' => 'child-page'];
        $status = PageStatus::PASSIVE->value;
        $order = 2;

        $dto = new PageEditResponseDTO(
            id: $id,
            title: $title,
            content: $content,
            slug: $slug,
            status: $status,
            order: $order,
            parentId: $parentId,
        );

        $this->assertEquals($parentId, $dto->parentId());
        $this->assertEquals($order, $dto->order());
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectId(): void
    {
        $id = Uuid::uuid7()->toString();

        $dto = new PageEditResponseDTO(
            id: $id,
            title: ['en' => 'Title'],
            content: ['en' => 'Content'],
            slug: ['en' => 'slug'],
            status: PageStatus::ACTIVE->value,
            order: 0,
        );

        $this->assertEquals($id, $dto->id());
        $this->assertTrue(Uuid::isValid($id));
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectTitle(): void
    {
        $title = [
            'en' => 'English Title',
            'tr' => 'Türkçe Başlık',
            'es' => 'Título en Español',
        ];

        $dto = new PageEditResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: $title,
            content: ['en' => 'Content'],
            slug: ['en' => 'slug'],
            status: PageStatus::ACTIVE->value,
            order: 0,
        );

        $this->assertEquals($title, $dto->title());
        $this->assertCount(3, $dto->title());
        $this->assertEquals('English Title', $dto->title()['en']);
        $this->assertEquals('Türkçe Başlık', $dto->title()['tr']);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectContent(): void
    {
        $content = [
            'en' => 'English Content',
            'tr' => 'Türkçe İçeriği',
            'es' => 'Contenido en Español',
        ];

        $dto = new PageEditResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Title'],
            content: $content,
            slug: ['en' => 'slug'],
            status: PageStatus::ACTIVE->value,
            order: 0,
        );

        $this->assertEquals($content, $dto->content());
        $this->assertCount(3, $dto->content());
        $this->assertEquals('Türkçe İçeriği', $dto->content()['tr']);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectSlug(): void
    {
        $slug = [
            'en' => 'english-page',
            'tr' => 'turkce-sayfa',
            'es' => 'pagina-espanola',
        ];

        $dto = new PageEditResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Title'],
            content: ['en' => 'Content'],
            slug: $slug,
            status: PageStatus::ACTIVE->value,
            order: 0,
        );

        $this->assertEquals($slug, $dto->slug());
        $this->assertCount(3, $dto->slug());
        $this->assertEquals('spanish-page', $dto->slug()['es'] = 'spanish-page');
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectStatus(): void
    {
        $activeStatus = PageStatus::ACTIVE->value;
        $passiveStatus = PageStatus::PASSIVE->value;

        $activeDto = new PageEditResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Active Page'],
            content: ['en' => 'Content'],
            slug: ['en' => 'active-page'],
            status: $activeStatus,
            order: 0,
        );

        $passiveDto = new PageEditResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Passive Page'],
            content: ['en' => 'Content'],
            slug: ['en' => 'passive-page'],
            status: $passiveStatus,
            order: 0,
        );

        $this->assertEquals($activeStatus, $activeDto->status());
        $this->assertEquals($passiveStatus, $passiveDto->status());
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectOrder(): void
    {
        $orders = [0, 1, 5, 10, 100];

        foreach ($orders as $expectedOrder) {
            $dto = new PageEditResponseDTO(
                id: Uuid::uuid7()->toString(),
                title: ['en' => 'Title'],
                content: ['en' => 'Content'],
                slug: ['en' => 'slug'],
                status: PageStatus::ACTIVE->value,
                order: $expectedOrder,
            );

            $this->assertEquals($expectedOrder, $dto->order());
        }
    }

    /** @test */
    #[Test]
    public function shouldReturnNullParentIdWhenNotProvided(): void
    {
        $dto = new PageEditResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Title'],
            content: ['en' => 'Content'],
            slug: ['en' => 'slug'],
            status: PageStatus::ACTIVE->value,
            order: 0,
        );

        $this->assertNull($dto->parentId());
    }

    /** @test */
    #[Test]
    public function shouldReturnValidParentIdWhenProvided(): void
    {
        $parentId = Uuid::uuid7()->toString();

        $dto = new PageEditResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Title'],
            content: ['en' => 'Content'],
            slug: ['en' => 'slug'],
            status: PageStatus::ACTIVE->value,
            order: 0,
            parentId: $parentId,
        );

        $this->assertEquals($parentId, $dto->parentId());
        $this->assertTrue(Uuid::isValid($dto->parentId()));
    }

    /** @test */
    #[Test]
    public function shouldHandleMultilingualDataCorrectly(): void
    {
        $dto = new PageEditResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Title'],
            content: ['en' => 'Content'],
            slug: ['en' => 'slug'],
            status: PageStatus::ACTIVE->value,
            order: 0,
        );

        $this->assertCount(1, $dto->title());
        $this->assertCount(1, $dto->content());
        $this->assertCount(1, $dto->slug());
    }
}
