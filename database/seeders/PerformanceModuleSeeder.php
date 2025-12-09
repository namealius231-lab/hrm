<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PerformanceModuleSeeder extends Seeder
{
    /**
     * Seed the performance pulse specific permissions.
     */
    public function run(): void
    {
        $permissions = [
            'Manage Performance Pulse',
            'Manage Org Hierarchy',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $roleAssignments = [
            'company' => $permissions,
            'hr' => $permissions,
            'super admin' => $permissions,
        ];

        foreach ($roleAssignments as $roleName => $permissionList) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissionList);
            }
        }
    }
}

