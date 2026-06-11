<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Source\Dashboard\Domain\Entity\ActivityLogEntity;
use Source\Dashboard\Domain\Entity\PageStatusCountEntity;
use Source\Dashboard\Infrastructure\Persistence\EloquentDashboardRepository;
use Source\Pages\Domain\Enums\PageStatus;
use Source\Pages\Domain\Models\Page as EloquentPage;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

#[Group('infrastructure')]
class EloquentDashboardRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentDashboardRepository $repository;

    private int $pageCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentDashboardRepository;
        $this->pageCounter = 0;
    }

    // ─── getPageStatusCounts() ────────────────────────────────────────

    /** @test */
    #[Test]
    public function it_returns_one_entry_per_page_status(): void
    {
        // Act
        $result = $this->repository->getPageStatusCounts();

        // Assert
        $this->assertCount(count(PageStatus::cases()), $result);
        foreach ($result as $entry) {
            $this->assertInstanceOf(PageStatusCountEntity::class, $entry);
        }
    }

    /** @test */
    #[Test]
    public function it_returns_zero_counts_when_no_pages_exist(): void
    {
        // Act
        $result = $this->repository->getPageStatusCounts();

        // Assert
        foreach ($result as $entry) {
            $this->assertSame(0, $entry->count());
        }
    }

    /** @test */
    #[Test]
    public function it_counts_active_pages_correctly(): void
    {
        // Arrange
        $this->createPage(PageStatus::ACTIVE);
        $this->createPage(PageStatus::ACTIVE);

        // Act
        $result = $this->repository->getPageStatusCounts();

        // Assert
        $this->assertSame(2, $this->findStatusEntry($result, 'active')->count());
    }

    /** @test */
    #[Test]
    public function it_counts_passive_pages_correctly(): void
    {
        // Arrange
        $this->createPage(PageStatus::PASSIVE);

        // Act
        $result = $this->repository->getPageStatusCounts();

        // Assert
        $this->assertSame(1, $this->findStatusEntry($result, 'passive')->count());
    }

    /** @test */
    #[Test]
    public function it_counts_each_status_independently(): void
    {
        // Arrange
        $this->createPage(PageStatus::ACTIVE);
        $this->createPage(PageStatus::ACTIVE);
        $this->createPage(PageStatus::ACTIVE);
        $this->createPage(PageStatus::PASSIVE);

        // Act
        $result = $this->repository->getPageStatusCounts();

        // Assert
        $this->assertSame(3, $this->findStatusEntry($result, 'active')->count());
        $this->assertSame(1, $this->findStatusEntry($result, 'passive')->count());
    }

    /** @test */
    #[Test]
    public function it_returns_zero_for_statuses_with_no_matching_pages(): void
    {
        // Arrange – only active pages exist
        $this->createPage(PageStatus::ACTIVE);

        // Act
        $result = $this->repository->getPageStatusCounts();

        // Assert – passive must still appear with count 0
        $this->assertSame(0, $this->findStatusEntry($result, 'passive')->count());
    }

    /** @test */
    #[Test]
    public function it_includes_the_status_value_string_in_each_entry(): void
    {
        // Act
        $result = $this->repository->getPageStatusCounts();

        // Assert
        $statuses = array_map(fn (PageStatusCountEntity $e) => $e->status(), $result);
        foreach (PageStatus::cases() as $case) {
            $this->assertContains($case->value, $statuses);
        }
    }

    // ─── getRecentActivityLogs() ──────────────────────────────────────

    /** @test */
    #[Test]
    public function it_returns_empty_array_when_no_activity_logs_exist(): void
    {
        // Act
        $result = $this->repository->getRecentActivityLogs();

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function it_returns_activity_log_entities(): void
    {
        // Arrange
        $this->createActivity();

        // Act
        $result = $this->repository->getRecentActivityLogs();

        // Assert
        $this->assertCount(1, $result);
        $this->assertInstanceOf(ActivityLogEntity::class, $result[0]);
    }

    /** @test */
    #[Test]
    public function it_maps_all_fields_to_activity_log_entity(): void
    {
        // Arrange
        Activity::create([
            'log_name' => 'audit',
            'description' => 'Page created',
            'event' => 'created',
            'properties' => ['key' => 'value'],
            'causer_id' => 42,
            'causer_type' => null,
            'subject_id' => null,
            'subject_type' => null,
        ]);

        // Act
        $result = $this->repository->getRecentActivityLogs();

        // Assert
        $entity = $result[0];
        $this->assertSame('audit', $entity->logName());
        $this->assertSame('Page created', $entity->description());
        $this->assertSame('created', $entity->event());
        $this->assertSame(['key' => 'value'], $entity->properties());
        $this->assertSame(42, $entity->causerId());
    }

    /** @test */
    #[Test]
    public function it_handles_nullable_fields(): void
    {
        // Arrange
        Activity::create([
            'log_name' => null,
            'description' => 'anonymous action',
            'event' => null,
            'properties' => [],
            'causer_id' => null,
            'causer_type' => null,
            'subject_id' => null,
            'subject_type' => null,
        ]);

        // Act
        $result = $this->repository->getRecentActivityLogs();

        // Assert
        $entity = $result[0];
        $this->assertNull($entity->logName());
        $this->assertNull($entity->event());
        $this->assertNull($entity->causerId());
    }

    /** @test */
    #[Test]
    public function it_returns_logs_ordered_newest_first(): void
    {
        // Arrange
        $this->createActivity(['description' => 'old', 'created_at' => now()->subHours(2)]);
        $this->createActivity(['description' => 'mid', 'created_at' => now()->subHour()]);
        $this->createActivity(['description' => 'new', 'created_at' => now()]);

        // Act
        $result = $this->repository->getRecentActivityLogs();

        // Assert
        $this->assertSame('new', $result[0]->description());
        $this->assertSame('mid', $result[1]->description());
        $this->assertSame('old', $result[2]->description());
    }

    /** @test */
    #[Test]
    public function it_respects_the_limit_parameter(): void
    {
        // Arrange
        for ($i = 1; $i <= 5; $i++) {
            $this->createActivity(['description' => "event $i"]);
        }

        // Act
        $result = $this->repository->getRecentActivityLogs(limit: 3);

        // Assert
        $this->assertCount(3, $result);
    }

    /** @test */
    #[Test]
    public function it_uses_default_limit_of_ten(): void
    {
        // Arrange
        for ($i = 1; $i <= 15; $i++) {
            $this->createActivity(['description' => "event $i"]);
        }

        // Act
        $result = $this->repository->getRecentActivityLogs();

        // Assert
        $this->assertCount(10, $result);
    }

    /** @test */
    #[Test]
    public function it_maps_created_at_to_date_time_immutable(): void
    {
        // Arrange
        $this->createActivity();

        // Act
        $result = $this->repository->getRecentActivityLogs();

        // Assert
        $this->assertInstanceOf(DateTimeImmutable::class, $result[0]->createdAt());
    }

    /** @test */
    #[Test]
    public function it_preserves_created_at_timestamp(): void
    {
        // Arrange
        $timestamp = now()->startOfMinute();
        $this->createActivity(['created_at' => $timestamp]);

        // Act
        $result = $this->repository->getRecentActivityLogs();

        // Assert
        $this->assertSame(
            $timestamp->toDateTimeString(),
            $result[0]->createdAt()->format('Y-m-d H:i:s'),
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    private function createPage(PageStatus $status = PageStatus::PASSIVE): EloquentPage
    {
        $this->pageCounter++;

        return EloquentPage::create([
            'uuid' => Uuid::uuid7()->toString(),
            'title' => ['en' => "Page {$this->pageCounter}"],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => "page-{$this->pageCounter}"],
            'is_active' => $status->value,
            'order' => $this->pageCounter,
            'parent_id' => null,
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function createActivity(array $overrides = []): Activity
    {
        return Activity::create(array_merge([
            'log_name' => 'default',
            'description' => 'test action',
            'event' => 'updated',
            'properties' => [],
            'causer_id' => null,
            'causer_type' => null,
            'subject_id' => null,
            'subject_type' => null,
        ], $overrides));
    }

    /**
     * @param  array<int, PageStatusCountEntity>  $entries
     */
    private function findStatusEntry(array $entries, string $status): PageStatusCountEntity
    {
        foreach ($entries as $entry) {
            if ($entry->status() === $status) {
                return $entry;
            }
        }

        $this->fail("No PageStatusCountEntity found with status '$status'.");
    }
}
