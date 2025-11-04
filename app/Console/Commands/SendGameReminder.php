<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game; // Game model
use App\Models\User; // User model
use App\Notifications\GameReminder; // Notification class (from Chapter 10)
use Carbon\Carbon; // Date/Time library
use Illuminate\Support\Facades\Log; // Logging
use Illuminate\Support\Facades\Notification; // Notification facade

class SendGameReminder extends Command
{
    /**
     * The name and signature of the console command.
     * Matches the signature used in Kernel.php.
     * @var string
     */
    protected $signature = 'notifications:send-reminder';

    /**
     * The console command description.
     * Describes the command's purpose.
     * @var string
     */
    protected $description = '試合開始1時間前の参加者にリマインダー通知を送信';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Log::info('[BATCH START] ' . $this->description);
        $this->info('Starting: ' . $this->description);

        // --- Logic based on 詳細９と１０と１１.txt ---
        // Target games starting between 1 hour and 1 hour + 10 mins from now
        // (The 10 min window accounts for cron running every 10 mins)
        $startTime = Carbon::now()->addHour();
        $endTime = Carbon::now()->addHour()->addMinutes(10);

        Log::debug("[SendGameReminder] Checking for games between {$startTime->toIso8601String()} and {$endTime->toIso8601String()}");

        // Find relevant games (status '募集中' or '満員')
        // Eager load participants and their users to avoid N+1 queries
        $games = Game::whereIn('status', ['募集中', '満員']) // Target statuses
                     ->whereBetween('game_date_time', [$startTime, $endTime])
                     ->with([
                         'participations' => function ($query) {
                             // Eager load the user associated with each participation
                             $query->with(['user' => function ($userQuery) {
                                 // Eager load device tokens for the user, ensuring user is not soft-deleted
                                 $userQuery->whereNull('deleted_at')->with('deviceTokens');
                             }]);
                         }
                     ])
                     ->get();

        if ($games->isEmpty()) {
            Log::info('[BATCH END] 通知対象の試合はありませんでした。');
            $this->info('No games found requiring reminders in the current window.');
            return Command::SUCCESS; // Indicate success (0)
        }

        $this->info("Found {$games->count()} games requiring reminders...");
        $totalNotificationsSent = 0;
        $totalUsersTargeted = 0;
        $errors = 0;

        foreach ($games as $game) {
            $this->line("Processing game: {$game->game_id} ({$game->place_name})");
            foreach ($game->participations as $participation) {
                $user = $participation->user;

                // Check if user exists, is not soft-deleted, and has device tokens
                if ($user && $user->deviceTokens()->exists()) {
                    $totalUsersTargeted++;
                    try {
                        // Send the GameReminder notification (PUSH-003)
                        Notification::sendNow($user, new GameReminder($game)); // Use sendNow for immediate dispatch in batch
                        $totalNotificationsSent++;
                        Log::debug("[SendGameReminder] Sent reminder for game {$game->game_id} to user {$user->user_id}");
                    } catch (\Exception $e) {
                        $errors++;
                        Log::error('[BATCH ERROR] リマインダー通知送信失敗 (UserID: ' . $user->user_id . ', GameID: ' . $game->game_id . ')', [
                            'exception' => $e->getMessage()
                        ]);
                        $this->error("  Failed sending to user {$user->user_id}: " . $e->getMessage());
                        // Continue to next user/game
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