<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckinRequest; //
use App\Models\Game;
use App\Services\CheckinService; //
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CheckinController extends Controller
{
    protected CheckinService $checkinService;

    public function __construct(CheckinService $checkinService)
    {
        $this->checkinService = $checkinService;
    }

    /**
     * Perform the check-in action. (F-USR-008)
     *
     * @param CheckinRequest $request
     * @param Game $game (Route Model Binding)
     */
    public function store(CheckinRequest $request, Game $game): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $request->user();

        try {
             $this->checkinService->executeCheckin(
                 $user,
                 $game->game_id,
                 $validatedData['latitude'],
                 $validatedData['longitude']
             ); // Service handles logic and DB update

             // Return success response
             return response()->json([
                 'data' => [
                     'status' => 'success',
                     'participation_status' => 'チェックイン済' // Confirmed status
                 ],
                 'meta' => ['timestamp' => now()->toIso8601String()]
             ], 200); // 200 OK

        } catch (\Exception $e) {
            // Abort exceptions (400, 404, 409) from Service will be handled by Handler.php
             throw $e;
        }
    }
}