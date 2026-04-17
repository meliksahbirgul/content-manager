<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\Services;

use DomainException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Pages\Application\DTOs\CreatePageDTO;
use Source\Pages\Application\Services\PageService;
use Source\Pages\Domain\Repository\Repository;
use Source\Pages\Domain\ValueObjects\CreatePage;

class PageServiceTest extends TestCase
{
    /** @var Repository&Mockery\MockInterface */
    private Mockery\MockInterface $repositoryMock;

    private PageService $pageService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock repository interface
        $this->repositoryMock = Mockery::mock(Repository::class);

        // Inject the mock repository into the PageService
        $this->pageService = new PageService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    #[Test]
    public function shouldCreatePageSuccessfully(): void
    {
        // GIVEN: A valid PageCreateDTO
        $dto = new CreatePageDTO(
            title: ['en' => 'Test Page'],
            content: ['en' => 'Test Content'],
            slug: ['en' => 'test-page'],
        );

        // Mock repository expectations
        $this->repositoryMock
            ->shouldReceive('isSlugUnique')
            ->once()
            ->with(['en' => 'test-page'])
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturnUsing(function (CreatePage $page) {
                return $page;
            });

        // WHEN: We call the PageService createPage method
        $result = $this->pageService->createPage($dto);

        // THEN: Should return a CreatePage instance
        $this->assertInstanceOf(CreatePage::class, $result);
        $this->assertEquals(['en' => 'Test Page'], $result->title());
        $this->assertEquals(['en' => 'Test Content'], $result->content());
        $this->assertEquals(['en' => 'test-page'], $result->slug());
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenSlugIsNotUnique(): void
    {
        // GIVEN: A PageCreateDTO with a slug that already exists
        $dto = new CreatePageDTO(
            title: ['en' => 'Existing Page'],
            content: ['en' => 'Content'],
            slug: ['en' => 'existing-slug'],
        );

        // Mock repository to return false (slug not unique)
        $this->repositoryMock
            ->shouldReceive('isSlugUnique')
            ->once()
            ->with(['en' => 'existing-slug'])
            ->andReturn(false);

        // AND: Create should NOT be called
        $this->repositoryMock->shouldNotReceive('create');

        // THEN: We expect DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('This slug is already taken.');

        // WHEN: We call the PageService createPage method
        $this->pageService->createPage($dto);
    }

    /** @test */
    #[Test]
    public function shouldVerifyRepositoryCallOrderIsCorrect(): void
    {
        // GIVEN: A valid CreatePageDTO
        $dto = new CreatePageDTO(
            title: ['en' => 'Test Page'],
            content: ['en' => 'Content'],
            slug: ['en' => 'test-page'],
        );

        // Mock with ordered expectations
        $this->repositoryMock
            ->shouldReceive('isSlugUnique')
            ->once()
            ->ordered()
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->ordered()
            ->andReturnUsing(function (CreatePage $page) {
                return $page;
            });

        // WHEN: We call the service
        $result = $this->pageService->createPage($dto);

        // THEN: The result should be a CreatePage instance
        // The ordered() constraints ensure isSlugUnique was called before create
        $this->assertInstanceOf(CreatePage::class, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandlePageWithParentId(): void
    {
        // GIVEN: A CreatePageDTO with parent ID
        $parentUuid = Uuid::uuid7()->toString();
        $dto = new CreatePageDTO(
            title: ['en' => 'Child Page'],
            content: ['en' => 'Content'],
            slug: ['en' => 'child-page'],
            parentId: $parentUuid,
            order: 2,
        );

        // Mock repository expectations
        $this->repositoryMock
            ->shouldReceive('isSlugUnique')
            ->once()
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturnUsing(function (CreatePage $page) {
                return $page;
            });

        // WHEN: We call the service
        $result = $this->pageService->createPage($dto);

        // THEN: Verify the page was created with parent info
        $this->assertInstanceOf(CreatePage::class, $result);
        $this->assertEquals($parentUuid, $result->parentId());
    }

    /** @test */
    #[Test]
    public function shouldVerifySlugValidationBeforeCreation(): void
    {
        // GIVEN: A CreatePageDTO
        $dto = new CreatePageDTO(
            title: ['en' => 'Test Page'],
            content: ['en' => 'Content'],
            slug: ['en' => 'test-page'],
        );

        // Mock: slug validation passes but we'll track call order
        $this->repositoryMock
            ->shouldReceive('isSlugUnique')
            ->once()
            ->ordered()
            ->with(['en' => 'test-page'])
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->ordered()
            ->andReturnUsing(function (CreatePage $page) {
                return $page;
            });

        // WHEN: We call the service
        $result = $this->pageService->createPage($dto);

        // THEN: The result should be valid and both repo methods were called
        $this->assertInstanceOf(CreatePage::class, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleMultilingualPages(): void
    {
        // GIVEN: A multilingual CreatePageDTO
        $dto = new CreatePageDTO(
            title: [
                'en' => 'English Title',
                'tr' => 'Türkçe Başlık',
                'es' => 'Título en Español',
            ],
            content: [
                'en' => 'English Content',
                'tr' => 'Türkçe İçerik',
                'es' => 'Contenido en Español',
            ],
            slug: [
                'en' => 'english-page',
                'tr' => 'turkce-sayfa',
                'es' => 'pagina-espanola',
            ],
        );

        // Mock repository
        $this->repositoryMock
            ->shouldReceive('isSlugUnique')
            ->once()
            ->with([
                'en' => 'english-page',
                'tr' => 'turkce-sayfa',
                'es' => 'pagina-espanola',
            ])
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturnUsing(function (CreatePage $page) {
                return $page;
            });

        // WHEN: We call the service
        $result = $this->pageService->createPage($dto);

        // THEN: Verify multilingual data is preserved
        $this->assertInstanceOf(CreatePage::class, $result);
        $this->assertCount(3, $result->title());
        $this->assertCount(3, $result->content());
        $this->assertCount(3, $result->slug());
        $this->assertEquals('Türkçe Başlık', $result->title()['tr']);
    }

    /** @test */
    #[Test]
    public function shouldRethrowRepositoryException(): void
    {
        // GIVEN: Repository throws an exception during slug check
        $dto = new CreatePageDTO(
            title: ['en' => 'Test'],
            content: ['en' => 'Content'],
            slug: ['en' => 'test'],
        );

        $this->repositoryMock
            ->shouldReceive('isSlugUnique')
            ->once()
            ->andThrow(new DomainException('Database error'));

        // THEN: We expect the exception to propagate
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Database error');

        // WHEN: We call the service
        $this->pageService->createPage($dto);
    }

    /** @test */
    #[Test]
    public function shouldVerifyRepositoryCreateReceivesCorrectData(): void
    {
        // GIVEN: A specific CreatePageDTO
        $dto = new CreatePageDTO(
            title: ['en' => 'My Page'],
            content: ['en' => 'My Content'],
            slug: ['en' => 'my-page'],
            order: 5,
        );

        // Mock expectations with data verification
        $this->repositoryMock
            ->shouldReceive('isSlugUnique')
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (CreatePage $page) {
                return $page->title() === ['en' => 'My Page']
                    && $page->content() === ['en' => 'My Content']
                    && $page->slug() === ['en' => 'my-page'];
            }))
            ->andReturnUsing(function (CreatePage $page) {
                return $page;
            });

        // WHEN: We call the service
        $result = $this->pageService->createPage($dto);

        // THEN: Page should be created successfully
        $this->assertInstanceOf(CreatePage::class, $result);
    }
}
