<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game; // Eloquent model for Game
use Carbon\Carbon; // Date/Time library
use Illuminate\Support\Facades\DB; // For database transactions
use Illuminate\Support\Facades\Log; // For logging

class UpdateGameStatus extends Command
{
    /**
     * The name and signature of the console command.
     * Matches the signature used in Kernel.php.
     * @var string
     */
    protected $signature = 'games:update-status';

    /**
     * The console command description.
     * Describes the command's purpose.
     * @var string
     */
    protected $description = '試合ステータスを自動更新(募集中/満員→開催済み)';

    /**
     * Execute the console command.
     * Contains the main logic for the batch job.
     */
    public function handle(): int // Return integer status code (0 for success)
    {
        Log::info('[BATCH START] ' . $this->description);
        $this->info('Starting: ' . $this->description); // Output to console when run manually

        // --- Logic based on 詳細９と１０と１１.txt ---
        // Update games whose game_date_time is more than 1 hour in the past
        $thresholdTime = Carbon::now()->subHour(1);

        // Find games matching the criteria
        $gamesToUpdate = Game::where('game_date_time', '<=', $thresholdTime)
                             ->whereIn('status', ['募集中', '満員']) // Target statuses
                             ->get();

        if ($gamesToUpdate->isEmpty()) {
            Log::info('[BATCH END] 更新対象の試合はありませんでした。');
            $this->info('No games found to update.');
            return Command::SUCCESS; // Indicate success (0)
        }

        $count = 0;
        $errors = 0;

        $this->info("Found {$gamesToUpdate->count()} games to potentially update...");
        $bar = $this->output->createProgressBar($gamesToUpdate->count()); // Progress bar for console
        $bar->start();

        foreach ($gamesToUpdate as $game) {
            // Use DB transaction and locking for each game to prevent race conditions
            try {
                DB::transaction(function () use ($game, &$count) {
                    // Lock the specific game row for update
                    $lockedGame = Game::where('game_id', $game->game_id)->lockForUpdate()->first();

                    // Double-check status after locking
                    if ($lockedGame && in_array($lockedGame->status, ['募集中', '満員'])) {
                        $lockedGame->status = '開催済み'; // New status
                        $lockedGame->save();
                        $count++;
                        Log::debug("[UpdateGameStatus] Updated game {$game->game_id} to '開催済み'");
                    } else {
                        Log::debug("[UpdateGameStatus] Skipped game {$game->game_id} (status changed or not found after lock). Status: " . ($lockedGame->status ?? 'Not Found'));
                    }
                });
            } catch (\Exception $e) {
                $errors++;
                Log::error('[BATCH ERROR] 試合ステータス更新失敗 (GameID: ' . $game->game_id . ')', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString() // Include trace for debugging
                ]);
                $this->error(" Failed to update game {$game->game_id}: " . $e->getMessage());
                // Continue to next game
            }
            $bar->advance(); // Advance progress bar
        }
        $bar->finish();
        $this->newLine();

        Log::info('[BATCH END] ' . $count . '件の試合ステータスを「開催済み」に更新しました。' . ($errors > 0 ? " エラー: {$errors}件" : ''));
        $this->info("Finished: Updated {$count} games to '開催済み'. Errors: {$errors}.");

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS; // Return appropriate status code
    }
}