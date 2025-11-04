<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\FilterGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    protected GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Display a listing of the resource for regular users. (F-USR-005)
     * ★ 修正: 各試合にユーザーの参加状態（is_participating, has_checked_in）を追加
     */
    public function index(FilterGameRequest $request): AnonymousResourceCollection
    {
        $validatedData = $request->validated();
        $filters = [
            'prefecture' => $validatedData['prefecture'] ?? null,
            'date_from' => $validatedData['date_from'] ?? null,
            'date_to' => $validatedData['date_to'] ?? null,
        ];
        $perPage = $validatedData['per_page'] ?? 20;

        try {
            $games = $this->gameService->getActiveGamesPaginated($filters, $perPage);
            
            // ★ 認証中のユーザーIDを取得
            $userId = auth()->id();
            
            // ★ 各試合に参加状態を追加
            if ($userId) {
                // ユーザーの参加情報をEager Loadingで取得（パフォーマンス向上）
                $games->load(['participations' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }]);
                
                // 各試合に参加状態を設定
                $games->getCollection()->transform(function ($game) use ($userId) {
                    // 1. ユーザーがこの試合に参加済みか
                    $isParticipating = $game->participations->contains('user_id', $userId);
                    
                    // 2. ユーザーがチェックイン済みか
                    $hasCheckedIn = false;
                    if ($isParticipating) {
                        $participation = $game->participations->firstWhere('user_id', $userId);
                        $hasCheckedIn = $participation && $participation->status === 'チェックイン済';
                    }
                    
                    // Gameモデルに一時的な属性として追加
                    $game->setAttribute('is_participating', $isParticipating);
                    $game->setAttribute('has_checked_in', $hasCheckedIn);
                    
                    // participationsリレーションを削除（不要なデータを送信しない）
                    $game->unsetRelation('participations');
                    
                    return $game;
                });
            } else {
                // 未認証の場合は全てfalseを設定
                $games->getCollection()->transform(function ($game) {
                    $game->setAttribute('is_participating', false);
                    $game->setAttribute('has_checked_in', false);
                    return $game;
                });
            }
            
            return GameResource::collection($games)->additional([
                'meta' => ['timestamp' => now()->toIso8601String()]
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Display the specified resource for regular users. (F-USR-007)
     */
    public function show(Game $game): GameResource
    {
        try {
            // 参加者リストと、各参加者のユーザー情報を読み込む
            $game->load(['participations.user']);

            // ★★★ ここから修正 ★★★
            // 認証中のユーザーIDを取得
            $userId = auth()->id();
            
            // 1. ユーザーがこの試合に参加済みか
            // (participationsリレーションがロード済みなので、DBに追加クエリは走らない)
            $isParticipating = $game->participations->contains('user_id', $userId);
            
            // 2. ユーザーがチェックイン済みか
            $hasCheckedIn = false;
            if ($isParticipating) {
                $participation = $game->participations->firstWhere('user_id', $userId);
                $hasCheckedIn = $participation && $participation->status === 'チェックイン済'; 
            }

            // Gameモデルに一時的な属性として追加
            // (GameResource側でこれを読み取ってJSONに含める)
            $game->setAttribute('is_participating', $isParticipating);
            $game->setAttribute('has_checked_in', $hasCheckedIn);
            // ★★★ 修正ここまで ★★★

            return (new GameResource($game))->additional([
                'meta' => ['timestamp' => now()->toIso8601String()]
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}