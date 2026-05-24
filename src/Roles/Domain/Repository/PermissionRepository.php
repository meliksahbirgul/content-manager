<?php

declare(strict_types=1);

namespace Source\Roles\Domain\Repository;

use Source\Roles\Domain\Entity\PermissionEntity;
use Source\Roles\Domain\ValueObjects\AssignPermission;
use Source\Roles\Domain\ValueObjects\RemovePermission;

interface PermissionRepository
{
    public function findByUuid(string $uuid): ?PermissionEntity;

    public function findByName(string $name): ?PermissionEntity;

    /** @return list<PermissionEntity> */
    public function all(): array;

    public function assignToUser(AssignPermission $payload): void;

    public function removeFromUser(RemovePermission $payload): void;

    /** @return list<PermissionEntity> */
    public function getDirectByUserUuid(string $userUuid): array;

    /** @return list<PermissionEntity> */
    public function getAllByUserUuid(string $userUuid): array;
}
