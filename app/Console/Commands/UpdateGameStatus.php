<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateGameStatus extends Command
{
    protected $signature = 'games:update-status';
    protected $description = '試合ステータスを自動更新(募集中/満員→開催済み)';

    public function handle(): int
    {
        Log::info('[BATCH START] ' . $this->description);
        $this->info('Starting: ' . $this->description);

        $thresholdTime = Carbon::now()->subHour(1);

        // Find games matching the criteria
        $gamesToUpdate = Game::where('game_date_time', '<=', $thresholdTime)
                             ->whereIn('status', ['募集中', '満員'])
                             ->get();

        if ($gamesToUpdate->isEmpty()) {
            Log::info('[BATCH END] 更新対象の試合はありませんでした。');
            $this->info('No games found to update.');
            return Command::SUCCESS; // Indicate success (0)
        }

        $count = 0;
        $errors = 0;

        $this->info("Found {$gamesToUpdate->count()} games to potentially update...");
        $bar = $this->output->createProgressBar($gamesToUpdate->count());
        $bar->start();

        foreach ($gamesToUpdate as $game) {
            try {
                DB::transaction(function () use ($game, &$count) {
                    $lockedGame = Game::where('game_id', $game->game_id)->lockForUpdate()->first();

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
                    'trace' => $e->getTraceAsString()
                ]);
                $this->error(" Failed to update game {$game->game_id}: " . $e->getMessage());
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        Log::info('[BATCH END] ' . $count . '件の試合ステータスを「開催済み」に更新しました。' . ($errors > 0 ? " エラー: {$errors}件" : ''));
        $this->info("Finished: Updated {$count} games to '開催済み'. Errors: {$errors}.");

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}