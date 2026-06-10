<?php

declare(strict_types=1);

namespace Tests\Unit\References\Queries;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\References\Application\DTOs\ReferenceResponseDTO;
use Source\References\Application\Queries\GetReferences;
use Source\References\Domain\Repository\ReferenceRepository;

class GetReferencesTest extends TestCase
{
    /** @var ReferenceRepository&Mockery\MockInterface */
    private Mockery\MockInterface $repository;

    private GetReferences $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ReferenceRepository::class);
        $this->query = new GetReferences($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    #[Test]
    public function returns_empty_array_when_no_references(): void
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
            ['id' => Uuid::uuid7()->toString(), 'name' => 'Acme Corp', 'order' => 0],
            ['id' => Uuid::uuid7()->toString(), 'name' => 'Beta Inc', 'order' => 1],
        ]);

        // WHEN
        $result = $this->query->execute();

        // THEN
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ReferenceResponseDTO::class, $result);
        $this->assertSame('Acme Corp', $result[0]->name());
        $this->assertSame('Beta Inc', $result[1]->name());
    }
}
