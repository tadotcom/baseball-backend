<?php

namespace App\Http\Controllers\Admin; // ★ スラッシュを \ に修正

use App\Http\Controllers\Controller; // ★ スラッシュを \ に修正
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * ユーザー一覧を表示 (F-ADM-001)
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // 検索キーワード (email or nickname)
        // ★ 以前のファイル から検索ロジックを修正
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%"); // 'name' ではなく 'nickname'
            });
        }

        // 退会済みを含むかどうか
        if ($request->boolean('include_deleted')) {
            $query->withTrashed();
        }

        $users = $query->latest('created_at')->paginate(20)->appends($request->except('page'));

        return view('admin.users.index', [
            'users' => $users,
            'search' => $request->input('search'), // ★ 検索キーワードをビューに渡す
        ]);
    }

    /**
     * ユーザー詳細を表示 (F-ADM-002)
     */
    public function show(User $user): View
    {
        // ★ 参加履歴と、各参加履歴に関連する試合情報を読み込む
        $user->load(['participations.game']);

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * ユーザーを強制削除 (F-ADM-003)
     */
    public function destroy(User $user): RedirectResponse
    {
        // 自分自身は削除できない
        if (auth()->id() === $user->user_id) { // ★ .id ではなく .user_id を使用
            return back()->with('error', '自分自身を削除することはできません。');
        }

        try {
            // ソフトデリート
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', 'ユーザーを強制退会させました。');
        } catch (\Exception $e) {
            Log::error('Admin User Deletion Failed: ' . $e->getMessage());

            return back()->with('error', 'ユーザーの削除に失敗しました。');
        }
    }
}