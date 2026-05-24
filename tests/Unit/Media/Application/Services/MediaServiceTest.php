<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Application\Services;

use DomainException;
use Illuminate\Http\UploadedFile;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Media\Application\Contracts\StorageDriver;
use Source\Media\Application\DTOs\MediaResponseDTO;
use Source\Media\Application\DTOs\UploadMediaDTO;
use Source\Media\Application\Services\MediaService;
use Source\Media\Domain\Entity\MediaEntity;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;
use Source\Media\Domain\Repository\MediaRepository;
use Source\Media\Domain\ValueObjects\UploadMedia;

class MediaServiceTest extends TestCase
{
    /** @var MediaRepository&Mockery\MockInterface */
    private Mockery\MockInterface $repository;

    /** @var StorageDriver&Mockery\MockInterface */
    private Mockery\MockInterface $storageDriver;

    private MediaService $service;

    private string $mediaUuid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(MediaRepository::class);
        $this->storageDriver = Mockery::mock(StorageDriver::class);
        $this->service = new MediaService($this->repository, $this->storageDriver);
        $this->mediaUuid = Uuid::uuid7()->toString();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeEntity(string $uuid): MediaEntity
    {
        return new MediaEntity(
            uuid: $uuid,
            mediableType: 'Source\\Pages\\Domain\\Models\\Page',
            mediableId: 42,
            collection: MediaCollection::Images,
            disk: MediaDisk::Public,
            path: 'page/42/images/'.$uuid.'.jpg',
            url: '/storage/page/42/images/'.$uuid.'.jpg',
            originalName: 'photo.jpg',
            mimeType: 'image/jpeg',
            size: 1024,
        );
    }

    /** @test */
    #[Test]
    public function upload_should_call_driver_then_repository_in_order(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');
        $dto = new UploadMediaDTO(
            mediableType: 'Source\\Pages\\Domain\\Models\\Page',
            mediableId: 42,
            file: $file,
            collection: MediaCollection::Images,
            disk: MediaDisk::Public,
        );

        $expectedPath = 'page/42/images/some-uuid.jpg';
        $expectedUrl = '/storage/'.$expectedPath;
        $entity = $this->makeEntity($this->mediaUuid);

        $this->storageDriver
            ->shouldReceive('storeAs')
            ->once()
            ->with(Mockery::type(UploadedFile::class), 'page/42/images', Mockery::type('string'))
            ->andReturn($expectedPath);

        $this->storageDriver
            ->shouldReceive('url')
            ->once()
            ->with($expectedPath)
            ->andReturn($expectedUrl);

        $this->repository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::type(UploadMedia::class))
            ->andReturn($entity);

        $result = $this->service->upload($dto);

        $this->assertInstanceOf(MediaResponseDTO::class, $result);
    }

    /** @test */
    #[Test]
    public function upload_should_derive_model_slug_from_fqcn(): void
    {
        $file = UploadedFile::fake()->image('img.png');
        $dto = new UploadMediaDTO(
            mediableType: 'Source\\Pages\\Domain\\Models\\Page',
            mediableId: 7,
            file: $file,
            collection: MediaCollection::Default,
            disk: MediaDisk::Public,
        );

        $entity = $this->makeEntity($this->mediaUuid);

        $this->storageDriver
            ->shouldReceive('storeAs')
            ->once()
            ->with(Mockery::any(), 'page/7/default', Mockery::any())
            ->andReturn('page/7/default/uuid.png');

        $this->storageDriver->shouldReceive('url')->once()->andReturn('/storage/page/7/default/uuid.png');
        $this->repository->shouldReceive('save')->once()->andReturn($entity);

        $this->service->upload($dto);

        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function delete_should_call_driver_then_repository(): void
    {
        $uuid = $this->mediaUuid;
        $entity = $this->makeEntity($uuid);

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andReturn($entity);

        $this->storageDriver
            ->shouldReceive('delete')
            ->once()
            ->with($entity->path());

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with($uuid);

        $this->service->delete($uuid);

        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function delete_should_throw_domain_exception_when_uuid_not_found(): void
    {
        $uuid = Uuid::uuid7()->toString();

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(null);

        $this->storageDriver->shouldNotReceive('delete');
        $this->repository->shouldNotReceive('delete');

        $this->expectException(DomainException::class);

        $this->service->delete($uuid);
    }

    /** @test */
    #[Test]
    public function for_model_should_map_entities_to_dt_os(): void
    {
        $entities = [
            $this->makeEntity(Uuid::uuid7()->toString()),
            $this->makeEntity(Uuid::uuid7()->toString()),
        ];

        $this->repository
            ->shouldReceive('findForModel')
            ->once()
            ->with('Source\\Pages\\Domain\\Models\\Page', 42, null)
            ->andReturn($entities);

        $result = $this->service->forModel('Source\\Pages\\Domain\\Models\\Page', 42);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(MediaResponseDTO::class, $result);
    }

    /** @test */
    #[Test]
    public function for_model_should_pass_collection_filter_to_repository(): void
    {
        $this->repository
            ->shouldReceive('findForModel')
            ->once()
            ->with(Mockery::any(), Mockery::any(), MediaCollection::Images)
            ->andReturn([]);

        $result = $this->service->forModel('Source\\Pages\\Domain\\Models\\Page', 1, MediaCollection::Images);

        $this->assertSame([], $result);
    }

    /** @test */
    #[Test]
    public function delete_call_order_should_be_driver_before_repository(): void
    {
        $uuid = $this->mediaUuid;
        $entity = $this->makeEntity($uuid);
        $order = [];

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andReturn($entity);

        $this->storageDriver
            ->shouldReceive('delete')
            ->once()
            ->ordered()
            ->andReturnUsing(function () use (&$order) {
                $order[] = 'driver';
            });

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->ordered()
            ->andReturnUsing(function () use (&$order) {
                $order[] = 'repository';
            });

        $this->service->delete($uuid);

        $this->assertSame(['driver', 'repository'], $order);
    }
}
