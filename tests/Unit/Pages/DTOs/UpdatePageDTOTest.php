<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Pages\Application\DTOs\UpdatePageDTO;

class UpdatePageDTOTest extends TestCase
{
    private string $validUuid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validUuid = Uuid::uuid7()->toString();
    }

    /** @test */
    #[Test]
    public function shouldCreateInstanceWithRequiredId(): void
    {
        // GIVEN: Required id parameter
        $id = $this->validUuid;

        // WHEN: Creating UpdatePageDTO
        $dto = new UpdatePageDTO(id: $id);

        // THEN: Should create instance successfully
        $this->assertInstanceOf(UpdatePageDTO::class, $dto);
        $this->assertEquals($id, $dto->id);
    }

    /** @test */
    #[Test]
    public function shouldCreateInstanceWithAllOptionalParameters(): void
    {
        // GIVEN: All parameters
        $id = $this->validUuid;
        $title = ['en' => 'Updated Title', 'tr' => 'Updated Başlığı'];
        $content = ['en' => 'Updated Content', 'tr' => 'Updated İçeriği'];
        $slug = ['en' => 'updated-title', 'tr' => 'updated-basligi'];
        $order = 10;
        $status = 'active';

        // WHEN: Creating UpdatePageDTO with all parameters
        $dto = new UpdatePageDTO(
            id: $id,
            title: $title,
            content: $content,
            slug: $slug,
            order: $order,
            status: $status,
        );

        // THEN: Should create instance successfully
        $this->assertInstanceOf(UpdatePageDTO::class, $dto);
        $this->assertEquals($id, $dto->id);
        $this->assertEquals($title, $dto->title);
        $this->assertEquals($content, $dto->content);
        $this->assertEquals($slug, $dto->slug);
    }

    /** @test */
    #[Test]
    public function shouldCreateFromRequestWithRequiredId(): void
    {
        // GIVEN: Request data with only id
        $id = $this->validUuid;
        $data = ['id' => $id];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should create instance successfully
        $this->assertInstanceOf(UpdatePageDTO::class, $dto);
        $this->assertEquals($id, $dto->id);
    }

    /** @test */
    #[Test]
    public function shouldCreateFromRequestWithAllData(): void
    {
        // GIVEN: Full request data
        $id = $this->validUuid;
        $title = ['en' => 'Updated Title', 'tr' => 'Updated Başlığı'];
        $content = ['en' => 'Updated Content', 'tr' => 'Updated İçeriği'];
        $slug = ['en' => 'updated-title', 'tr' => 'updated-basligi'];
        $order = 15;
        $status = 'passive';

        $data = [
            'id' => $id,
            'title' => $title,
            'content' => $content,
            'slug' => $slug,
            'order' => $order,
            'status' => $status,
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should create instance with all data
        $this->assertEquals($id, $dto->id);
        $this->assertEquals($title, $dto->title);
        $this->assertEquals($content, $dto->content);
        $this->assertEquals($slug, $dto->slug);
    }

    /** @test */
    #[Test]
    public function shouldDefaultToNullForOptionalFields(): void
    {
        // GIVEN: Request with only id
        $id = $this->validUuid;
        $data = ['id' => $id];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Optional fields should be null
        $this->assertNull($dto->title);
        $this->assertNull($dto->content);
        $this->assertNull($dto->slug);
    }

    /** @test */
    #[Test]
    public function shouldAllowPartialUpdate(): void
    {
        // GIVEN: Request with only title and order
        $id = $this->validUuid;
        $title = ['en' => 'New Title'];
        $data = [
            'id' => $id,
            'title' => $title,
            'order' => 5,
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should have title but content and slug null
        $this->assertEquals($id, $dto->id);
        $this->assertEquals($title, $dto->title);
        $this->assertNull($dto->content);
        $this->assertNull($dto->slug);
    }

    /** @test */
    #[Test]
    public function shouldIgnoreExtraFieldsInRequest(): void
    {
        // GIVEN: Request with extra fields
        $id = $this->validUuid;
        $data = [
            'id' => $id,
            'title' => ['en' => 'Title'],
            'extra_field' => 'should_be_ignored',
            'another_field' => 'also_ignored',
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should only use specified fields
        $this->assertInstanceOf(UpdatePageDTO::class, $dto);
    }

    /** @test */
    #[Test]
    public function shouldPreserveMultilingualData(): void
    {
        // GIVEN: Request with multilingual data
        $id = $this->validUuid;
        $title = ['en' => 'English', 'tr' => 'Turkish', 'es' => 'Spanish'];
        $content = ['en' => 'EN Content', 'tr' => 'TR Content', 'es' => 'ES Content'];
        $slug = ['en' => 'english', 'tr' => 'turkish', 'es' => 'spanish'];

        $data = [
            'id' => $id,
            'title' => $title,
            'content' => $content,
            'slug' => $slug,
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should preserve all languages
        $this->assertCount(3, $dto->title);
        $this->assertCount(3, $dto->content);
        $this->assertCount(3, $dto->slug);
        $this->assertEquals('English', $dto->title['en']);
        $this->assertEquals('Turkish', $dto->title['tr']);
    }

    /** @test */
    #[Test]
    public function shouldReturnArrayFromToArray(): void
    {
        // GIVEN: UpdatePageDTO with data
        $id = $this->validUuid;
        $title = ['en' => 'Title'];
        $content = ['en' => 'Content'];

        $dto = new UpdatePageDTO(
            id: $id,
            title: $title,
            content: $content,
            order: 5,
            status: 'active',
        );

        // WHEN: Calling toArray
        $array = $dto->toArray();

        // THEN: Should return complete array
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('slug', $array);
        $this->assertArrayHasKey('order', $array);
        $this->assertArrayHasKey('status', $array);
    }

    /** @test */
    #[Test]
    public function shouldHaveIdAsPublicReadonlyProperty(): void
    {
        // GIVEN: UpdatePageDTO
        $id = $this->validUuid;
        $dto = new UpdatePageDTO(id: $id);

        // THEN: id should be public readonly property
        $this->assertEquals($id, $dto->id);
    }

    /** @test */
    #[Test]
    public function shouldHaveTitleAsPublicReadonlyProperty(): void
    {
        // GIVEN: UpdatePageDTO with title
        $title = ['en' => 'Title'];
        $dto = new UpdatePageDTO(id: $this->validUuid, title: $title);

        // THEN: title should be public readonly property
        $this->assertEquals($title, $dto->title);
    }

    /** @test */
    #[Test]
    public function shouldHandleActiveStatus(): void
    {
        // GIVEN: Request with active status
        $data = [
            'id' => $this->validUuid,
            'status' => 'active',
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should preserve status
        $array = $dto->toArray();
        $this->assertEquals('active', $array['status']);
    }

    /** @test */
    #[Test]
    public function shouldHandlePassiveStatus(): void
    {
        // GIVEN: Request with passive status
        $data = [
            'id' => $this->validUuid,
            'status' => 'passive',
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should preserve status
        $array = $dto->toArray();
        $this->assertEquals('passive', $array['status']);
    }

    /** @test */
    #[Test]
    public function shouldHandleNullStatus(): void
    {
        // GIVEN: Request without status
        $data = [
            'id' => $this->validUuid,
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: status should be null
        $array = $dto->toArray();
        $this->assertNull($array['status']);
    }

    /** @test */
    #[Test]
    public function shouldHandleVariousOrderValues(): void
    {
        // GIVEN: Various order values
        $orderValues = [0, 1, 10, 100, 999];

        foreach ($orderValues as $order) {
            $data = [
                'id' => $this->validUuid,
                'order' => $order,
            ];

            // WHEN: Creating from request
            $dto = UpdatePageDTO::fromRequest($data);

            // THEN: Should preserve order value
            $array = $dto->toArray();
            $this->assertEquals($order, $array['order']);
        }
    }

    /** @test */
    #[Test]
    public function shouldAllowUpdateOnlyTitle(): void
    {
        // GIVEN: Request with only title update
        $id = $this->validUuid;
        $newTitle = ['en' => 'Brand New Title'];
        $data = [
            'id' => $id,
            'title' => $newTitle,
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should update only title
        $this->assertEquals($newTitle, $dto->title);
        $this->assertNull($dto->content);
        $this->assertNull($dto->slug);
    }

    /** @test */
    #[Test]
    public function shouldAllowUpdateOnlyContent(): void
    {
        // GIVEN: Request with only content update
        $id = $this->validUuid;
        $newContent = ['en' => 'Brand New Content'];
        $data = [
            'id' => $id,
            'content' => $newContent,
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should update only content
        $this->assertNull($dto->title);
        $this->assertEquals($newContent, $dto->content);
        $this->assertNull($dto->slug);
    }

    /** @test */
    #[Test]
    public function shouldAllowUpdateOnlySlug(): void
    {
        // GIVEN: Request with only slug update
        $id = $this->validUuid;
        $newSlug = ['en' => 'new-slug'];
        $data = [
            'id' => $id,
            'slug' => $newSlug,
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should update only slug
        $this->assertNull($dto->title);
        $this->assertNull($dto->content);
        $this->assertEquals($newSlug, $dto->slug);
    }

    /** @test */
    #[Test]
    public function shouldBeReadonly(): void
    {
        // THEN: UpdatePageDTO should be readonly
        $dto = new UpdatePageDTO(id: $this->validUuid);

        $reflection = new \ReflectionClass(UpdatePageDTO::class);
        $this->assertTrue($reflection->isReadonly(), 'UpdatePageDTO should be readonly');
    }

    /** @test */
    #[Test]
    public function shouldPreserveNullValuesInArray(): void
    {
        // GIVEN: UpdatePageDTO with some null values
        $dto = new UpdatePageDTO(
            id: $this->validUuid,
            title: ['en' => 'Title'],
            content: null,
            slug: null,
        );

        // WHEN: Calling toArray
        $array = $dto->toArray();

        // THEN: Should preserve null values
        $this->assertNull($array['content']);
        $this->assertNull($array['slug']);
    }

    /** @test */
    #[Test]
    public function shouldCreateFromRequestWithMissingOptionalFields(): void
    {
        // GIVEN: Request without optional fields
        $id = $this->validUuid;
        $data = [
            'id' => $id,
            'title' => ['en' => 'Title'],
            // Missing: content, slug, order, status
        ];

        // WHEN: Creating from request
        $dto = UpdatePageDTO::fromRequest($data);

        // THEN: Should handle missing optional fields gracefully
        $this->assertEquals($id, $dto->id);
        $this->assertNotNull($dto->title);
        $this->assertNull($dto->content);
        $this->assertNull($dto->slug);
    }
}
