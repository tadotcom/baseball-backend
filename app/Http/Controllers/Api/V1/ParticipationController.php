<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParticipationRequest; //
use App\Http\Resources\ParticipationResource; //
use App\Models\Game;
use App\Services\GameService; // Using GameService for participation logic
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ParticipationController extends Controller
{
    protected GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Store a newly created resource in storage. (F-USR-006)
     *
     * @param StoreParticipationRequest $request
     * @param Game $game (Route Model Binding)
     */
    public function store(StoreParticipationRequest $request, Game $game): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $request->user(); // Get authenticated user

        try {
            $participation = $this->gameService->addParticipant(
                $user,
                $game->game_id, // Pass game ID
                $validatedData // Pass validated 'team_division' and 'position'
            );

            // Return the created participation resource
            return response()->json([
                'data' => new ParticipationResource($participation),
                'meta' => ['timestamp' => now()->toIso8601String()]
            ], 201); // 201 Created

        } catch (\Exception $e) {
             // Abort exceptions (400, 404, 409) from Service will be handled by Handler.php
            throw $e; // Rethrow other exceptions
        }
    }

    // TODO: Add methods for index (list user's participations) or destroy (cancel participation) if needed
}