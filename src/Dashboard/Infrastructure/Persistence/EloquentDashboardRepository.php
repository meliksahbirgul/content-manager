<?php

declare(strict_types=1);

namespace Source\Dashboard\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Source\Dashboard\Domain\Entity\ActivityLogEntity;
use Source\Dashboard\Domain\Entity\PageStatusCountEntity;
use Source\Dashboard\Domain\Repository\DashboardRepository;
use Source\Pages\Domain\Enums\PageStatus;
use Source\Pages\Domain\Models\Page as EloquentPage;
use Spatie\Activitylog\Models\Activity;

class EloquentDashboardRepository implements DashboardRepository
{
    /** @return array<int, PageStatusCountEntity> */
    public function getPageStatusCounts(): array
    {
        $counts = EloquentPage::query()
            ->select('is_active', DB::raw('count(*) as count'))
            ->groupBy('is_active')
            ->pluck('count', 'is_active')
            ->all();

        return array_map(
            fn (PageStatus $status) => new PageStatusCountEntity(
                status: $status->value,
                count: (int) ($counts[$status->value] ?? 0),
            ),
            PageStatus::cases(),
        );
    }

    /** @return array<int, ActivityLogEntity> */
    public function getRecentActivityLogs(int $limit = 10): array
    {
        return Activity::query()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Activity $activity) => new ActivityLogEntity(
                id: $activity->id,
                logName: $activity->log_name,
                description: $activity->description,
                event: $activity->event,
                properties: ($activity->properties ?? collect())->toArray(),
                causerId: $activity->causer_id,
                createdAt: new DateTimeImmutable($activity->created_at->toDateTimeString()),
            ))
            ->values()
            ->all();
    }
}
