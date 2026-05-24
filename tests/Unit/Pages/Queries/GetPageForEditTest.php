<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\Queries;

use DomainException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Pages\Application\DTOs\PageEditResponseDTO;
use Source\Pages\Application\Queries\GetPageForEdit;
use Source\Pages\Domain\Entity\PageEntity;
use Source\Pages\Domain\Enums\PageStatus;
use Source\Pages\Domain\Repository\Repository;

class GetPageForEditTest extends TestCase
{
    /** @var Repository&Mockery\MockInterface */
    private Mockery\MockInterface $repositoryMock;

    private GetPageForEdit $getPageForEdit;

    private string $validUuid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(Repository::class);
        $this->getPageForEdit = new GetPageForEdit($this->repositoryMock);
        $this->validUuid = Uuid::uuid7()->toString();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    #[Test]
    public function should_return_page_edit_response_dto_for_valid_uuid(): void
    {
        // GIVEN: Valid UUID and mock page entity
        $uuid = $this->validUuid;
        $pageEntity = Mockery::mock(PageEntity::class);
        $pageEntity->shouldReceive('id')->andReturn($uuid);
        $pageEntity->shouldReceive('title')->andReturn(['en' => 'Test Title']);
        $pageEntity->shouldReceive('content')->andReturn(['en' => 'Test Content']);
        $pageEntity->shouldReceive('slug')->andReturn(['en' => 'test-title']);
        $pageEntity->shouldReceive('status')->andReturn(PageStatus::ACTIVE);
        $pageEntity->shouldReceive('order')->andReturn(0);
        $pageEntity->shouldReceive('parentId')->andReturn(null);
        $pageEntity->shouldReceive('images')->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid, true)
            ->andReturn($pageEntity);

        // WHEN: Executing query
        $result = $this->getPageForEdit->execute($uuid);

