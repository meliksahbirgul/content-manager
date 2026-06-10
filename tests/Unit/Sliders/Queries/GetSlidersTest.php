<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\Queries;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Sliders\Application\DTOs\SliderResponseDTO;
use Source\Sliders\Application\Queries\GetSliders;
use Source\Sliders\Domain\Repository\SliderRepository;

class GetSlidersTest extends TestCase
{
    /** @var SliderRepository&Mockery\MockInterface */
    private Mockery\MockInterface $repository;

    private GetSliders $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(SliderRepository::class);
        $this->query = new GetSliders($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    #[Test]
    public function returns_empty_array_when_no_sliders(): void
    {
        $this->repository->shouldReceive('listAll')->once()->andReturn([]);

        $result = $this->query->execute();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function maps_repository_rows_to_response_dtos(): void
    {
        // GIVEN
        $this->repository->shouldReceive('listAll')->once()->andReturn([
            [
                'id'       => Uuid::uuid7()->toString(),
                'title'    => ['en' => 'Hero'],
                'href'     => ['en' => 'https://example.com'],
                'order'    => 0,
                'isActive' => 'active',
            ],
            [
                'id'       => Uuid::uuid7()->toString(),
                'title'    => ['en' => 'Banner'],
                'href'     => ['en' => 'https://example.com/banner'],
                'order'    => 1,
                'isActive' => 'passive',
            ],
        ]);

        // WHEN
        $result = $this->query->execute();

        // THEN
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(SliderResponseDTO::class, $result);
        $this->assertSame(['en' => 'Hero'], $result[0]->title());
        $this->assertSame(['en' => 'Banner'], $result[1]->title());
    }
}
