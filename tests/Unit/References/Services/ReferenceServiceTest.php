<?php

declare(strict_types=1);

namespace Tests\Unit\References\Services;

use DomainException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\References\Application\DTOs\CreateReferenceDTO;
use Source\References\Application\DTOs\UpdateReferenceDTO;
use Source\References\Application\Services\ReferenceService;
use Source\References\Domain\Entity\ReferenceEntity;
use Source\References\Domain\Repository\ReferenceRepository;
use Source\References\Domain\ValueObjects\CreateReference;

class ReferenceServiceTest extends TestCase
{
    /** @var ReferenceRepository&Mockery\MockInterface */
    private Mockery\MockInterface $repository;

    private ReferenceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ReferenceRepository::class);
        $this->service = new ReferenceService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- createReference ---

    /** @test */
    #[Test]
    public function create_reference_succeeds_and_returns_payload(): void
    {
        // GIVEN
        $dto = CreateReferenceDTO::fromRequest(['name' => 'Acme Corp', 'order' => 1]);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturnUsing(fn (CreateReference $p) => $p);

        // WHEN
        $result = $this->service->createReference($dto);

        // THEN
        $this->assertInstanceOf(CreateReference::class, $result);
        $this->assertSame('Acme Corp', $result->name());
    }

    // --- updateReference ---

    /** @test */
    #[Test]
    public function update_reference_succeeds(): void
    {
        // GIVEN
        $id = Uuid::uuid7()->toString();
        $dto = UpdateReferenceDTO::fromRequest(['id' => $id, 'name' => 'New Corp']);

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($id)
            ->andReturn(Mockery::mock(ReferenceEntity::class));

        $this->repository->shouldReceive('update')->once();

        // WHEN
        $this->service->updateReference($dto);

        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function update_reference_throws_when_not_found(): void
    {
        // GIVEN
        $id = Uuid::uuid7()->toString();
        $dto = UpdateReferenceDTO::fromRequest(['id' => $id, 'name' => 'New Corp']);

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($id)
            ->andReturn(null);

        $this->repository->shouldNotReceive('update');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Reference not found.');

        // WHEN
        $this->service->updateReference($dto);
    }

    // --- deleteReference ---

    /** @test */
    #[Test]
    public function delete_reference_succeeds(): void
    {
        // GIVEN
        $id = Uuid::uuid7()->toString();

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($id)
            ->andReturn(Mockery::mock(ReferenceEntity::class));

        $this->repository->shouldReceive('delete')->once()->with($id);

        // WHEN
        $this->service->deleteReference($id);

        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function delete_reference_throws_when_not_found(): void
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
        $this->expectExceptionMessage('Reference not found.');

        // WHEN
        $this->service->deleteReference($id);
    }
}
