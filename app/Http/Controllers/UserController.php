<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * ユーザー一覧を表示 (SCR-ADM-001)
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // 検索キーワード (email or nickname)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%");
            });
        }

        $users = $query->latest('created_at')->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    /**
     * ★★★ 以下を新規追加 ★★★
     * ユーザー詳細を表示 (SCR-ADM-002)
     */
    public function show(User $user): View
    {
        // 必要に応じて、ユーザーの参加履歴なども読み込む
        // $user->load('participations.game'); 

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }
}