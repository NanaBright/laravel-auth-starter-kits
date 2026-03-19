<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now()->subDays(30),
                'last_login_at' => now()->subHours(2),
                'login_count' => 15,
                'created_at' => now()->subDays(30),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now()->subDays(20),
                'last_login_at' => now()->subDay(),
                'login_count' => 8,
                'created_at' => now()->subDays(20),
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'is_admin' => false,
                'is_active' => false,
                'email_verified_at' => now()->subDays(45),
                'last_login_at' => now()->subDays(10),
                'login_count' => 3,
                'created_at' => now()->subDays(45),
            ],
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now()->subDays(60),
                'last_login_at' => now()->subHours(5),
                'login_count' => 42,
                'created_at' => now()->subDays(60),
            ],
            [
                'name' => 'Mike Brown',
                'email' => 'mike@example.com',
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => null,
                'last_login_at' => null,
                'login_count' => 0,
                'created_at' => now()->subDays(2),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['password' => Hash::make('password')])
            );
        }

        // Create demo activity logs
        $admin = User::where('is_admin', true)->first();
        
        $activities = [
            ['action' => 'login', 'description' => 'Admin user logged in'],
            ['action' => 'user_created', 'description' => 'Created user John Doe'],
            ['action' => 'user_updated', 'description' => 'Updated user Jane Smith'],
            ['action' => 'user_deactivated', 'description' => 'Deactivated user Bob Wilson'],
            ['action' => 'login', 'description' => 'Admin user logged in'],
            ['action' => 'users_exported', 'description' => 'Exported users list to CSV'],
        ];

        foreach ($activities as $i => $activity) {
            ActivityLog::create([
                'admin_id' => $admin->id,
                'user_id' => User::inRandomOrder()->first()->id,
                'action' => $activity['action'],
                'description' => $activity['description'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Demo Seeder',
                'created_at' => now()->subDays(count($activities) - $i),
            ]);
        }

        $this->command->info('Demo users and activity logs created.');
    }
}
