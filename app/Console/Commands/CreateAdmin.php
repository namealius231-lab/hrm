<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:admin {--email=admin} {--name=Admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user with email "admin" and password "admin"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $name = $this->option('name');
        $password = 'admin';

        // Check if admin user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("A user with email '{$email}' already exists!");
            return 1;
        }

        // Get or create company role
        $companyRole = Role::firstOrCreate(
            ['name' => 'company'],
            ['created_by' => 0]
        );

        // If role is new, give it all permissions (or you can assign specific permissions)
        if ($companyRole->wasRecentlyCreated) {
            $this->info('Company role created. Assigning all permissions...');
            // Get all permissions
            $allPermissions = \Spatie\Permission\Models\Permission::all();
            $companyRole->givePermissionTo($allPermissions);
        }

        // Create admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'type' => 'company',
            'lang' => 'en',
            'avatar' => '',
            'email_verified_at' => now(),
            'created_by' => 0,
        ]);

        // Assign role
        $user->assignRole($companyRole);

        $this->info('Admin user created successfully!');
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");
        $this->warn('Please change the password after first login!');

        return 0;
    }
}

