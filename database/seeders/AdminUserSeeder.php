<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // User model
use Illuminate\Support\Facades\Hash; // Hashing facade
use Illuminate\Support\Str; // For UUID

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates the default admin user if it doesn't exist.
     *
     */
    public function run(): void
    {
        // Use firstOrCreate to avoid creating duplicates if seeder runs multiple times
        User::firstOrCreate(
            ['email' => 'admin@yourdomain.com'], // Find by email
            [
                // user_id is set automatically by the model's boot method
                'password' => Hash::make('password'), // Use 'password' as default
                'nickname' => 'かんり', // Default nickname - Ensure it's 4 chars
                // 'email_verified_at' => now(), // Mark as verified if needed
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}