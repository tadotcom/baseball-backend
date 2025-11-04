<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\UpdateGameStatus::class, // Register command
        Commands\SendGameReminder::class, // Register command
    ];

    /**
     * Define the application's command schedule.
     * Defines when the batch jobs run.
     *
     */
    protected function schedule(Schedule $schedule): void
    {
        // --- 試合ステータス自動更新バッチ ---
        // 毎時0分に実行
        $schedule->command('games:update-status') // Command signature
             ->cron('0 * * * *') // Execute on the hour, every hour
             ->withoutOverlapping() // Prevent duplicate runs
             ->runInBackground() // Run in background if possible
             ->appendOutputTo(storage_path('logs/schedule_games-update-status.log')); // Log output

        // --- 試合リマインダー通知バッチ ---
        // 毎10分に実行
        $schedule->command('notifications:send-reminder') // Command signature
             ->cron('*/10 * * * *') // Execute every 10 minutes
             ->withoutOverlapping()
             ->runInBackground()
             ->appendOutputTo(storage_path('logs/schedule_notifications-send-reminder.log')); // Log output

        // --- 論理削除データ物理削除バッチ ---
        // 毎日深夜3時に実行
        $schedule->command('model:prune', [
            '--model' => [\App\Models\User::class], // Target User model
        ])->dailyAt('03:00') // Run daily at 3:00 AM
           ->appendOutputTo(storage_path('logs/schedule_model-prune.log')); // Log output
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}