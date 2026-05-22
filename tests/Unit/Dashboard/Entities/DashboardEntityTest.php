<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Entities;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Dashboard\Domain\Entity\ActivityLogEntity;
use Source\Dashboard\Domain\Entity\DashboardEntity;
use Source\Dashboard\Domain\Entity\PageStatusCountEntity;

class DashboardEntityTest extends TestCase
{
    private PageStatusCountEntity $activeCount;
    private PageStatusCountEntity $passiveCount;
    private ActivityLogEntity $activityLog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activeCount  = new PageStatusCountEntity(status: 'active', count: 5);
        $this->passiveCount = new PageStatusCountEntity(status: 'passive', count: 2);

        $this->activityLog = new ActivityLogEntity(
            id: 1,
            logName: 'default',
            description: 'Page created',
            event: 'created',
            properties: [],
            causerId: 10,
            createdAt: new DateTimeImmutable('2024-01-15 10:00:00'),
        );
    }

    /** @test */
    #[Test]
    public function shouldCreateInstanceWithPageStatusCountsAndActivityLogs(): void
    {
        // GIVEN: Arrays of status counts and activity logs
        $statusCounts  = [$this->activeCount, $this->passiveCount];
        $activityLogs  = [$this->activityLog];

        // WHEN: Creating DashboardEntity
        $entity = new DashboardEntity(
            pageStatusCounts: $statusCounts,
            recentActivityLogs: $activityLogs,
        );

        // THEN: Should create instance and expose correct data
        $this->assertInstanceOf(DashboardEntity::class, $entity);
        $this->assertSame($statusCounts, $entity->pageStatusCounts());
        $this->assertSame($activityLogs, $entity->recentActivityLogs());
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectPageStatusCounts(): void
    {
        // GIVEN: Two PageStatusCountEntity instances
        $statusCounts = [$this->activeCount, $this->passiveCount];
        $entity = new DashboardEntity(pageStatusCounts: $statusCounts, recentActivityLogs: []);

        // WHEN: Calling pageStatusCounts()
        $result = $entity->pageStatusCounts();

        // THEN: Should return the full array of status counts
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(PageStatusCountEntity::class, $result[0]);
        $this->assertSame('active', $result[0]->status());
        $this->assertSame(5, $result[0]->count());
        $this->assertSame('passive', $result[1]->status());
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectRecentActivityLogs(): void
    {
        // GIVEN: One activity log entry
        $activityLogs = [$this->activityLog];
        $entity = new DashboardEntity(pageStatusCounts: [], recentActivityLogs: $activityLogs);

        // WHEN: Calling recentActivityLogs()
        $result = $entity->recentActivityLogs();

        // THEN: Should return the full array of activity logs
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(ActivityLogEntity::class, $result[0]);
        $this->assertSame(1, $result[0]->id());
        $this->assertSame('Page created', $result[0]->description());
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyPageStatusCounts(): void
    {
        // GIVEN: No page status counts
        $entity = new DashboardEntity(pageStatusCounts: [], recentActivityLogs: [$this->activityLog]);

        // WHEN: Calling pageStatusCounts()
        $result = $entity->pageStatusCounts();

        // THEN: Should return an empty array
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyRecentActivityLogs(): void
    {
        // GIVEN: No activity logs
        $entity = new DashboardEntity(
            pageStatusCounts: [$this->activeCount],
            recentActivityLogs: [],
        );

        // WHEN: Calling recentActivityLogs()
        $result = $entity->recentActivityLogs();

        // THEN: Should return an empty array
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleBothCollectionsEmpty(): void
    {
        // GIVEN: No data at all (e.g. fresh install)
        $entity = new DashboardEntity(pageStatusCounts: [], recentActivityLogs: []);

        // THEN: Both collections should be empty arrays
        $this->assertEmpty($entity->pageStatusCounts());
        $this->assertEmpty($entity->recentActivityLogs());
    }

    /** @test */
    #[Test]
    public function shouldHandleMultipleActivityLogs(): void
    {
        // GIVEN: Multiple activity log entries
        $log2 = new ActivityLogEntity(
            id: 2,
            logName: 'audit',
            description: 'Page deleted',
            event: 'deleted',
            properties: ['id' => 99],
            causerId: null,
            createdAt: new DateTimeImmutable('2024-02-01'),
        );

        $activityLogs = [$this->activityLog, $log2];
        $entity = new DashboardEntity(pageStatusCounts: [], recentActivityLogs: $activityLogs);

        // WHEN: Calling recentActivityLogs()
        $result = $entity->recentActivityLogs();

        // THEN: Should return all logs in order
        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]->id());
        $this->assertSame(2, $result[1]->id());
        $this->assertSame('Page deleted', $result[1]->description());
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectTypesForBothCollections(): void
    {
        // GIVEN: A populated DashboardEntity
        $entity = new DashboardEntity(
            pageStatusCounts: [$this->activeCount],
            recentActivityLogs: [$this->activityLog],
        );

        // THEN: Both getters should return arrays
        $this->assertIsArray($entity->pageStatusCounts());
        $this->assertIsArray($entity->recentActivityLogs());
    }

    /** @test */
    #[Test]
    public function shouldBeImmutableAfterConstruction(): void
    {
        // GIVEN: A DashboardEntity instance
        $statusCounts = [$this->activeCount];
        $entity = new DashboardEntity(pageStatusCounts: $statusCounts, recentActivityLogs: []);

        // WHEN: Accessing the collection twice
        $first  = $entity->pageStatusCounts();
        $second = $entity->pageStatusCounts();

        // THEN: Both calls should return the same array reference
        $this->assertSame($first, $second);
    }
}
