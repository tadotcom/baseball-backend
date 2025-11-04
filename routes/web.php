<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\Admin\GameController as AdminWebGameController;
use App\Http\Controllers\Admin\UserController as AdminWebUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// 管理者認証ルート
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->middleware('guest')
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('guest');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth:web')
        ->name('logout');
});

// 認証済み管理者ルート
Route::middleware(['auth:web', 'admin.web'])->prefix('admin')->name('admin.')->group(function () {
    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // 試合管理
    Route::resource('games', AdminWebGameController::class);
    
    // ユーザー管理
    Route::resource('users', AdminWebUserController::class)->only(['index', 'show', 'destroy']);
    
    // ★ 通知管理ルート（更新）
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // 通知送信履歴一覧
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        
        // 新規通知作成フォーム
        Route::get('/create', [NotificationController::class, 'create'])->name('create');
        
        // 通知送信履歴詳細
        Route::get('/{notificationLog}', [NotificationController::class, 'show'])->name('show');
        
        // プッシュ通知送信
        Route::post('/send-push', [NotificationController::class, 'sendPush'])->name('send-push');
        
        // メール送信
        Route::post('/send-email', [NotificationController::class, 'sendEmail'])->name('send-email');
    });
});

// 利用規約ページ（認証不要）
Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');

// プライバシーポリシーページ（認証不要）
Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');