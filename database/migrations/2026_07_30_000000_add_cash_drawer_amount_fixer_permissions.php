<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSIONS = [
        'CanViewAmountFixer:CashDrawer',
        'CanUseAmountFixer:CashDrawer',
    ];

    public function up(): void
    {
        $permissionTable = (string) config('permission.table_names.permissions', 'permissions');
        $roleTable = (string) config('permission.table_names.roles', 'roles');
        $rolePermissionTable = (string) config('permission.table_names.role_has_permissions', 'role_has_permissions');

        foreach (self::PERMISSIONS as $permission) {
            DB::table($permissionTable)->insertOrIgnore([
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $administratorRoleId = DB::table($roleTable)
            ->where('name', (string) config('filament-shield.super_admin.name'))
            ->where('guard_name', 'web')
            ->value('id');

        if ($administratorRoleId === null) {
            return;
        }

        DB::table($permissionTable)
            ->whereIn('name', self::PERMISSIONS)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->each(function (mixed $permissionId) use ($rolePermissionTable, $administratorRoleId): void {
                DB::table($rolePermissionTable)->insertOrIgnore([
                    'permission_id' => (int) $permissionId,
                    'role_id' => (int) $administratorRoleId,
                ]);
            });
    }

    public function down(): void
    {
        $permissionTable = (string) config('permission.table_names.permissions', 'permissions');

        DB::table($permissionTable)
            ->whereIn('name', self::PERMISSIONS)
            ->where('guard_name', 'web')
            ->delete();
    }
};
