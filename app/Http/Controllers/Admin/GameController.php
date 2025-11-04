<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    protected $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * 試合一覧を表示 (F-ADM-004)
     */
    public function index(Request $request): View
    {
        $query = Game::query()->withCount('participations');

        // 検索キーワード (place_name or prefecture)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('place_name', 'like', "%{$search}%")
                  ->orWhere('prefecture', 'like', "%{$search}%");
            });
        }

        // フィルター: 都道府県
        if ($prefecture = $request->input('prefecture')) {
            $query->where('prefecture', $prefecture);
        }

        // フィルター: ステータス
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // フィルター: 開催日時の範囲
        if ($dateFrom = $request->input('date_from')) {
            $query->where('game_date_time', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->where('game_date_time', '<=', $dateTo . ' 23:59:59');
        }

        // ソート処理
        $sortField = $request->input('sort', 'game_date_time');
        $sortDirection = $request->input('direction', 'desc');
        
        // 許可されたソートフィールドのみ
        $allowedSorts = ['game_date_time', 'place_name', 'prefecture', 'status', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'game_date_time';
        }
        
        // ソート方向の検証
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortField, $sortDirection);

        $games = $query->paginate(20)->appends($request->except('page'));

        return view('admin.games.index', [
            'games' => $games,
            'search' => $search,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
            'prefectures' => $this->getPrefectures(),
        ]);
    }

    /**
     * 試合登録フォームを表示 (F-ADM-006)
     */
    public function create(): View
    {
        $prefectures = $this->getPrefectures();
        
        return view('admin.games.create', [
            'prefectures' => $prefectures,
        ]);
    }

    /**
     * 新しい試合を登録 (F-ADM-006)
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'place_name' => ['required', 'string', 'max:254'],
            'game_date_time' => ['required', 'date', 'after:now +1 hour'],
            'address' => ['required', 'string', 'max:254'],
            'prefecture' => ['required', 'string', 'in:'.implode(',', $this->getPrefectures())],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'acceptable_radius' => ['required', 'integer', 'min:1', 'max:1999'],
            'fee' => ['nullable', 'integer', 'min:0'],
            'capacity' => ['required', 'integer', 'min:18'],
        ]);
        
        try {
            $this->gameService->createGame($validatedData);
        } catch (\Exception $e) {
            Log::error('Admin Game Creation Failed: ' . $e->getMessage());
            return back()->with('error', '試合の登録に失敗しました。');
        }

        return redirect()->route('admin.games.index')->with('success', '試合を登録しました。');
    }

    /**
     * 試合詳細を表示 (F-ADM-005)
     */
    public function show(Game $game): View
    {
        // 参加者情報を読み込む
        $game->load(['participations.user']);

        return view('admin.games.show', [
            'game' => $game,
        ]);
    }

    /**
     * 試合編集フォームを表示 (F-ADM-007)
     */
    public function edit(Game $game): View
    {
        // 編集画面で参加者を表示するため読み込む
        $game->load(['participations.user']);
        $prefectures = $this->getPrefectures();
        
        return view('admin.games.edit', [
            'game' => $game,
            'prefectures' => $prefectures,
        ]);
    }

    /**
     * 試合を更新 (F-ADM-007)
     */
    public function update(Request $request, Game $game): RedirectResponse
    {
        $validatedData = $request->validate([
            'place_name' => ['required', 'string', 'max:254'],
            'game_date_time' => ['required', 'date', 'after:now +1 hour'],
            'address' => ['required', 'string', 'max:254'],
            'prefecture' => ['required', 'string', 'in:'.implode(',', $this->getPrefectures())],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'acceptable_radius' => ['required', 'integer', 'min:1', 'max:1999'],
            'fee' => ['nullable', 'integer', 'min:0'],
            'capacity' => ['required', 'integer', 'min:18'],
            'status' => ['sometimes', 'string', 'in:募集中,満員,開催済み,中止'],
        ]);

        try {
            $this->gameService->updateGame($game, $validatedData);
        } catch (\Exception $e) {
            Log::error('Admin Game Update Failed: ' . $e->getMessage());
            return back()->with('error', '試合の更新に失敗しました。');
        }

        return redirect()->route('admin.games.index')->with('success', '試合を更新しました。');
    }

    /**
     * 試合を削除 (F-ADM-008)
     */
    public function destroy(Game $game): RedirectResponse
    {
        // 参加者がいる場合は削除できない
        if ($game->participations()->count() > 0) {
            return back()->with('error', '参加者がいるため削除できません。');
        }

        try {
            $game->delete();
            
            return redirect()->route('admin.games.index')
                ->with('success', '試合を削除しました。');
        } catch (\Exception $e) {
            Log::error('Admin Game Deletion Failed: ' . $e->getMessage());
            
            return back()->with('error', '試合の削除に失敗しました。');
        }
    }

    /**
     * 都道府県リストを取得
     */
    private function getPrefectures(): array
    {
        return [
            '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
            '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
            '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
            '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
            '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
        ];
    }
}