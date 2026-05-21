<?php

declare(strict_types=1);

namespace Source\Dashboard\Application\Service;

use Source\Dashboard\Domain\Entity\DashboardEntity;
use Source\Dashboard\Domain\Repository\DashboardRepository;

class DashboardService
{
    public function __construct(
        private DashboardRepository $repository,
    ) {}

    public function getDashboard(int $activityLimit = 10): DashboardEntity
    {
        return new DashboardEntity(
            pageStatusCounts: $this->repository->getPageStatusCounts(),
            recentActivityLogs: $this->repository->getRecentActivityLogs($activityLimit),
        );
    }
}
