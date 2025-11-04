<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use App\Models\User; // ★ 1. Userモデル
use App\Models\Game; // ★ 2. Gameモデル
use App\Models\Participation; // ★ 3. Participationモデル
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // ★ 4. Carbon（日付操作）

class DashboardController extends Controller
{
    /**
     * 管理者ダッシュボードを表示
     */
    public function index(): View
    {
        // 1. 総ユーザー数 (1,234 の代わり)
        $totalUsers = User::count();

        // 2. 今月の新規登録 (89 の代わり)
        $monthlySignups = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // 3. 開催予定試合 (15 の代わり)
        // (募集中 または 満員 で、日時が未来)
        $upcomingGamesCount = Game::whereIn('status', ['募集中', '満員'])
                             ->where('game_date_time', '>', Carbon::now())
                             ->count();
        
        // 4. 参加登録数(今月) (456 の代わり)
        $monthlyParticipants = Participation::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // 5. 最近の試合 (テーブルの代わり)
        // (開催日時が未来のものを5件)
        $recentGames = Game::where('game_date_time', '>', Carbon::now())
                           ->orderBy('game_date_time', 'asc') // 近い順
                           ->take(5)
                           ->get();

        // ビューにデータを渡す
        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'monthlySignups' => $monthlySignups,
            'upcomingGamesCount' => $upcomingGamesCount,
            'monthlyParticipants' => $monthlyParticipants,
            'recentGames' => $recentGames,
        ]);
    }
}