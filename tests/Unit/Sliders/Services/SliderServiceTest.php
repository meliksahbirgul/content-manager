<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\Services;

use DomainException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Languages\Domain\Repository\LanguageRepository;
use Source\Sliders\Application\DTOs\CreateSliderDTO;
use Source\Sliders\Application\DTOs\UpdateSliderDTO;
use Source\Sliders\Application\Services\SliderService;
use Source\Sliders\Domain\Entity\SliderEntity;
use Source\Sliders\Domain\Enums\SliderStatus;
use Source\Sliders\Domain\Repository\SliderRepository;
use Source\Sliders\Domain\ValueObjects\CreateSlider;

class SliderServiceTest extends TestCase
{
    /** @var SliderRepository&Mockery\MockInterface */
    private Mockery\MockInterface $repository;

    /** @var LanguageRepository&Mockery\MockInterface */
    private Mockery\MockInterface $languageRepository;

    private SliderService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SliderRepository::class);
        $this->languageRepository = Mockery::mock(LanguageRepository::class);
        $this->languageRepository->allows('codeExists')->andReturn(true)->byDefault();

        $this->service = new SliderService($this->repository, $this->languageRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- createSlider ---

    /** @test */
    #[Test]
    public function create_slider_succeeds_and_returns_payload(): void
    {
        // GIVEN
        $dto = CreateSliderDTO::fromRequest([
            'title' => ['en' => 'Banner'],
            'href'  => ['en' => 'https://example.com'],
            'order' => 1,
        ]);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturnUsing(fn (CreateSlider $p) => $p);

        // WHEN
        $result = $this->service->createSlider($dto);

        // THEN
        $this->assertInstanceOf(CreateSlider::class, $result);
        $this->assertSame(['en' => 'Banner'], $result->title());
        $this->assertSame(['en' => 'https://example.com'], $result->href());
    }

    /** @test */
    #[Test]
    public function create_slider_validates_language_codes_in_title(): void
    {
        // GIVEN: unknown lang code in title
        $this->languageRepository->allows('codeExists')
            ->with('xx')
            ->andReturn(false);

        $dto = CreateSliderDTO::fromRequest([
            'title' => ['xx' => 'Bad Lang'],
            'href'  => ['en' => 'https://example.com'],
        ]);

        $this->repository->shouldNotReceive('create');

        // THEN
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid language code: "xx".');

        // WHEN
        $this->service->createSlider($dto);
    }

    /** @test */
    #[Test]
    public function create_slider_validates_language_codes_in_href(): void
    {
        // GIVEN: unknown lang code in href
        $this->languageRepository->allows('codeExists')
            ->with('xx')
            ->andReturn(false);

        $dto = CreateSliderDTO::fromRequest([
            'title' => ['en' => 'Banner'],
            'href'  => ['xx' => 'https://example.com'],
        ]);

        $this->repository->shouldNotReceive('create');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid language code: "xx".');

        $this->service->createSlider($dto);
    }

    // --- updateSlider ---

    /** @test */
    #[Test]
    public function update_slider_succeeds(): void
    {
        // GIVEN
        $id = Uuid::uuid7()->toString();
        $dto = UpdateSliderDTO::fromRequest(['id' => $id, 'title' => ['en' => 'Updated']]);

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($id)
            ->andReturn(Mockery::mock(SliderEntity::class));

        $this->repository->shouldReceive('update')->once();

        // WHEN
        $this->service->updateSlider($dto);

        // THEN: no exception
        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function update_slider_throws_when_slider_not_found(): void
    {
        // GIVEN
        $id = Uuid::uuid7()->toString();
        $dto = UpdateSliderDTO::fromRequest(['id' => $id, 'title' => ['en' => 'Updated']]);

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($id)
            ->andReturn(null);

        $this->repository->shouldNotReceive('update');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Slider not found.');

        // WHEN
        $this->service->updateSlider($dto);
    }

    /** @test */
    #[Test]
    public function update_slider_validates_language_codes_in_href(): void
    {
        // GIVEN
        $id = Uuid::uuid7()->toString();
        $this->languageRepository->allows('codeExists')->with('xx')->andReturn(false);

        $dto = UpdateSliderDTO::fromRequest([
            'id'   => $id,
            'href' => ['xx' => 'https://example.com'],
        ]);

        $this->repository->shouldNotReceive('findByUuid');
        $this->repository->shouldNotReceive('update');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid language code: "xx".');

        $this->service->updateSlider($dto);
    }

    // --- deleteSlider ---

    /** @test */
    #[Test]
    public function delete_slider_succeeds(): void
    {
        // GIVEN
        $id = Uuid::uuid7()->toString();

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($id)
            ->andReturn(Mockery::mock(SliderEntity::class));

        $this->repository->shouldReceive('delete')->once()->with($id);

        // WHEN
        $this->service->deleteSlider($id);

        // THEN: no exception
        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function delete_slider_throws_when_slider_not_found(): void
    {
        // GIVEN
        $id = Uuid::uuid7()->toString();

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($id)
            ->andReturn(null);

        $this->repository->shouldNotReceive('delete');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Slider not found.');

        // WHEN
        $this->service->deleteSlider($id);
    }
}
