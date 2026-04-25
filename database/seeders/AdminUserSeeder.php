<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@devlapan.com'],
            [
                'email' => 'admin@devlapan.com',
                'password' => '$2y$12$ExJU0LzNJ1njx8z2CNOAnupbVUm5u5Q.8hbgWuGCN22h4E9M9xQ8i',
                'employee_id' => 50,
                'must_change_password' => false,
                'status' => 'active',
                'failed_attempts' => 0,
            ]
        );
        
        $this->command->info('Admin user created in ULS!');
        $this->command->info('Email: admin@devlapan.com');
        $this->command->info('Password: Admin@123456');
    }
}