<?php

namespace App\Repositories;

use App\Models\Participation;
use App\Models\User; // Needed for type hinting
use Illuminate\Database\Eloquent\Collection; // For return types

class ParticipationRepository
{
    /**
     * Find a participation record by user ID and game ID.
     * @param string $userId
     * @param string $gameId
     * @param bool $forUpdate Lock the row for update.
     * @return Participation|null
     */
    public function findByUserAndGame(string $userId, string $gameId, bool $forUpdate = false): ?Participation
    {
        $query = Participation::where('user_id', $userId)->where('game_id', $gameId);
        if ($forUpdate) {
            $query->lockForUpdate();
        }
        return $query->first();
    }

    /**
     * Check if a participation record exists for a user and game.
     * @param string $userId
     * @param string $gameId
     * @return bool
     */
    public function existsByUserAndGame(string $userId, string $gameId): bool
    {
        return Participation::where('user_id', $userId)->where('game_id', $gameId)->exists();
    }


    /**
     * Create a new participation record. (F-USR-006)
     * @param array $data ('user_id', 'game_id', 'team_division', 'position', 'status')
     * @return Participation
     */
    public function create(array $data): Participation
    {
        // Model handles UUID generation
        return Participation::create($data);
    }

    /**
     * Update the status of a participation record. (F-USR-008 Check-in)
     * @param string $participationId
     * @param string $status ('参加確定', 'チェックイン済')
     * @return bool True on success, false otherwise
     */
    public function updateStatus(string $participationId, string $status): bool
    {
        $affectedRows = Participation::where('participation_id', $participationId)
                          ->update(['status' => $status]);
        return $affectedRows > 0;
    }

    /**
     * Count the number of participants for a given game.
     * Excludes potential soft-deleted users implicitly if User model relationship doesn't use withTrashed().
     * @param string $gameId
     * @return int
     */
    public function countByGame(string $gameId): int
    {
        // Counts records in the participations table for the game
        return Participation::where('game_id', $gameId)->count();
        // Alternative: Count only participants whose user is not soft-deleted
        // return Participation::where('game_id', $gameId)->whereHas('user')->count();
    }

    /**
     * Get all users participating in a specific game.
     * @param string $gameId
     * @return Collection<int, User>
     */
    public function getUsersByGame(string $gameId): Collection
    {
        // Eager load User model via relationship, automatically excludes soft-deleted users
        // unless User relationship is modified.
        return Participation::where('game_id', $gameId)->with('user')
                        ->get()->map->user->filter(); // Get users, filter out nulls if any
        // Alternative using Game model:
        // $game = Game::with('participants')->find($gameId);
        // return $game ? $game->participants : collect();
    }


    // Optional: Add methods to get games a user is participating in
    // public function getGamesByUser(string $userId, int $perPage = 15) { ... }
}