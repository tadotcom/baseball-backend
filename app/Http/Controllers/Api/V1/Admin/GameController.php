<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
// Use Admin specific requests if validation differs, otherwise reuse Game requests
use App\Http\Requests\Game\StoreGameRequest; //
use App\Http\Requests\Game\UpdateGameRequest; //
use App\Http\Requests\Game\FilterGameRequest; //
use App\Http\Resources\GameResource; //
use App\Models\Game;
// TODO: Create and use Admin specific GameService if logic differs significantly
use App\Services\GameService; // Using general GameService for now, split if needed
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    // Use general GameService, assuming admin actions are authorized by middleware
    // If complex admin-specific logic exists, create Admin\GameService
    protected GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Store a newly created game resource. (F-ADM-006)
     *
     */
    public function store(StoreGameRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        try {
            // TODO: Implement createGame in GameService (or Admin\GameService)
            // $game = $this->gameService->createGame($validatedData);
            $game = Game::create($validatedData); // Direct create for simplicity here, use Service

             // TODO: Send PUSH-001 notification to the admin who created it
             // auth()->user()->notify(new GameAdminRegistered($game));

            return response()->json([
                'data' => new GameResource($game),
                'meta' => ['timestamp' => now()->toIso8601String()]
            ], 201); // 201 Created
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update the specified game resource. (F-ADM-007)
     *
     */
    public function update(UpdateGameRequest $request, Game $game): GameResource
    {
        $validatedData = $request->validated();
        try {
            // TODO: Implement updateGame in GameService (or Admin\GameService)
            // $updatedGame = $this->gameService->updateGame($game, $validatedData);
             $game->update($validatedData); // Direct update for simplicity, use Service
             $updatedGame = $game->fresh(); // Get updated model instance

             // TODO: Handle status change notifications (e.g., GameCancelled PUSH-004/MAIL-005)
             // if ($validatedData['status'] === '中止') { ... send notifications ... }

            return (new GameResource($updatedGame))->additional([
                'meta' => ['timestamp' => now()->toIso8601String()]
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Remove the specified game resource. (F-ADM-008)
     *
     */
    public function destroy(Game $game): JsonResponse
    {
         try {
             // TODO: Implement deleteGame in GameService (or Admin\GameService) including participant check
             // $this->gameService->deleteGame($game);

             // Simple implementation with check
             if ($game->participations()->exists()) {
                 abort(409, '参加者が存在するため、試合を削除できません。'); // 409 Conflict might be better than 403/400
             }
             $game->delete();

             return response()->json(null, 204); // 204 No Content
         } catch (\Exception $e) {
             throw $e;
         }
    }

    /**
     * Display a paginated listing of all games for admin. (F-ADM-004)
     *
     */
     public function index(FilterGameRequest $request): AnonymousResourceCollection
    {
        $validatedData = $request->validated();
        $filters = [
            'prefecture' => $validatedData['prefecture'] ?? null,
            'date_from' => $validatedData['date_from'] ?? null,
            'date_to' => $validatedData['date_to'] ?? null,
            // Admin might filter by status including '開催済み', '中止'
            'status' => $validatedData['status'] ?? null,
        ];
        $perPage = $validatedData['per_page'] ?? 20;

        try {
             // TODO: Implement getGamesPaginated in GameService (or Admin\GameService)
             // Allow filtering by all statuses for admin
            // $games = $this->gameService->getAllGamesPaginated($filters, $perPage);

            // Simple implementation
             $query = Game::query();
             if ($filters['prefecture']) $query->where('prefecture', $filters['prefecture']);
             // ... apply other filters ...
             if ($filters['status']) $query->where('status', $filters['status']);
             $query->withCount('participations')->orderBy('game_date_time', 'desc'); // Default sort
             $games = $query->paginate($perPage);


            return GameResource::collection($games)->additional(['meta' => ['timestamp' => now()->toIso8601String()]]);
        } catch (\Exception $e) {
             throw $e;
        }
    }

    /**
     * Display the specified game resource for admin. (F-ADM-005)
     *
     */
    public function show(Game $game): GameResource
    {
        try {
            // Admin might need more details than regular user show
             // TODO: Implement findGameWithDetails in GameService (or Admin\GameService)
            $game->load(['participations.user']); // Load participants and users
            return (new GameResource($game))->additional(['meta' => ['timestamp' => now()->toIso8601String()]]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

}