<?php

declare(strict_types=1);

namespace Source\Roles\Infrastructure\Persistence;

use Source\Roles\Domain\Entity\RoleEntity;
use Source\Roles\Domain\Models\Role;
use Source\Roles\Domain\Repository\RoleRepository;
use Source\Roles\Domain\ValueObjects\AssignRole;
use Source\Users\Domain\Models\User;

class EloquentRoleRepository implements RoleRepository
{
    public function findByUuid(string $uuid): ?RoleEntity
    {
        $role = Role::where('uuid', $uuid)->first();

        if (! $role) {
            return null;
        }

        return $this->mapToEntity($role);
    }

    public function findByName(string $name): ?RoleEntity
    {
        $role = Role::where('name', $name)->first();
        if (! $role) {
            return null;
        }

        return $this->mapToEntity($role);
    }

    /** @return list<RoleEntity> */
    public function all(): array
    {
        return array_values(
            Role::all()
                ->map(fn (Role $r) => $this->mapToEntity($r))
                ->all()
        );
    }

    public function assignToUser(AssignRole $payload): void
    {
        $user = User::where('uuid', $payload->userUuid())->firstOrFail();
        $role = Role::where('uuid', $payload->roleUuid())->firstOrFail();

        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    public function removeFromUser(AssignRole $payload): void
    {
        $user = User::where('uuid', $payload->userUuid())->firstOrFail();
        $role = Role::where('uuid', $payload->roleUuid())->firstOrFail();

        $user->roles()->detach($role->id);
    }

    /** @return list<RoleEntity> */
    public function getByUserUuid(string $userUuid): array
    {
        $user = User::where('uuid', $userUuid)->firstOrFail();

        return array_values(
            $user->roles()
                ->get()
                ->map(fn (Role $r) => $this->mapToEntity($r))
                ->all()
        );
    }

    private function mapToEntity(Role $role): RoleEntity
    {
        return new RoleEntity(
            uuid: $role->uuid,
            name: $role->name,
            displayName: $role->display_name,
            description: $role->description,
        );
    }
}
