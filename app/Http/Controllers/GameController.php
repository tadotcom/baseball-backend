<?php

namespace App\Http\Controllers;

use App\Services\GameService;
use App\Http\Resources\GameResource;
// API用の FormRequest を使う (Web用と異なる)
use App\Http\Requests\StoreGameRequest; 
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    // F-USR-005: 試合一覧取得 (API)
    public function index(Request $request)
    {
        // TODO: サービス層でフィルターロジックを実装
        $games = Game::query()->latest('game_date_time')->paginate(20);
        return GameResource::collection($games);
    }
    
    // F-USR-007: 試合詳細取得 (API)
    public function show(Game $game)
    {
        return new GameResource($game);
    }

    // F-ADM-006: 試合登録 (API)
    public function store(StoreGameRequest $request)
    {
        $game = $this->gameService->createGame($request->validated());
        return (new GameResource($game))->response()->setStatusCode(201);
    }

    // F-ADM-007: 試合更新 (API)
    public function update(StoreGameRequest $request, Game $game)
    {
        $updatedGame = $this->gameService->updateGame($game, $request->validated());
        return new GameResource($updatedGame);
    }

    // F-ADM-008: 試合削除 (API)
    public function destroy(Game $game)
    {
        // TODO: サービス層で削除ロジックを実装
        $game->delete();
        return response()->json(null, 204);
    }
}