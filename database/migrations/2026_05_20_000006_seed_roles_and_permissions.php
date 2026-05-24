<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'pages.view' => 'View Pages',
            'pages.create' => 'Create Pages',
            'pages.update' => 'Update Pages',
            'pages.delete' => 'Delete Pages',
        ];

        foreach ($permissions as $name => $displayName) {
            DB::table('permissions')->insertOrIgnore([
                'uuid' => Uuid::uuid7()->toString(),
                'name' => $name,
                'display_name' => $displayName,
            ]);
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_keys($permissions))
            ->pluck('id', 'name');

        $roles = [
            'admin' => [
                'display_name' => 'Administrator',
                'description' => 'Full access to all resources.',
                'permissions' => ['pages.view', 'pages.create', 'pages.update', 'pages.delete'],
            ],
            'editor' => [
                'display_name' => 'Editor',
                'description' => 'Can create and edit pages.',
                'permissions' => ['pages.view', 'pages.create', 'pages.update'],
            ],
            'viewer' => [
                'display_name' => 'Viewer',
                'description' => 'Read-only access to pages.',
                'permissions' => ['pages.view'],
            ],
        ];

        foreach ($roles as $name => $data) {
            DB::table('roles')->insertOrIgnore([
                'uuid' => Uuid::uuid7()->toString(),
                'name' => $name,
                'display_name' => $data['display_name'],
                'description' => $data['description'],
            ]);

            $roleId = DB::table('roles')->where('name', $name)->value('id');

            $pivotRows = array_map(
                fn (string $p) => ['role_id' => $roleId, 'permission_id' => $permissionIds[$p]],
                $data['permissions'],
            );

            DB::table('role_permission')->insertOrIgnore($pivotRows);
        }
    }

    public function down(): void
    {
        DB::table('role_permission')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
    }
};
