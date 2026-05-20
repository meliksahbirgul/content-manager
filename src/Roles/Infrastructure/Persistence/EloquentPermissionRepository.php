<?php

declare(strict_types=1);

namespace Source\Roles\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;
use Source\Roles\Domain\Entity\PermissionEntity;
use Source\Roles\Domain\Models\Permission;
use Source\Roles\Domain\Repository\PermissionRepository;
use Source\Roles\Domain\ValueObjects\AssignPermission;
use Source\Roles\Domain\ValueObjects\RemovePermission;
use Source\Users\Domain\Models\User;

class EloquentPermissionRepository implements PermissionRepository
{
    public function findByUuid(string $uuid): PermissionEntity|null
    {
        $permission = Permission::where('uuid', $uuid)->first();

        return $permission ? $this->mapToEntity($permission) : null;
    }

    public function findByName(string $name): PermissionEntity|null
    {
        $permission = Permission::where('name', $name)->first();

        return $permission ? $this->mapToEntity($permission) : null;
    }

    /** @return list<PermissionEntity> */
    public function all(): array
    {
        return array_values(
            Permission::all()
                ->map(fn(Permission $p) => $this->mapToEntity($p))
                ->all()
        );
    }

    public function assignToUser(AssignPermission $payload): void
    {
        $user       = User::where('uuid', $payload->userUuid())->firstOrFail();
        $permission = Permission::where('uuid', $payload->permissionUuid())->firstOrFail();

        $user->permissions()->syncWithoutDetaching([
            $permission->id => ['granted' => $payload->granted()],
        ]);
    }

    public function removeFromUser(RemovePermission $payload): void
    {
        $user       = User::where('uuid', $payload->userUuid())->firstOrFail();
        $permission = Permission::where('uuid', $payload->permissionUuid())->firstOrFail();

        $user->permissions()->detach($permission->id);
    }

    /** @return list<PermissionEntity> */
    public function getDirectByUserUuid(string $userUuid): array
    {
        $user = User::where('uuid', $userUuid)->firstOrFail();

        return array_values(
            $user->permissions()
                ->wherePivot('granted', true)
                ->get()
                ->map(fn(Permission $p) => $this->mapToEntity($p))
                ->all()
        );
    }

    /** @return list<PermissionEntity> */
    public function getAllByUserUuid(string $userUuid): array
    {
        $user = User::where('uuid', $userUuid)->firstOrFail();

        $fromRolePermissionIds = $user->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions->pluck('id'))
            ->unique();

        $directRows = DB::table('user_permission')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('permission_id');

        $deniedIds  = $directRows->filter(fn($row) => ! $row->granted)->keys();
        $grantedIds = $directRows->filter(fn($row) => $row->granted)->keys();

        $permissionIds = $fromRolePermissionIds
            ->reject(fn($id) => $deniedIds->contains($id))
            ->merge($grantedIds)
            ->unique()
            ->values();

        return array_values(
            Permission::whereIn('id', $permissionIds->all())
                ->get()
                ->map(fn(Permission $p) => $this->mapToEntity($p))
                ->all()
        );
    }

    private function mapToEntity(Permission $permission): PermissionEntity
    {
        return new PermissionEntity(
            uuid: $permission->uuid,
            name: $permission->name,
            displayName: $permission->display_name,
            description: $permission->description,
        );
    }
}
