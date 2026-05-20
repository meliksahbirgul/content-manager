<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $user = DB::table('users')->where('email', 'admin@test.com')->first();

        if (! $user) {
            return;
        }

        $adminRole = DB::table('roles')->where('name', 'admin')->first();

        if (! $adminRole) {
            return;
        }

        DB::table('user_role')->insertOrIgnore([
            'user_id' => $user->id,
            'role_id' => $adminRole->id,
        ]);

        $permissionIds = DB::table('permissions')->pluck('id');

        $rows = $permissionIds->map(fn ($pid) => [
            'user_id'       => $user->id,
            'permission_id' => $pid,
            'granted'       => true,
        ])->all();

        DB::table('user_permission')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        $user = DB::table('users')->where('email', 'admin@test.com')->first();

        if (! $user) {
            return;
        }

        DB::table('user_role')->where('user_id', $user->id)->delete();
        DB::table('user_permission')->where('user_id', $user->id)->delete();
    }
};
