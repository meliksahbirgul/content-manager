<?php

declare(strict_types=1);

namespace Source\Pages\Infrastructure\Logging;

use Source\Pages\Application\Contracts\ActivityLogger;

class PageActivityLogger implements ActivityLogger
{
    public function logPageCreated(string $uuid, string $status): void
    {
        activity('pages')
            ->withProperties(['uuid' => $uuid, 'status' => $status])
            ->log('page.created');
    }

    public function logPageStatusChanged(string $uuid, string $oldStatus, string $newStatus): void
    {
        activity('pages')
            ->withProperties([
                'uuid'       => $uuid,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ])
            ->log('page.status_changed');
    }
}
