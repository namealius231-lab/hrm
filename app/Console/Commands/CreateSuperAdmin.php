<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:super-admin {--email=superadmin@example.com} {--name=Super Admin} {--update-permissions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user for the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if super admin already exists
        $existingSuperAdmin = User::where('type', 'super admin')->first();
        
        if ($existingSuperAdmin) {
            $this->warn('A super admin user already exists!');
            if (!$this->confirm('Do you want to create another super admin user?')) {
                return 0;
            }
        }

        $email = $this->option('email');
        $name = $this->option('name');

        // Check if email already exists
        if (User::where('email', $email)->exists()) {
            $this->error("A user with email '{$email}' already exists!");
            return 1;
        }

        // Get or create super admin role
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super admin'],
            ['created_by' => 0]
        );

        // Always ensure super admin role has all permissions
        $allPermissions = \Spatie\Permission\Models\Permission::all();
        if ($superAdminRole->wasRecentlyCreated) {
            $this->info('Super admin role created. Assigning all permissions...');
            $superAdminRole->givePermissionTo($allPermissions);
        } else {
            // Sync all permissions to ensure super admin has everything
            $this->info('Syncing all permissions to super admin role...');
            $superAdminRole->syncPermissions($allPermissions);
        }

        // Create super admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('superadmin'),
            'type' => 'super admin',
            'lang' => 'en',
            'avatar' => '',
            'email_verified_at' => now(),
            'created_by' => 0,
        ]);

        // Assign role
        $user->assignRole($superAdminRole);

        // Update permissions for all existing super admin users if requested
        if ($this->option('update-permissions')) {
            $this->info('Updating permissions for all existing super admin users...');
            $existingSuperAdmins = User::where('type', 'super admin')->get();
            foreach ($existingSuperAdmins as $existingAdmin) {
                if (!$existingAdmin->hasRole($superAdminRole)) {
                    $existingAdmin->assignRole($superAdminRole);
                }
            }
            $this->info('All super admin users now have all permissions.');
        }

        $this->info('Super admin user created successfully!');
        $this->line("Email: {$email}");
        $this->line("Password: superadmin");
        $this->warn('Please change the password after first login!');

        return 0;
    }
}
