<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use App\Repositories\GameRepository;
use App\Repositories\ParticipationRepository;
use App\Utils\DistanceCalculator; // Haversine calculation
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CheckinCompleted; //
use Symfony\Component\HttpKernel\Exception\HttpException; // For abort()

class CheckinService
{
    protected ParticipationRepository $participationRepository;
    protected GameRepository $gameRepository;

    public function __construct(ParticipationRepository $participationRepository, GameRepository $gameRepository)
    {
        $this->participationRepository = $participationRepository;
        $this->gameRepository = $gameRepository;
    }

    /**
     * Execute the check-in process. (F-USR-008)
     *
     * @param User $user
     * @param string $gameId
     * @param float $latitude User's current latitude
     * @param float $longitude User's current longitude
     * @return void
     * @throws HttpException If check-in fails validation.
     * @throws \Exception For other errors.
     */
    public function executeCheckin(User $user, string $gameId, float $latitude, float $longitude): void
    {
        Log::info("[CheckinService] Check-in attempt.", [
            'user_id' => $user->user_id,
            'game_id' => $gameId,
            'lat' => $latitude,
            'lon' => $longitude
        ]);

        // Wrap validations and update in a transaction? Not strictly necessary if update is atomic.
        // DB::transaction(function () use ($user, $gameId, $latitude, $longitude) { // Optional Transaction

        // 3a. Find participation record
        $participation = $this->participationRepository->findByUserAndGame($user->user_id, $gameId);
        if (!$participation) {
            Log::warning("[CheckinService] Check-in failed: User not participating.", ['user_id' => $user->user_id, 'game_id' => $gameId]);
            abort(400, 'E-400-08: この試合に参加登録していません'); //
        }

        // 3b. Check participation status
        if ($participation->status === 'チェックイン済') {
             Log::warning("[CheckinService] Check-in failed: Already checked in.", ['participation_id' => $participation->participation_id]);
            abort(409, 'E-409-04: 既にチェックイン済みです'); //
        }
        // If status is not '参加確定', also fail (shouldn't happen)
        if ($participation->status !== '参加確定') {
             Log::error("[CheckinService] Check-in failed: Invalid participation status.", ['participation_id' => $participation->participation_id, 'status' => $participation->status]);
             abort(400, 'チェックインできない参加ステータスです。');
        }


        // 3c. Find game details
        $game = $this->gameRepository->findById($gameId);
        if (!$game) {
            // Should be caught by route model binding, but double-check
             Log::error("[CheckinService] Check-in failed: Game not found during service execution.", ['game_id' => $gameId]);
            abort(404, 'E-404-02: 試合が見つかりません');
        }

        // 3d. Check Time Window
        if (!$this->isWithinCheckinWindow($game->game_date_time)) {
             Log::warning("[CheckinService] Check-in failed: Outside time window.", ['game_id' => $gameId, 'game_time' => $game->game_date_time]);
            abort(400, 'E-400-09: チェックイン可能時間外です'); //
        }

        // 3e/3f. Calculate distance and check radius
        $distance = DistanceCalculator::calculate(
            $latitude,
            $longitude,
            $game->latitude,
            $game->longitude
        );
        Log::debug("[CheckinService] Calculated distance.", ['game_id' => $gameId, 'distance_m' => $distance, 'radius_m' => $game->acceptable_radius]);

        if ($distance > $game->acceptable_radius) {
             Log::warning("[CheckinService] Check-in failed: Outside acceptable radius.", ['game_id' => $gameId, 'distance_m' => $distance, 'radius_m' => $game->acceptable_radius]);
            // Format distance for message
             $distanceFormatted = round($distance);
             abort(400, "E-400-10: 会場から{$distanceFormatted}メートル離れています"); //
        }

        // 4. Update participation status
        $updated = $this->participationRepository->updateStatus(
            $participation->participation_id,
            'チェックイン済' // Target status
        );

        if (!$updated) {
            // This might happen if the record was deleted between checks (unlikely)
             Log::error("[CheckinService] Check-in failed: DB update returned false.", ['participation_id' => $participation->participation_id]);
            throw new \Exception("チェックインステータスの更新に失敗しました。");
        }
         Log::info("[CheckinService] Check-in status updated successfully.", ['participation_id' => $participation->participation_id]);

        // }); // End Optional Transaction

        // 5. Send Check-in Completed Notification (outside transaction)
        try {
            Notification::send($user, new CheckinCompleted($game)); // PUSH-006
            Log::info("[CheckinService] CheckinCompleted notification queued.", ['user_id' => $user->user_id, 'game_id' => $gameId]);
        } catch (\Exception $e) {
             Log::error("[CheckinService] Failed to queue CheckinCompleted notification.", ['user_id' => $user->user_id, 'error' => $e->getMessage()]);
             // Do not fail the check-in if notification fails
        }
    }

    /**
     * Check if the current time is within the allowed check-in window.
     * Rule: 2 hours before start to 3 hours after start (game end assumed).
     * @param Carbon $gameDateTime
     * @return bool
     */
    private function isWithinCheckinWindow(Carbon $gameDateTime): bool
    {
        $now = Carbon::now();
        $startTime = $gameDateTime->copy()->subHours(2);
        $endTime = $gameDateTime->copy()->addHours(3); // Assuming 3-hour game duration

        Log::debug("[CheckinService] Time Window Check:", [
             'now' => $now->toIso8601String(),
             'game_start' => $gameDateTime->toIso8601String(),
             'window_start' => $startTime->toIso8601String(),
             'window_end' => $endTime->toIso8601String(),
             'is_within' => $now->betweenIncluded($startTime, $endTime) // Use Carbon's betweenIncluded
        ]);

        return $now->betweenIncluded($startTime, $endTime);
    }
}