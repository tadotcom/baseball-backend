<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use App\Repositories\GameRepository;
use App\Repositories\ParticipationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
// Notifications
use App\Notifications\GameParticipantRegistered;
use App\Notifications\GameFull;
use App\Notifications\GameCancelled;
use App\Notifications\GameAdminRegistered;
// Exceptions
use Illuminate\Auth\Access\AuthorizationException; // For potential ownership checks
use Symfony\Component\HttpKernel\Exception\HttpException; // For abort()

class GameService
{
    protected GameRepository $gameRepository;
    protected ParticipationRepository $participationRepository;

    public function __construct(GameRepository $gameRepository, ParticipationRepository $participationRepository)
    {
        $this->gameRepository = $gameRepository;
        $this->participationRepository = $participationRepository;
    }

    /**
     * Get paginated list of active games with filters (for users).
     *
     * @param array $filters ('prefecture', 'date_from', 'date_to')
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActiveGamesPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        // Only show '募集中' or '満員' games and upcoming games
        $defaultFilters = [
            'status' => ['募集中', '満員'],
            'date_from' => now()->toDateString(), // Only show from today onwards
            // 'include_past' => false, // Ensure past games aren't included
        ];
        $mergedFilters = array_merge($defaultFilters, array_filter($filters)); // Merge and remove null/empty filters

        Log::debug("[GameService] Fetching active games.", ['filters' => $mergedFilters, 'perPage' => $perPage]);
        return $this->gameRepository->getPaginated($mergedFilters, $perPage);
    }

     /**
      * Find a specific game by ID, ensuring it's suitable for user view (optional).
      * @param string $gameId
      * @return Game
      */
     public function findGameByIdForUser(string $gameId): Game
     {
         $game = $this->gameRepository->findById($gameId);
         if (!$game) {
             abort(404, 'E-404-02: 試合が見つかりません');
         }
         // Optionally add checks if certain statuses shouldn't be viewable by users
         return $game;
     }


    /**
     * Add a participant to a game. (F-USR-006 Logic)
     *
     * @param User $user
     * @param string $gameId
     * @param array $data ('team_division', 'position')
     * @return \App\Models\Participation
     * @throws HttpException If participation is not allowed.
     */
    public function addParticipant(User $user, string $gameId, array $data): \App\Models\Participation
    {
        Log::info("[GameService] Attempting participation.", ['user_id' => $user->user_id, 'game_id' => $gameId]);
        return DB::transaction(function () use ($user, $gameId, $data) {
            // 5. Lock and find game
            $game = $this->gameRepository->findById($gameId, true); // Lock for update

            // 6. Check if game exists
            if (!$game) {
                 Log::warning("[GameService] Participation failed: Game not found.", ['game_id' => $gameId]);
                abort(404, 'E-404-02: 試合が見つかりません');
            }

            // 7. Check game status
            if ($game->status !== '募集中') {
                 Log::warning("[GameService] Participation failed: Game not recruiting.", ['game_id' => $gameId, 'status' => $game->status]);
                abort(400, 'E-400-07: この試合は参加登録できません');
            }

            // 8. Check for duplicate participation
            if ($this->participationRepository->existsByUserAndGame($user->user_id, $gameId)) {
                 Log::warning("[GameService] Participation failed: User already participating.", ['user_id' => $user->user_id, 'game_id' => $gameId]);
                abort(409, 'E-409-03: 既にこの試合に参加登録しています');
            }

            // --- Check Capacity before Insert ---
            $currentParticipants = $this->participationRepository->countByGame($gameId);
            if ($currentParticipants >= $game->capacity) {
                Log::warning("[GameService] Participation failed: Game is full.", ['game_id' => $gameId, 'count' => $currentParticipants, 'capacity' => $game->capacity]);
                 // Update status just in case it wasn't updated before, then abort
                 if ($game->status === '募集中') {
                    $this->gameRepository->updateStatus($gameId, '満員');
                 }
                 abort(400, 'E-400-07: この試合は満員のため参加登録できません'); // Use same code? Or a specific 'full' code?
            }
            // --- End Capacity Check ---


            // 9. Create participation record
            $participationData = array_merge($data, [
                'user_id' => $user->user_id,
                'game_id' => $gameId,
                'status' => '参加確定', // Default status
            ]);
            $participation = $this->participationRepository->create($participationData);
             Log::info("[GameService] Participation record created.", ['participation_id' => $participation->participation_id]);

            // 10. Check if game is now full (re-check after insert within transaction)
            $newParticipantCount = $currentParticipants + 1;
            if ($newParticipantCount >= $game->capacity) {
                // 11. Update game status to '満員'
                 Log::info("[GameService] Game is now full. Updating status.", ['game_id' => $gameId]);
                $this->gameRepository->updateStatus($gameId, '満員');

                // Send 'Game Full' notification (PUSH-005)
                try {
                    $participants = $this->participationRepository->getUsersByGame($gameId);
                    Notification::send($participants, new GameFull($game));
                     Log::info("[GameService] GameFull notification queued.", ['game_id' => $gameId]);
                } catch (\Exception $e) {
                     Log::error("[GameService] Failed to queue GameFull notification.", ['game_id' => $gameId, 'error' => $e->getMessage()]);
                }
            }

            // 12. Transaction commits here

            // 13. Send 'Participant Registered' notifications (outside transaction)
            try {
                 Notification::send($user, new GameParticipantRegistered($game /*, $participation*/)); // PUSH-002
                 Log::info("[GameService] GameParticipantRegistered notification queued.", ['user_id' => $user->user_id, 'game_id' => $gameId]);
            } catch (\Exception $e) {
                 Log::error("[GameService] Failed to queue GameParticipantRegistered notification.", ['user_id' => $user->user_id, 'error' => $e->getMessage()]);
            }


            return $participation;
        });
    }

