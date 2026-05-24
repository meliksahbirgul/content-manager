<?php

declare(strict_types=1);

namespace Source\Roles\Domain\Repository;

use Source\Roles\Domain\Entity\RoleEntity;
use Source\Roles\Domain\ValueObjects\AssignRole;

interface RoleRepository
{
    public function findByUuid(string $uuid): ?RoleEntity;

    public function findByName(string $name): ?RoleEntity;

    /** @return list<RoleEntity> */
    public function all(): array;

    public function assignToUser(AssignRole $payload): void;

    public function removeFromUser(AssignRole $payload): void;

    /** @return list<RoleEntity> */
    public function getByUserUuid(string $userUuid): array;
}
