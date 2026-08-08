<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Superadmin',
            'email' => 'superadmin@buyer-tracker.test',
            'password' => bcrypt('ganti-password-ini'),
            'role' => UserRole::Superadmin,
        ]);
    }
}