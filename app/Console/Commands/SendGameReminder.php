<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\User;
use App\Notifications\GameReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendGameReminder extends Command
{
    protected $signature = 'notifications:send-reminder';
    protected $description = '試合開始1時間前の参加者にリマインダー通知を送信';

    public function handle(): int
    {
        Log::info('[BATCH START] ' . $this->description);
        $this->info('Starting: ' . $this->description);
        $startTime = Carbon::now()->addHour();
        $endTime = Carbon::now()->addHour()->addMinutes(10);

        Log::debug("[SendGameReminder] Checking for games between {$startTime->toIso8601String()} and {$endTime->toIso8601String()}");

        $games = Game::whereIn('status', ['募集中', '満員'])
                     ->whereBetween('game_date_time', [$startTime, $endTime])
                     ->with([
                         'participations' => function ($query) {
                             $query->with(['user' => function ($userQuery) {
                                 $userQuery->whereNull('deleted_at')->with('deviceTokens');
                             }]);
                         }
                     ])
                     ->get();

        if ($games->isEmpty()) {
            Log::info('[BATCH END] 通知対象の試合はありませんでした。');
            $this->info('No games found requiring reminders in the current window.');
            return Command::SUCCESS;
        }

        $this->info("Found {$games->count()} games requiring reminders...");
        $totalNotificationsSent = 0;
        $totalUsersTargeted = 0;
        $errors = 0;

        foreach ($games as $game) {
            $this->line("Processing game: {$game->game_id} ({$game->place_name})");
            foreach ($game->participations as $participation) {
                $user = $participation->user;

                if ($user && $user->deviceTokens()->exists()) {
                    $totalUsersTargeted++;
                    try {
                        Notification::sendNow($user, new GameReminder($game));
                        $totalNotificationsSent++;
                        Log::debug("[SendGameReminder] Sent reminder for game {$game->game_id} to user {$user->user_id}");
                    } catch (\Exception $e) {
                        $errors++;
                        Log::error('[BATCH ERROR] リマインダー通知送信失敗 (UserID: ' . $user->user_id . ', GameID: ' . $game->game_id . ')', [
                            'exception' => $e->getMessage()
                        ]);
                        $this->error("  Failed sending to user {$user->user_id}: " . $e->getMessage());
                    }
                } elseif ($user && !$user->deviceTokens()->exists()) {
                    Log::debug("[SendGameReminder] Skipped user {$user->user_id} for game {$game->game_id} (no device tokens).");
                } elseif (!$user) {
                     Log::warning("[SendGameReminder] Participation record found without a valid user for game {$game->game_id}, participation ID {$participation->participation_id}.");
                }
            }
        }

        Log::info('[BATCH END] ' . $totalNotificationsSent . "件のリマインダー通知を {$totalUsersTargeted}人の対象ユーザーに送信しました。" . ($errors > 0 ? " エラー: {$errors}件" : ''));
        $this->info("Finished: Sent {$totalNotificationsSent} reminders to {$totalUsersTargeted} targeted users. Errors: {$errors}.");

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}