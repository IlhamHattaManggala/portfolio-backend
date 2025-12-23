<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'ilhamhattamanggala123@gmail.com'],
            [
                'name' => 'Ilham Hatta',
                'password' => Hash::make('Ilham311202.'),
                'email_verified_at' => now(),
            ]
        );
    }
}
