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
        // 既存のバッチ (要件定義書 [cite: 47] より)
        $schedule->command('games:update-status')
            ->cron('0 * * * *') // 毎時0分
            ->withoutOverlapping()
            ->runInBackground();
            
        // ★★★ ここから追記 ★★★
        
        // 参加者不足の試合を中止にするバッチ（毎時0分に実行）
        $schedule->command('games:cancel-low-participants')
            ->hourly() // ->cron('0 * * * *') と同じ意味です
            ->withoutOverlapping()
            ->runInBackground();
            
        // ★★★ 追記ここまで ★★★
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