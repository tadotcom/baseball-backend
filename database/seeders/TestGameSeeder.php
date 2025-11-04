<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Game; // Game model
use Illuminate\Support\Facades\DB; // If using raw queries or truncating
use Illuminate\Support\Carbon; // For date manipulation


class TestGameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates example game data for development/testing.
     */
    public function run(): void
    {
        // Optional: Clear existing games before seeding
        // DB::table('games')->truncate(); // Careful with truncate in production!

        // --- Create Specific Test Game ---
        Game::firstOrCreate(
            ['place_name' => 'テスト球場', 'prefecture' => '東京都'], // Find criteria
            [
                 // game_id is set automatically by model
                 'game_date_time' => Carbon::now()->addDays(7)->setTime(14, 0, 0), // 7 days from now at 14:00
                 'address' => '東京都新宿区西新宿2-8-1',
                 'latitude' => 35.689614,
                 'longitude' => 139.691648,
                 'acceptable_radius' => 500,
                 'status' => '募集中',
                 'fee' => 1000,
                 'capacity' => 18,
                 'created_at' => now(),
                 'updated_at' => now(),
            ]
        );

        // --- Create Multiple Games using Factory ---
        // Create 20 games that are '募集中' and in the future
        Game::factory()->count(20)->recruiting()->create([
             'game_date_time' => fn() => Carbon::instance(fake()->dateTimeBetween('+2 hours', '+1 month')),
        ]);

        // Create 5 games that are '満員'
        Game::factory()->count(5)->full()->create([
             'game_date_time' => fn() => Carbon::instance(fake()->dateTimeBetween('+1 week', '+2 months')),
             // Optionally associate participants here or in a separate seeder
        ]);

        // Create 10 games that are '開催済み' (in the past)
        Game::factory()->count(10)->finished()->create();

        // Create 2 games that are '中止'
        Game::factory()->count(2)->cancelled()->create([
             'game_date_time' => fn() => Carbon::instance(fake()->dateTimeBetween('+3 days', '+10 days')),
        ]);

    }
}