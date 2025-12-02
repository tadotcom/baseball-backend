<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelGamesDueToLowParticipants extends Command
{
    protected $signature = 'games:cancel-low-participants';
    protected $description = '試合開始24時間前までに参加者が18人に満たない試合を「中止」に更新する';

    public function handle()
    {
        $this->info('参加者不足による試合中止処理を開始...');
        Log::info('[BATCH] 参加者不足による試合中止処理を開始');

        $now = Carbon::now();
        $targetStartTime = $now->copy()->addHours(24);
        $targetEndTime = $targetStartTime->copy()->addHour();

        $gamesToCancel = Game::with('participants.user')
            ->where('status', '募集中')
            ->whereBetween('game_date_time', [$targetStartTime, $targetEndTime])
            ->withCount('participants')
            ->having('participants_count', '<', 18) 
            ->get();

        if ($gamesToCancel->isEmpty()) {
            $this->info('中止対象の試合はありませんでした。');
            Log::info('[BATCH] 中止対象の試合はありませんでした。');
            return 0;
        }

        $this->info("{$gamesToCancel->count()}件の試合を中止します。");
        $cancelledCount = 0;

        foreach ($gamesToCancel as $game) {
            DB::beginTransaction();
            try {
                $game->status = '中止';
                $game->save();

                $this->line("  - 試合ID: {$game->game_id} (参加者: {$game->participants_count}人) を「中止」に更新しました。");

                $participants = $game->participants->pluck('user')->filter();
                if ($participants->isNotEmpty()) {
                    $title = '試合中止のお知らせ';
                    $body = "{$game->game_date_time->format('Y/m/d H:i')} 開催予定の「{$game->place_name}」は、規定人数に達しなかったため中止となりました。";            
                    $this->line("    ... {$participants->count()}人の参加者に通知を試みます。");
                }

                DB::commit();
                $cancelledCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("[BATCH] 試合中止処理失敗 (GameID: {$game->game_id}): " . $e->getMessage());
                $this->error("  - 試合ID: {$game->game_id} の処理に失敗しました: " . $e->getMessage());
            }
        }

        $this->info("合計 {$cancelledCount}件の試合を中止しました。");
        Log::info("[BATCH] 参加者不足による試合中止処理を完了。 {$cancelledCount}件を中止。");
        
        return 0;
    }
}