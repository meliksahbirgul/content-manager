<?php

declare(strict_types=1);

namespace Source\Dashboard\Domain\Repository;

use Source\Dashboard\Domain\Entity\ActivityLogEntity;
use Source\Dashboard\Domain\Entity\PageStatusCountEntity;

interface DashboardRepository
{
    /**
     * Returns a count entry for every page status, including statuses with zero pages.
     *
     * @return array<int, PageStatusCountEntity>
     */
    public function getPageStatusCounts(): array;

    /**
     * Returns the most recent activity log entries, newest first.
     *
     * @return array<int, ActivityLogEntity>
     */
    public function getRecentActivityLogs(int $limit = 10): array;
}