        // THEN: Should return PageEditResponseDTO
        $this->assertInstanceOf(PageEditResponseDTO::class, $result);
        $this->assertEquals($uuid, $result->id());
        $this->assertEquals('Test Title', $result->title()['en']);
        $this->assertEquals('Test Content', $result->content()['en']);
        $this->assertEquals('test-title', $result->slug()['en']);
        $this->assertEquals('active', $result->status());
    }

    /** @test */
    #[Test]
    public function should_throw_exception_when_page_not_found(): void
    {
        // GIVEN: UUID that doesn't exist
        $uuid = $this->validUuid;
        $this->repositoryMock
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid, true)
            ->andReturn(null);

        // THEN: Should throw DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Page not found.');

        // WHEN: Executing query
        $this->getPageForEdit->execute($uuid);
    }

    /** @test */
    #[Test]
    public function should_call_repository_find_by_uuid_with_correct_uuid(): void
    {
        // GIVEN: Valid UUID
        $uuid = $this->validUuid;
        $pageEntity = Mockery::mock(PageEntity::class);
        $pageEntity->shouldReceive('id')->andReturn($uuid);
        $pageEntity->shouldReceive('title')->andReturn(['en' => 'Title']);
        $pageEntity->shouldReceive('content')->andReturn(['en' => 'Content']);
        $pageEntity->shouldReceive('slug')->andReturn(['en' => 'slug']);
        $pageEntity->shouldReceive('status')->andReturn(PageStatus::PASSIVE);
        $pageEntity->shouldReceive('order')->andReturn(0);
        $pageEntity->shouldReceive('parentId')->andReturn(null);
        $pageEntity->shouldReceive('images')->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid, true)
            ->andReturn($pageEntity);

        // WHEN: Executing query
        $this->getPageForEdit->execute($uuid);

        // THEN: Repository method was called with correct UUID
        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function should_return_dto_with_all_page_attributes(): void
    {
        // GIVEN: Page entity with all attributes
        $uuid = $this->validUuid;
        $parentUuid = Uuid::uuid7()->toString();
        $title = ['en' => 'English Title', 'tr' => 'Turkish Title'];
        $content = ['en' => 'English Content', 'tr' => 'Turkish Content'];
        $slug = ['en' => 'english-title', 'tr' => 'turkish-title'];
        $order = 5;
        $status = PageStatus::ACTIVE;

        $pageEntity = Mockery::mock(PageEntity::class);
        $pageEntity->shouldReceive('id')->andReturn($uuid);
        $pageEntity->shouldReceive('title')->andReturn($title);
        $pageEntity->shouldReceive('content')->andReturn($content);
        $pageEntity->shouldReceive('slug')->andReturn($slug);
        $pageEntity->shouldReceive('status')->andReturn($status);
        $pageEntity->shouldReceive('order')->andReturn($order);
        $pageEntity->shouldReceive('parentId')->andReturn($parentUuid);
        $pageEntity->shouldReceive('images')->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('findByUuid')
            ->with($uuid, true)
            ->andReturn($pageEntity);

        // WHEN: Executing query
        $result = $this->getPageForEdit->execute($uuid);

        // THEN: DTO should contain all attributes
        $this->assertEquals($uuid, $result->id());
        $this->assertEquals($title, $result->title());
        $this->assertEquals($content, $result->content());
        $this->assertEquals($slug, $result->slug());
        $this->assertEquals('active', $result->status());
        $this->assertEquals($order, $result->order());
        $this->assertEquals($parentUuid, $result->parentId());
    }

    /** @test */
    #[Test]
    public function should_return_dto_with_passive_status(): void
    {
        // GIVEN: Page entity with PASSIVE status
        $uuid = $this->validUuid;
        $pageEntity = Mockery::mock(PageEntity::class);
        $pageEntity->shouldReceive('id')->andReturn($uuid);
        $pageEntity->shouldReceive('title')->andReturn(['en' => 'Title']);
        $pageEntity->shouldReceive('content')->andReturn(['en' => 'Content']);
        $pageEntity->shouldReceive('slug')->andReturn(['en' => 'slug']);
        $pageEntity->shouldReceive('status')->andReturn(PageStatus::PASSIVE);
        $pageEntity->shouldReceive('order')->andReturn(0);
        $pageEntity->shouldReceive('parentId')->andReturn(null);
        $pageEntity->shouldReceive('images')->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('findByUuid')
            ->with($uuid, true)
            ->andReturn($pageEntity);

        // WHEN: Executing query
        $result = $this->getPageForEdit->execute($uuid);

        // THEN: DTO should have PASSIVE status
        $this->assertEquals('passive', $result->status());
    }

    /** @test */
    #[Test]
    public function should_return_dto_with_null_parent_id(): void
    {
        // GIVEN: Page entity without parent
        $uuid = $this->validUuid;
        $pageEntity = Mockery::mock(PageEntity::class);
        $pageEntity->shouldReceive('id')->andReturn($uuid);
        $pageEntity->shouldReceive('title')->andReturn(['en' => 'Title']);
        $pageEntity->shouldReceive('content')->andReturn(['en' => 'Content']);
        $pageEntity->shouldReceive('slug')->andReturn(['en' => 'slug']);
        $pageEntity->shouldReceive('status')->andReturn(PageStatus::ACTIVE);
        $pageEntity->shouldReceive('order')->andReturn(0);
        $pageEntity->shouldReceive('parentId')->andReturn(null);
        $pageEntity->shouldReceive('images')->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('findByUuid')
            ->with($uuid, true)
            ->andReturn($pageEntity);

        // WHEN: Executing query
        $result = $this->getPageForEdit->execute($uuid);

        // THEN: DTO parentId should be null
        $this->assertNull($result->parentId());
    }

    /** @test */
    #[Test]
    public function should_handle_multilingual_page_data(): void
    {
        // GIVEN: Page entity with multilingual data
        $uuid = $this->validUuid;
        $title = [
            'en' => 'English Title',
            'tr' => 'Turkish Title',
            'es' => 'Spanish Title',
        ];
        $content = [
            'en' => 'English Content',
            'tr' => 'Turkish Content',
            'es' => 'Spanish Content',
        ];
        $slug = [
            'en' => 'english-title',
            'tr' => 'turkish-title',
            'es' => 'spanish-title',
        ];

        $pageEntity = Mockery::mock(PageEntity::class);
        $pageEntity->shouldReceive('id')->andReturn($uuid);
        $pageEntity->shouldReceive('title')->andReturn($title);
        $pageEntity->shouldReceive('content')->andReturn($content);
        $pageEntity->shouldReceive('slug')->andReturn($slug);
        $pageEntity->shouldReceive('status')->andReturn(PageStatus::ACTIVE);
        $pageEntity->shouldReceive('order')->andReturn(0);
        $pageEntity->shouldReceive('parentId')->andReturn(null);
        $pageEntity->shouldReceive('images')->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('findByUuid')
            ->with($uuid, true)
            ->andReturn($pageEntity);

        // WHEN: Executing query
        $result = $this->getPageForEdit->execute($uuid);

        // THEN: DTO should have all language versions
        $this->assertCount(3, $result->title());
        $this->assertCount(3, $result->content());
        $this->assertCount(3, $result->slug());
        $this->assertEquals('English Title', $result->title()['en']);
        $this->assertEquals('Turkish Title', $result->title()['tr']);
    }

    /** @test */
    #[Test]
    public function should_handle_various_order_values(): void
    {
        // GIVEN: Page entities with different order values
        $orderValues = [0, 1, 10, 100];

        foreach ($orderValues as $order) {
            $uuid = Uuid::uuid7()->toString();
            $pageEntity = Mockery::mock(PageEntity::class);
            $pageEntity->shouldReceive('id')->andReturn($uuid);
            $pageEntity->shouldReceive('title')->andReturn(['en' => 'Title']);
            $pageEntity->shouldReceive('content')->andReturn(['en' => 'Content']);
            $pageEntity->shouldReceive('slug')->andReturn(['en' => 'slug']);
            $pageEntity->shouldReceive('status')->andReturn(PageStatus::ACTIVE);
            $pageEntity->shouldReceive('order')->andReturn($order);
            $pageEntity->shouldReceive('parentId')->andReturn(null);
            $pageEntity->shouldReceive('images')->andReturn([]);

            $this->repositoryMock
                ->shouldReceive('findByUuid')
                ->with($uuid, true)
                ->andReturn($pageEntity);

            // WHEN: Executing query
            $result = $this->getPageForEdit->execute($uuid);

            // THEN: DTO should have correct order
            $this->assertEquals($order, $result->order());
        }
    }

    /** @test */
    #[Test]
    public function should_convert_page_entity_to_dto(): void
    {
        // GIVEN: Page entity
        $uuid = $this->validUuid;
        $pageEntity = Mockery::mock(PageEntity::class);
        $pageEntity->shouldReceive('id')->andReturn($uuid);
        $pageEntity->shouldReceive('title')->andReturn(['en' => 'Title']);
        $pageEntity->shouldReceive('content')->andReturn(['en' => 'Content']);
        $pageEntity->shouldReceive('slug')->andReturn(['en' => 'slug']);
        $pageEntity->shouldReceive('status')->andReturn(PageStatus::ACTIVE);
        $pageEntity->shouldReceive('order')->andReturn(5);
        $pageEntity->shouldReceive('parentId')->andReturn(Uuid::uuid7()->toString());
        $pageEntity->shouldReceive('images')->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('findByUuid')
            ->with($uuid, true)
            ->andReturn($pageEntity);

        // WHEN: Executing query
        $result = $this->getPageForEdit->execute($uuid);

        // THEN: Result should be a valid DTO
        $this->assertInstanceOf(PageEditResponseDTO::class, $result);
        $this->assertTrue(Uuid::isValid($result->id()));
    }

    /** @test */
    #[Test]
    public function should_not_throw_exception_for_valid_uuids(): void
    {
        // GIVEN: Valid UUID
        $uuid = $this->validUuid;
        $pageEntity = Mockery::mock(PageEntity::class);
        $pageEntity->shouldReceive('id')->andReturn($uuid);
        $pageEntity->shouldReceive('title')->andReturn(['en' => 'Title']);
        $pageEntity->shouldReceive('content')->andReturn(['en' => 'Content']);
        $pageEntity->shouldReceive('slug')->andReturn(['en' => 'slug']);
        $pageEntity->shouldReceive('status')->andReturn(PageStatus::ACTIVE);
        $pageEntity->shouldReceive('order')->andReturn(0);
        $pageEntity->shouldReceive('parentId')->andReturn(null);
        $pageEntity->shouldReceive('images')->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('findByUuid')
            ->with($uuid, true)
            ->andReturn($pageEntity);

        // THEN: Should not throw exception
        $this->expectNotToPerformAssertions();

        // WHEN: Executing query
        $this->getPageForEdit->execute($uuid);
    }

    /** @test */
    #[Test]
    public function should_be_readonly_class(): void
    {
        // THEN: GetPageForEdit should be readonly
        $this->assertInstanceOf(GetPageForEdit::class, $this->getPageForEdit);
        $reflection = new \ReflectionClass(GetPageForEdit::class);
        $this->assertTrue($reflection->isReadonly(), 'GetPageForEdit should be readonly');
    }
}
