<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's users with predefined test accounts.
     *
     * @var array<int, array{name: string, email: string, password: string, role: string}>
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Inventaris',
                'email' => 'admin@inventaris.test',
                'password' => Hash::make('password'),
                'status' => 'approved',
                'role' => 'Admin',
            ],
            [
                'name' => 'Staff Inventaris',
                'email' => 'staff@inventaris.test',
                'password' => Hash::make('password'),
                'status' => 'approved',
                'role' => 'Staff',
            ],
            [
                'name' => 'Manager Inventaris',
                'email' => 'manager@inventaris.test',
                'password' => Hash::make('password'),
                'status' => 'approved',
                'role' => 'Manager',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData,
            );

            $user->syncRoles([$role]);
        }
    }
}
