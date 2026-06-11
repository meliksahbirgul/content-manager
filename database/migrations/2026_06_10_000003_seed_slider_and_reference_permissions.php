<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'sliders.view'      => 'View Sliders',
            'sliders.create'    => 'Create Sliders',
            'sliders.update'    => 'Update Sliders',
            'sliders.delete'    => 'Delete Sliders',
            'references.view'   => 'View References',
            'references.create' => 'Create References',
            'references.update' => 'Update References',
            'references.delete' => 'Delete References',
        ];

        foreach ($permissions as $name => $displayName) {
            DB::table('permissions')->insertOrIgnore([
                'uuid'         => Uuid::uuid7()->toString(),
                'name'         => $name,
                'display_name' => $displayName,
            ]);
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_keys($permissions))
            ->pluck('id', 'name');

        $rolePermissions = [
            'admin' => array_keys($permissions),
            'editor' => [
                'sliders.view', 'sliders.create', 'sliders.update',
                'references.view', 'references.create', 'references.update',
            ],
            'viewer' => ['sliders.view', 'references.view'],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');

            if ($roleId === null) {
                continue;
            }

            $pivotRows = array_map(
                fn (string $p) => ['role_id' => $roleId, 'permission_id' => $permissionIds[$p]],
                $perms,
            );

            DB::table('role_permission')->insertOrIgnore($pivotRows);
        }
    }

    public function down(): void
    {
        $names = [
            'sliders.view', 'sliders.create', 'sliders.update', 'sliders.delete',
            'references.view', 'references.create', 'references.update', 'references.delete',
        ];

        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');

        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};
