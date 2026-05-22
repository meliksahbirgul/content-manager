<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Services;

use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Source\Dashboard\Application\Service\DashboardService;
use Source\Dashboard\Domain\Entity\ActivityLogEntity;
use Source\Dashboard\Domain\Entity\DashboardEntity;
use Source\Dashboard\Domain\Entity\PageStatusCountEntity;
use Source\Dashboard\Domain\Repository\DashboardRepository;

class DashboardServiceTest extends TestCase
{
    /** @var DashboardRepository&Mockery\MockInterface */
    private Mockery\MockInterface $repositoryMock;

    private DashboardService $dashboardService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock   = Mockery::mock(DashboardRepository::class);
        $this->dashboardService = new DashboardService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    #[Test]
    public function shouldReturnDashboardEntityWithDefaultActivityLimit(): void
    {
        // GIVEN: Repository returns status counts and activity logs
        $statusCounts = [
            new PageStatusCountEntity(status: 'active', count: 5),
            new PageStatusCountEntity(status: 'passive', count: 2),
        ];
        $activityLogs = [
            new ActivityLogEntity(
                id: 1,
                logName: 'default',
                description: 'Page created',
                event: 'created',
                properties: [],
                causerId: 10,
                createdAt: new DateTimeImmutable(),
            ),
        ];

        $this->repositoryMock
            ->shouldReceive('getPageStatusCounts')
            ->once()
            ->andReturn($statusCounts);

        $this->repositoryMock
            ->shouldReceive('getRecentActivityLogs')
            ->once()
            ->with(10)
            ->andReturn($activityLogs);

        // WHEN: Calling getDashboard with the default limit
        $result = $this->dashboardService->getDashboard();

        // THEN: Should return a DashboardEntity containing the repository data
        $this->assertInstanceOf(DashboardEntity::class, $result);
        $this->assertSame($statusCounts, $result->pageStatusCounts());
        $this->assertSame($activityLogs, $result->recentActivityLogs());
    }

    /** @test */
    #[Test]
    public function shouldPassCustomActivityLimitToRepository(): void
    {
        // GIVEN: A custom activity limit
        $customLimit = 25;

        $this->repositoryMock
            ->shouldReceive('getPageStatusCounts')
            ->once()
            ->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('getRecentActivityLogs')
            ->once()
            ->with($customLimit)
            ->andReturn([]);

        // WHEN: Calling getDashboard with a custom limit
        $result = $this->dashboardService->getDashboard($customLimit);

        // THEN: Should return a valid DashboardEntity
        $this->assertInstanceOf(DashboardEntity::class, $result);
    }

    /** @test */
    #[Test]
    public function shouldReturnDashboardEntityWithEmptyCollections(): void
    {
        // GIVEN: Repository returns empty arrays (no pages, no activity)
        $this->repositoryMock
            ->shouldReceive('getPageStatusCounts')
            ->once()
            ->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('getRecentActivityLogs')
            ->once()
            ->with(10)
            ->andReturn([]);

        // WHEN: Calling getDashboard
        $result = $this->dashboardService->getDashboard();

        // THEN: Should return a DashboardEntity with empty collections
        $this->assertInstanceOf(DashboardEntity::class, $result);
        $this->assertEmpty($result->pageStatusCounts());
        $this->assertEmpty($result->recentActivityLogs());
    }

