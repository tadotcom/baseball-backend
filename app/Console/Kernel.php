<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * アプリケーションのコマンドスケジュールを定義します。
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('games:update-status')
            ->cron('0 * * * *')
            ->withoutOverlapping()
            ->runInBackground();
            
        $schedule->command('games:cancel-low-participants')
            ->hourly() 
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * アプリケーションのコマンドを登録します。
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}