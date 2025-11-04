<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * ログイン画面を表示する
     */
    public function create(): View
    {
        return view('admin.login');
    }

    /**
     * ログインリクエストを処理する
     */
public function store(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ]);

    // 🔍 デバッグ情報を表示
    \Log::info('Login attempt:', $credentials);
    
    $attemptResult = Auth::guard('web')->attempt($credentials, $request->boolean('remember'));
    
    \Log::info('Attempt result:', ['success' => $attemptResult]);

    if ($attemptResult) {
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    return back()->withErrors([
        'email' => 'メールアドレスまたはパスワードが正しくありません。',
    ])->onlyInput('email');
}

    /**
     * ログアウト処理
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後、ログイン画面へリダイレクト
        return redirect()->route('admin.login');
    }
}