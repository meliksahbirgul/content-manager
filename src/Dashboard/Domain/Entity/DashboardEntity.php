<?php

declare(strict_types=1);

namespace Source\Dashboard\Domain\Entity;

class DashboardEntity
{
    /**
     * @param array<int, PageStatusCountEntity> $pageStatusCounts
     * @param array<int, ActivityLogEntity>     $recentActivityLogs
     */
    public function __construct(
        private array $pageStatusCounts,
        private array $recentActivityLogs,
    ) {}

    /** @return array<int, PageStatusCountEntity> */
    public function pageStatusCounts(): array
    {
        return $this->pageStatusCounts;
    }

    /** @return array<int, ActivityLogEntity> */
    public function recentActivityLogs(): array
    {
        return $this->recentActivityLogs;
    }
}
