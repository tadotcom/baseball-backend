<?php

namespace App\Services;

use App\Models\Participation;
use App\Models\User;
use App\Repositories\ParticipationRepository;

class ParticipationService
{
    protected ParticipationRepository $participationRepository;

    public function __construct(ParticipationRepository $participationRepository)
    {
        $this->participationRepository = $participationRepository;
    }

    // Example method (Not explicitly in design docs, but might be needed)
    /**
     * Cancel a user's participation in a game.
     * @param User $user
     * @param string $gameId
     * @return bool True on success
     * @throws \Exception If cancellation fails or is not allowed.
     */
    // public function cancelParticipation(User $user, string $gameId): bool
    // {
    //     $participation = $this->participationRepository->findByUserAndGame($user->user_id, $gameId);
    //
    //     if (!$participation) {
    //         throw new \Exception("あなたはこの試合に参加していません。");
    //     }
    //
    //     // TODO: Add rules for cancellation (e.g., only before game starts?)
    //     if ($participation->game->game_date_time <= now()) {
    //          throw new \Exception("試合開始後のキャンセルはできません。");
    //     }
    //
    //     // Perform deletion
    //     $deleted = $participation->delete();
    //
    //     if ($deleted) {
    //          // TODO: Potentially update game status back to '募集中' if it was '満員'
    //          // $game = $participation->game;
    //          // if ($game->status === '満員') { ... check count and update ... }
    //          // TODO: Send notification about cancellation?
    //          \Log::info("User cancelled participation.", ['user_id' => $user->user_id, 'game_id' => $gameId]);
    //     }
    //
    //     return $deleted;
    // }
}