    // --- Admin Actions (Placeholders - Implement in Admin\GameService if separated) ---

     /**
      * Create a new game (Admin). (F-ADM-006)
      * @param array $data
      * @return Game
      */
     public function createGame(array $data): Game
     {
         Log::info("[GameService] Creating new game by admin.", ['admin_id' => auth()->id() ?? 'N/A', 'place' => $data['place_name']]);
         // Add 'status' => '募集中' if not provided
         $data['status'] = $data['status'] ?? '募集中';
         $game = $this->gameRepository->create($data);

          // Send notification to creator admin (PUSH-001)
          try {
             $adminUser = auth()->user();
             if ($adminUser) {
                  Notification::send($adminUser, new GameAdminRegistered($game));
                  Log::info("[GameService] GameAdminRegistered notification queued.", ['admin_id' => $adminUser->user_id, 'game_id' => $game->game_id]);
             }
          } catch (\Exception $e) {
              Log::error("[GameService] Failed to queue GameAdminRegistered notification.", ['game_id' => $game->game_id, 'error' => $e->getMessage()]);
          }

         return $game;
     }

     /**
      * Update an existing game (Admin). (F-ADM-007)
      * @param Game $game
      * @param array $data
      * @return Game
      */
     public function updateGame(Game $game, array $data): Game
     {
          Log::info("[GameService] Updating game by admin.", ['admin_id' => auth()->id() ?? 'N/A', 'game_id' => $game->game_id]);
          $originalStatus = $game->status;
          $updateSuccess = $this->gameRepository->update($game, $data);

          if (!$updateSuccess) {
              throw new \Exception("試合情報の更新に失敗しました。");
          }

          $updatedGame = $game->fresh(); // Get updated model

          // Handle status change notifications
          if (isset($data['status']) && $data['status'] !== $originalStatus) {
              Log::info("[GameService] Game status changed.", ['game_id' => $game->game_id, 'from' => $originalStatus, 'to' => $data['status']]);
              if ($data['status'] === '中止') {
                  // Send GameCancelled notification (PUSH-004 / MAIL-005)
                   try {
                       $participants = $this->participationRepository->getUsersByGame($game->game_id);
                       Notification::send($participants, new GameCancelled($updatedGame));
                        Log::info("[GameService] GameCancelled notification queued.", ['game_id' => $game->game_id, 'count' => $participants->count()]);
                   } catch (\Exception $e) {
                        Log::error("[GameService] Failed to queue GameCancelled notification.", ['game_id' => $game->game_id, 'error' => $e->getMessage()]);
                   }
              }
              // TODO: Add notification for status change to '満員' if needed (PUSH-005 already sent by addParticipant?)
          }

          return $updatedGame;
     }

     /**
      * Delete a game (Admin). (F-ADM-008)
      * @param Game $game
      * @return void
      * @throws HttpException If deletion is not allowed.
      */
     public function deleteGame(Game $game): void
     {
         Log::warning("[GameService] Attempting to delete game by admin.", ['admin_id' => auth()->id() ?? 'N/A', 'game_id' => $game->game_id]);
         // Check if participants exist (Foreign key constraint ON DELETE RESTRICT)
         if ($this->participationRepository->countByGame($game->game_id) > 0) {
              Log::error("[GameService] Game deletion failed: Participants exist.", ['game_id' => $game->game_id]);
             // Use 409 Conflict as the reason is existing related data
             abort(409, '参加者が存在するため、試合を削除できません。');
         }

         $deleteSuccess = $this->gameRepository->delete($game);

         if (!$deleteSuccess) {
              Log::error("[GameService] Game deletion failed unexpectedly.", ['game_id' => $game->game_id]);
             throw new \Exception("試合の削除に失敗しました。");
         }
          Log::info("[GameService] Game deleted successfully.", ['game_id' => $game->game_id]);
     }

      /**
       * Get paginated list of all games with filters (for admin).
       * @param array $filters
       * @param int $perPage
       * @return LengthAwarePaginator
       */
      public function getAllGamesPaginated(array $filters, int $perPage): LengthAwarePaginator
      {
           // Admin can see all statuses, adjust default filters if needed
           $mergedFilters = array_filter($filters); // Remove null/empty filters
           Log::debug("[GameService] Fetching all games for admin.", ['filters' => $mergedFilters, 'perPage' => $perPage]);
           return $this->gameRepository->getPaginated($mergedFilters, $perPage);
      }

       /**
        * Find a game by ID with details (for admin).
        * @param string $gameId
        * @return Game
        */
       public function findGameWithDetails(string $gameId): Game
       {
           $game = $this->gameRepository->findById($gameId);
           if (!$game) {
               abort(404, 'E-404-02: 試合が見つかりません');
           }
           // Load details needed for admin view
           $game->load(['participations.user']); // Load participants and their user info
           return $game;
       }

}