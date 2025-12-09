<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class UpdateSuperAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:super-admin-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all super admin users to have all permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get or create super admin role
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super admin'],
            ['created_by' => 0]
        );

        // Get all permissions
        $allPermissions = \Spatie\Permission\Models\Permission::all();
        
        // Sync all permissions to super admin role
        $this->info('Syncing all permissions to super admin role...');
        $superAdminRole->syncPermissions($allPermissions);
        $this->info("Assigned {$allPermissions->count()} permissions to super admin role.");

        // Update all existing super admin users
        $existingSuperAdmins = User::where('type', 'super admin')->get();
        
        if ($existingSuperAdmins->isEmpty()) {
            $this->warn('No super admin users found.');
            return 0;
        }

        $this->info("Found {$existingSuperAdmins->count()} super admin user(s). Updating permissions...");
        
        foreach ($existingSuperAdmins as $admin) {
            if (!$admin->hasRole($superAdminRole)) {
                $admin->assignRole($superAdminRole);
                $this->line("  - Assigned role to: {$admin->email}");
            } else {
                $this->line("  - Role already assigned to: {$admin->email}");
            }
        }

        $this->info('All super admin users now have all permissions!');
        $this->warn('Please log out and log back in to see the updated menu options.');

        return 0;
    }
}

