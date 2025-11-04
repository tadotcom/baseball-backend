<?php

namespace App\Repositories;

use App\Models\Game;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder; // For query builder type hinting

class GameRepository
{
    /**
     * Find a game by its ID, optionally locking for update.
     * @param string $gameId
     * @param bool $forUpdate Lock the row for update.
     * @return Game|null
     */
    public function findById(string $gameId, bool $forUpdate = false): ?Game
    {
        $query = Game::query();
        if ($forUpdate) {
            $query->lockForUpdate(); //
        }
        return $query->find($gameId); // Assumes $gameId is the primary key 'game_id'
    }

     /**
     * Get paginated list of games with filters.
     * Used by GameController@index (F-USR-005) and Admin\GameController@index (F-ADM-004).
     * @param array $filters (e.g., ['status' => ['募集中'], 'prefecture' => '...', 'date_from' => '...', 'date_to' => '...'])
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Game::query();

        // Apply status filter(s)
        if (!empty($filters['status'])) {
            $query->whereIn('status', (array) $filters['status']);
        }
        // Apply prefecture filter
        if (!empty($filters['prefecture'])) {
            $query->where('prefecture', $filters['prefecture']);
        }
        // Apply date range filters
        if (!empty($filters['date_from'])) {
             // Compare date part only
             $query->whereDate('game_date_time', '>=', $filters['date_from']);
        }
         if (!empty($filters['date_to'])) {
              // Compare date part only
             $query->whereDate('game_date_time', '<=', $filters['date_to']);
        }
         // Optional: Filter out games that have already finished completely
         // if (!isset($filters['include_past']) || !$filters['include_past']) {
         //     $query->where('game_date_time', '>=', now()->subHours(3)); // Example: Show games finished < 3 hrs ago
         // }


        // Default sort order (e.g., upcoming games first for users, maybe different for admin?)
        $query->orderBy('game_date_time', 'asc');

        // Add participant count efficiently
        $query->withCount('participations');

        return $query->paginate($perPage);
    }


    /**
     * Create a new game. (Used by F-ADM-006)
     * @param array $data
     * @return Game
     */
    public function create(array $data): Game
    {
        // Model automatically handles UUID generation
        return Game::create($data);
    }

     /**
     * Update an existing game. (Used by F-ADM-007)
     * @param Game $game
     * @param array $data
     * @return bool
     */
    public function update(Game $game, array $data): bool
    {
        return $game->update($data);
    }

     /**
     * Delete a game. (Used by F-ADM-008)
     * Note: Does not check for participants here; Service layer should check.
     * @param Game $game
     * @return bool|null
     */
    public function delete(Game $game): ?bool
    {
        // This performs a hard delete. Foreign key constraints apply.
        return $game->delete();
    }


     /**
     * Update game status.
     * @param string $gameId
     * @param string $status
     * @return bool True on success, false otherwise
     */
    public function updateStatus(string $gameId, string $status): bool
    {
        // Use update method for efficiency, returns number of rows affected
        $affectedRows = Game::where('game_id', $gameId)->update(['status' => $status]);
        return $affectedRows > 0;
    }

    // --- Deprecated Methods from previous examples ---
    // Use findById($id, true) instead
    // public function findByIdForUpdate(string $gameId): ?Game { ... }
}