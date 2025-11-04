<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Calls specific seeders.
     */
    public function run(): void
    {
        // Call the AdminUserSeeder to create the initial admin account
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Only run TestDataSeeder in local/development environments
        if (app()->environment('local', 'development', 'testing')) {
            $this->call([
                TestGameSeeder::class, // Call the test game seeder
                // TestParticipationSeeder::class, // Optionally seed participations
            ]);
        }
    }
}