<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddBillViewAnyPermission extends Migration
{
    /**
     * Adds a new "Bill View Any" permission that lets specific users see
     * every bill regardless of creator, assigned department or status.
     * Granted to Super Admin by default.
     */
    public function up()
    {
        $name = 'Bill View Any';
        $now  = now();

        $exists = DB::table('permissions')->where('name', $name)->first();
        if (!$exists) {
            DB::table('permissions')->insert([
                'name'       => $name,
                'comment'    => 'View every bill in the system, bypassing role-based scoping (creator / department / status).',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permId = DB::table('permissions')->where('name', $name)->value('id');
        if (!$permId) {
            return;
        }

        // Always grant to Super Admin.
        $superAdminId = DB::table('roles')->where('name', 'Super Admin')->value('id');
        if ($superAdminId) {
            DB::table('role_has_permissions')->updateOrInsert(
                ['role_id' => $superAdminId, 'permission_id' => $permId],
                []
            );
        }

        // Reset the spatie permission cache so the new grant is visible.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        $permId = DB::table('permissions')->where('name', 'Bill View Any')->value('id');
        if ($permId) {
            DB::table('model_has_permissions')->where('permission_id', $permId)->delete();
            DB::table('role_has_permissions')->where('permission_id', $permId)->delete();
            DB::table('permissions')->where('id', $permId)->delete();
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
