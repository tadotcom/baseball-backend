<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB; // For Health Check
use Illuminate\Support\Facades\Log; // For Health Check
// --- Controller Imports ---
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GameController;
use App\Http\Controllers\Api\V1\ParticipationController;
use App\Http\Controllers\Api\V1\CheckinController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Admin\GameController as AdminGameController;
use App\Http\Controllers\Api\V1\Admin\NotificationController as AdminNotificationController;
// --- End Controller Imports ---

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- API Version 1 Routes ---
Route::prefix('v1')->group(function () { // API Version Prefix

    // --- Public Routes (Rate limit 'api') ---
    // These routes do not require authentication.
    Route::middleware('throttle:api')->group(function () { // Apply public API rate limiting
        // Authentication Endpoints
        Route::post('/auth/register', [AuthController::class, 'register'])->name('api.v1.auth.register');         // F-USR-001
        Route::post('/auth/login', [AuthController::class, 'login'])->name('api.v1.auth.login');                 // F-USR-002
        // Password Reset Endpoints
        Route::post('/auth/password/reset', [AuthController::class, 'sendResetLinkEmail'])->name('api.v1.password.email'); // F-USR-004 Part 1
        Route::put('/auth/password/update', [AuthController::class, 'resetPassword'])->name('api.v1.password.update');     // F-USR-004 Part 2
        // Health Check Endpoint
        Route::get('/health', function () { //
            try { DB::connection()->getPdo(); return response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()], 200); }
            catch (\Exception $e) { Log::critical('[HEALTH CHECK FAILED]', ['error' => $e->getMessage()]); return response()->json(['status' => 'error','message' => 'System health check failed.','timestamp' => now()->toIso8601String()], 503); }
        })->name('api.v1.health');
    });

    // --- Authenticated User Routes (Rate limit 'api_authenticated') ---
    // These routes require authentication via Sanctum PAT.
    Route::middleware(['auth:sanctum', 'throttle:api_authenticated'])->name('api.v1.user.')->group(function () { //
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout'); // F-USR-003

        // ★★★ この行を新規追加 (F-USR-009: アカウント削除) ★★★
        Route::delete('/auth/me', [AuthController::class, 'deleteAccount'])->name('auth.delete');
        // ★★★ 追加ここまで ★★★

        // Device Token Management
        Route::post('/device-tokens', [DeviceTokenController::class, 'store'])->name('device-tokens.store'); // FCM Token Reg

        // Game Routes (User perspective)
        Route::get('/games', [GameController::class, 'index'])->name('games.index');       // F-USR-005
        Route::get('/games/{game:game_id}', [GameController::class, 'show'])->name('games.show'); // F-USR-007 (Route Model Binding using game_id)

        // Participation Routes
        Route::post('/games/{game:game_id}/participations', [ParticipationController::class, 'store'])->name('games.participations.store'); // F-USR-006

        // Check-in Route
        Route::post('/games/{game:game_id}/checkin', [CheckinController::class, 'store'])->name('games.checkin.store'); // F-USR-008

        // TODO: Add route for '/api/v1/me' to get authenticated user details (recommended)
        // Route::get('/me', function (Request $request) { return new UserResource($request->user()); })->name('me');
    });

    // --- Admin Routes (Requires 'admin' middleware, Rate limit 'api_authenticated') ---
    // These routes require authentication AND admin privileges.
    Route::prefix('admin')->name('api.v1.admin.')->middleware(['auth:sanctum', 'admin', 'throttle:api_authenticated'])->group(function () { //
        // User Management (Admin)
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');             // F-ADM-001
        Route::get('/users/{user:user_id}', [AdminUserController::class, 'show'])->name('users.show'); // F-ADM-002 (Use user_id for binding)
        Route::delete('/users/{user:user_id}', [AdminUserController::class, 'destroy'])->name('users.destroy'); // F-ADM-003

        // Game Management (Admin - Full CRUD + List/Show)
        Route::post('/games', [AdminGameController::class, 'store'])->name('games.store');            // F-ADM-006
        Route::put('/games/{game:game_id}', [AdminGameController::class, 'update'])->name('games.update');     // F-ADM-007
        Route::delete('/games/{game:game_id}', [AdminGameController::class, 'destroy'])->name('games.destroy'); // F-ADM-008
        Route::get('/games', [AdminGameController::class, 'index'])->name('games.index');             // F-ADM-004
        Route::get('/games/{game:game_id}', [AdminGameController::class, 'show'])->name('games.show'); // F-ADM-005

        // Notification Management (Admin)
        Route::post('/notifications/push', [AdminNotificationController::class, 'sendPush'])->name('notifications.push');   // F-ADM-009
        Route::post('/notifications/email', [AdminNotificationController::class, 'sendEmail'])->name('notifications.email');  // F-ADM-010
    });

});

// Fallback route for undefined API endpoints (optional)
Route::fallback(function(){
    return response()->json([
        'error' => [
            'code' => 'E-404-00', // Example undefined endpoint code
            'message' => 'API endpoint not found.',
            'details' => []
         ],
         'meta' => ['timestamp' => now()->toIso8601String()]
    ], 404);
})->name('api.fallback'); // Name is optional