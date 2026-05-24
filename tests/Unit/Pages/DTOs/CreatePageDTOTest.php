<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Pages\Application\DTOs\CreatePageDTO;
use Source\Pages\Domain\Enums\PageStatus;

class CreatePageDTOTest extends TestCase
{
    /** @test */
    #[Test]
    public function should_create_instance_with_required_parameters(): void
    {
        // GIVEN: Required parameters
        $title = ['en' => 'Test Title'];
        $content = ['en' => 'Test Content'];
        $slug = ['en' => 'test-title'];

        // WHEN: Creating CreatePageDTO
        $dto = new CreatePageDTO(
            title: $title,
            content: $content,
            slug: $slug,
        );

        // THEN: Should create instance successfully
        $this->assertInstanceOf(CreatePageDTO::class, $dto);
    }

    /** @test */
    #[Test]
    public function should_create_instance_with_optional_parameters(): void
    {
        // GIVEN: All parameters
        $title = ['en' => 'Test Title'];
        $content = ['en' => 'Test Content'];
        $slug = ['en' => 'test-title'];
        $parentId = Uuid::uuid7()->toString();
        $order = 5;
        $status = 'active';

        // WHEN: Creating CreatePageDTO with all parameters
        $dto = new CreatePageDTO(
            title: $title,
            content: $content,
            slug: $slug,
            parentId: $parentId,
            order: $order,
            status: $status,
        );

        // THEN: Should create instance successfully
        $this->assertInstanceOf(CreatePageDTO::class, $dto);
    }