    /** @test */
    #[Test]
    public function shouldCallBothRepositoryMethodsOnEachInvocation(): void
    {
        // GIVEN: Two separate getDashboard calls
        $this->repositoryMock
            ->shouldReceive('getPageStatusCounts')
            ->twice()
            ->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('getRecentActivityLogs')
            ->twice()
            ->with(10)
            ->andReturn([]);

        // WHEN: Calling getDashboard twice
        $this->dashboardService->getDashboard();
        $this->dashboardService->getDashboard();

        // THEN: Mockery verifies both repository methods were called twice
        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function shouldVerifyRepositoryCallOrder(): void
    {
        // GIVEN: Ordered mock expectations
        $this->repositoryMock
            ->shouldReceive('getPageStatusCounts')
            ->once()
            ->ordered()
            ->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('getRecentActivityLogs')
            ->once()
            ->ordered()
            ->with(10)
            ->andReturn([]);

        // WHEN: Calling getDashboard
        $this->dashboardService->getDashboard();

        // THEN: Mockery verifies the call order
        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function shouldPropagateRepositoryExceptionFromGetPageStatusCounts(): void
    {
        // GIVEN: Repository throws during getPageStatusCounts
        $this->repositoryMock
            ->shouldReceive('getPageStatusCounts')
            ->once()
            ->andThrow(new RuntimeException('Database connection lost'));

        $this->repositoryMock
            ->shouldNotReceive('getRecentActivityLogs');

        // THEN: Exception should propagate to the caller
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Database connection lost');

        // WHEN: Calling getDashboard
        $this->dashboardService->getDashboard();
    }

    /** @test */
    #[Test]
    public function shouldPropagateRepositoryExceptionFromGetRecentActivityLogs(): void
    {
        // GIVEN: getPageStatusCounts succeeds but getRecentActivityLogs throws
        $this->repositoryMock
            ->shouldReceive('getPageStatusCounts')
            ->once()
            ->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('getRecentActivityLogs')
            ->once()
            ->andThrow(new RuntimeException('Query timeout'));

        // THEN: Exception should propagate to the caller
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Query timeout');

        // WHEN: Calling getDashboard
        $this->dashboardService->getDashboard();
    }

    /** @test */
    #[Test]
    public function shouldReturnDashboardWithMultipleStatusCounts(): void
    {
        // GIVEN: Multiple page status counts from the repository
        $statusCounts = [
            new PageStatusCountEntity(status: 'active', count: 100),
            new PageStatusCountEntity(status: 'passive', count: 50),
            new PageStatusCountEntity(status: 'draft', count: 0),
        ];

        $this->repositoryMock
            ->shouldReceive('getPageStatusCounts')
            ->once()
            ->andReturn($statusCounts);

        $this->repositoryMock
            ->shouldReceive('getRecentActivityLogs')
            ->once()
            ->with(10)
            ->andReturn([]);

        // WHEN: Calling getDashboard
        $result = $this->dashboardService->getDashboard();

        // THEN: All three status counts should be present in the entity
        $this->assertCount(3, $result->pageStatusCounts());
        $this->assertSame('active', $result->pageStatusCounts()[0]->status());
        $this->assertSame(100, $result->pageStatusCounts()[0]->count());
        $this->assertSame(0, $result->pageStatusCounts()[2]->count());
    }

    /** @test */
    #[Test]
    public function shouldReturnDashboardWithMaxActivityLogs(): void
    {
        // GIVEN: 10 activity log entries (the default limit)
        $activityLogs = array_map(
            fn(int $i) => new ActivityLogEntity(
                id: $i,
                logName: 'default',
                description: "Event $i",
                event: 'updated',
                properties: [],
                causerId: 1,
                createdAt: new DateTimeImmutable(),
            ),
            range(1, 10),
        );

        $this->repositoryMock
            ->shouldReceive('getPageStatusCounts')
            ->once()
            ->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('getRecentActivityLogs')
            ->once()
            ->with(10)
            ->andReturn($activityLogs);

        // WHEN: Calling getDashboard
        $result = $this->dashboardService->getDashboard();

        // THEN: All 10 logs should be returned
        $this->assertCount(10, $result->recentActivityLogs());
    }

    /** @test */
    #[Test]
    public function shouldPassLimitOneToRepository(): void
    {
        // GIVEN: A limit of 1 (minimum meaningful value)
        $this->repositoryMock
            ->shouldReceive('getPageStatusCounts')
            ->once()
            ->andReturn([]);

        $this->repositoryMock
            ->shouldReceive('getRecentActivityLogs')
            ->once()
            ->with(1)
            ->andReturn([]);

        // WHEN: Calling getDashboard with limit 1
        $result = $this->dashboardService->getDashboard(1);

        // THEN: Should return a valid DashboardEntity
        $this->assertInstanceOf(DashboardEntity::class, $result);
    }
}
