<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // webガードで認証チェック
        if (!Auth::guard('web')->check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::guard('web')->user();

        // 管理者チェック（メールアドレスで判定）
        $isAdmin = ($user->email === 'admin@yourdomain.com');
        
        // または role カラムを使用する場合:
        // $isAdmin = ($user->role === 1);
        
        // または is_admin カラムを使用する場合:
        // $isAdmin = ($user->is_admin === true);

        if (!$isAdmin) {
            abort(403, '管理者権限が必要です');
        }

        return $next($request);
    }
}