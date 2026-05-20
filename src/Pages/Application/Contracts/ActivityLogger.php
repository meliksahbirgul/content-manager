<?php

declare(strict_types=1);

namespace Source\Pages\Application\Contracts;

interface ActivityLogger
{
    public function logPageCreated(string $uuid, string $status): void;

    public function logPageStatusChanged(string $uuid, string $oldStatus, string $newStatus): void;
}