    /** @test */
    #[Test]
    public function should_create_from_request_with_minimal_data(): void
    {
        // GIVEN: Minimal request data
        $data = [
            'title' => ['en' => 'Test Title'],
            'content' => ['en' => 'Test Content'],
            'slug' => ['en' => 'test-title'],
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: Should create instance with default values
        $this->assertInstanceOf(CreatePageDTO::class, $dto);
    }

    /** @test */
    #[Test]
    public function should_create_from_request_with_all_data(): void
    {
        // GIVEN: Full request data
        $title = ['en' => 'Test Title', 'tr' => 'Test Başlığı'];
        $content = ['en' => 'Test Content', 'tr' => 'Test İçeriği'];
        $slug = ['en' => 'test-title', 'tr' => 'test-basligi'];
        $parentId = Uuid::uuid7()->toString();
        $order = 10;
        $status = 'active';

        $data = [
            'title' => $title,
            'content' => $content,
            'slug' => $slug,
            'parentId' => $parentId,
            'order' => $order,
            'status' => $status,
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: Should create instance with all data
        $this->assertInstanceOf(CreatePageDTO::class, $dto);
        $this->assertEquals($title, $dto->toArray()['title']);
        $this->assertEquals($content, $dto->toArray()['content']);
        $this->assertEquals($slug, $dto->toArray()['slug']);
        $this->assertEquals($parentId, $dto->toArray()['parentId']);
        $this->assertEquals($order, $dto->toArray()['order']);
        $this->assertEquals($status, $dto->toArray()['status']);
    }

    /** @test */
    #[Test]
    public function should_default_to_null_parent_id(): void
    {
        // GIVEN: Request data without parentId
        $data = [
            'title' => ['en' => 'Title'],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'slug'],
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: parentId should be null
        $this->assertNull($dto->toArray()['parentId']);
    }

    /** @test */
    #[Test]
    public function should_default_order_to_zero(): void
    {
        // GIVEN: Request data without order
        $data = [
            'title' => ['en' => 'Title'],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'slug'],
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: order should default to 0
        $this->assertEquals(0, $dto->toArray()['order']);
    }

    /** @test */
    #[Test]
    public function should_default_status_to_passive(): void
    {
        // GIVEN: Request data without status
        $data = [
            'title' => ['en' => 'Title'],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'slug'],
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: status should default to 'passive'
        $this->assertEquals(PageStatus::PASSIVE->value, $dto->toArray()['status']);
    }

    /** @test */
    #[Test]
    public function should_convert_order_to_integer(): void
    {
        // GIVEN: Request data with string order
        $data = [
            'title' => ['en' => 'Title'],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'slug'],
            'order' => '5',
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: order should be converted to integer
        $this->assertIsInt($dto->toArray()['order']);
        $this->assertEquals(5, $dto->toArray()['order']);
    }

    /** @test */
    #[Test]
    public function should_handle_multilingual_data_from_request(): void
    {
        // GIVEN: Request with multilingual data
        $title = ['en' => 'English', 'tr' => 'Turkish', 'es' => 'Spanish'];
        $content = ['en' => 'Content EN', 'tr' => 'Content TR', 'es' => 'Content ES'];
        $slug = ['en' => 'english', 'tr' => 'turkish', 'es' => 'spanish'];

        $data = [
            'title' => $title,
            'content' => $content,
            'slug' => $slug,
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: Should preserve all languages
        $array = $dto->toArray();
        $this->assertCount(3, $array['title']);
        $this->assertCount(3, $array['content']);
        $this->assertCount(3, $array['slug']);
    }

    /** @test */
    #[Test]
    public function should_ignore_extra_data_from_request(): void
    {
        // GIVEN: Request with extra fields
        $data = [
            'title' => ['en' => 'Title'],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'slug'],
            'extra_field' => 'should_be_ignored',
            'another_field' => 'also_ignored',
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: Should only use specified fields
        $this->assertInstanceOf(CreatePageDTO::class, $dto);
    }

    /** @test */
    #[Test]
    public function should_return_array_from_to_array(): void
    {
        // GIVEN: CreatePageDTO
        $title = ['en' => 'Title'];
        $content = ['en' => 'Content'];
        $slug = ['en' => 'slug'];
        $parentId = Uuid::uuid7()->toString();

        $dto = new CreatePageDTO(
            title: $title,
            content: $content,
            slug: $slug,
            parentId: $parentId,
            order: 5,
            status: 'active',
        );

        // WHEN: Calling toArray
        $array = $dto->toArray();

        // THEN: Should return complete array
        $this->assertIsArray($array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('slug', $array);
        $this->assertArrayHasKey('parentId', $array);
        $this->assertArrayHasKey('order', $array);
        $this->assertArrayHasKey('status', $array);
    }

    /** @test */
    #[Test]
    public function should_handle_active_status(): void
    {
        // GIVEN: Request with active status
        $data = [
            'title' => ['en' => 'Title'],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'slug'],
            'status' => 'active',
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: Should have active status
        $this->assertEquals('active', $dto->toArray()['status']);
    }

    /** @test */
    #[Test]
    public function should_handle_passive_status(): void
    {
        // GIVEN: Request with passive status
        $data = [
            'title' => ['en' => 'Title'],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'slug'],
            'status' => 'passive',
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: Should have passive status
        $this->assertEquals('passive', $dto->toArray()['status']);
    }

    /** @test */
    #[Test]
    public function should_be_readonly(): void
    {
        // THEN: CreatePageDTO should be readonly
        $dto = new CreatePageDTO(
            title: ['en' => 'Title'],
            content: ['en' => 'Content'],
            slug: ['en' => 'slug'],
        );

        $this->assertInstanceOf(CreatePageDTO::class, $dto);
        $reflection = new \ReflectionClass(CreatePageDTO::class);
        $this->assertTrue($reflection->isReadonly(), 'CreatePageDTO should be readonly');
    }

    /** @test */
    #[Test]
    public function should_handle_various_order_values(): void
    {
        // GIVEN: Various order values in request
        $orderValues = [0, 1, 10, 100, 999];

        foreach ($orderValues as $order) {
            $data = [
                'title' => ['en' => 'Title'],
                'content' => ['en' => 'Content'],
                'slug' => ['en' => 'slug'],
                'order' => $order,
            ];

            // WHEN: Creating from request
            $dto = CreatePageDTO::fromRequest($data);

            // THEN: Should handle various order values
            $this->assertEquals($order, $dto->toArray()['order']);
        }
    }

    /** @test */
    #[Test]
    public function should_preserve_null_parent_id_in_array(): void
    {
        // GIVEN: CreatePageDTO without parent
        $dto = new CreatePageDTO(
            title: ['en' => 'Title'],
            content: ['en' => 'Content'],
            slug: ['en' => 'slug'],
            parentId: null,
        );

        // WHEN: Calling toArray
        $array = $dto->toArray();

        // THEN: Should have null parentId
        $this->assertNull($array['parentId']);
    }

    /** @test */
    #[Test]
    public function should_convert_from_request_with_extra_order_field(): void
    {
        // GIVEN: Request with float order value
        $data = [
            'title' => ['en' => 'Title'],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'slug'],
            'order' => 5.7,
        ];

        // WHEN: Creating from request
        $dto = CreatePageDTO::fromRequest($data);

        // THEN: Should convert to integer
        $this->assertIsInt($dto->toArray()['order']);
        $this->assertEquals(5, $dto->toArray()['order']);
    }
}
