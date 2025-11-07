<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// TODO: 必要に応じて通知用のServiceをインポート
// use App\Services\NotificationService; 

class CancelGamesDueToLowParticipants extends Command
{
    /**
     * コマンドのシグネチャ（呼び出し名）
     */
    protected $signature = 'games:cancel-low-participants';

    /**
     * コマンドの説明
     */
    protected $description = '試合開始24時間前までに参加者が18人に満たない試合を「中止」に更新する';

    // TODO: 通知サービスを利用する場合はコンストラクタでDI
    // protected $notificationService;
    // public function __construct(NotificationService $notificationService)
    // {
    //     parent::__construct();
    //     $this->notificationService = $notificationService;
    // }

    /**
     * コマンドの実行ロジック
     */
    public function handle()
    {
        $this->info('参加者不足による試合中止処理を開始...');
        Log::info('[BATCH] 参加者不足による試合中止処理を開始');

        $now = Carbon::now();
        
        // 1. チェック対象の期間を定義
        // (バッチが1時間ごとに動くと仮定)
        
        // ちょうど24時間後の試合をターゲットにする
        $targetStartTime = $now->copy()->addHours(24);
        // 1時間以内に開始される試合（24時間後〜25時間後）を対象とする
        $targetEndTime = $targetStartTime->copy()->addHour();

        // 2. 対象となる試合を取得
        $gamesToCancel = Game::with('participants.user') // 通知のために参加者情報を事前読み込み
            ->where('status', '募集中') // 「募集中」の試合のみ
            ->whereBetween('game_date_time', [$targetStartTime, $targetEndTime]) // 開催日時が24〜25時間後
            ->withCount('participants') // 参加者数をカウント
            ->having('participants_count', '<', 18) // 参加者が18人未満
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
                // 3. 試合ステータスを「中止」に更新
                $game->status = '中止';
                $game->save();

                $this->line("  - 試合ID: {$game->game_id} (参加者: {$game->participants_count}人) を「中止」に更新しました。");

                // 4. (推奨) 参加者に中止通知を送信
                $participants = $game->participants->pluck('user')->filter();
                if ($participants->isNotEmpty()) {
                    $title = '試合中止のお知らせ';
                    $body = "{$game->game_date_time->format('Y/m/d H:i')} 開催予定の「{$game->place_name}」は、規定人数に達しなかったため中止となりました。";
                    
                    // TODO: NotificationControllerにあるような通知ロジックを
                    //       共通サービス経由で呼び出す
                    // 例: $this->notificationService->sendPush($participants, $title, $body);
                    // 例: $this->notificationService->sendEmail($participants, $title, $body);
                    
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