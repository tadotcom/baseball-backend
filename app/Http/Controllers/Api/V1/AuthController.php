<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\PasswordUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password; // For password reset
use Illuminate\Auth\Events\PasswordReset; // For password reset
use Illuminate\Support\Str; // For Str::random

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle a registration request. (F-USR-001)
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $result = $this->authService->registerUser($validatedData); // Service handles logic

            // Return user and token
            return response()->json([
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ],
                'meta' => ['timestamp' => now()->toIso8601String()]
            ], 201); // 201 Created

        } catch (\Exception $e) {
            // Let Handler.php handle the exception formatting
            throw $e;
        }
    }

    /**
     * Handle a login request. (F-USR-002)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        try {
            $result = $this->authService->loginUser($credentials); // Service handles logic

            if (!$result) {
                 // Service indicates authentication failure
                abort(401, 'E-401-02: メールアドレスまたはパスワードが正しくありません');
            }

            // Return user and token
            return response()->json([
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ],
                'meta' => ['timestamp' => now()->toIso8601String()]
            ], 200); // 200 OK

        } catch (\Exception $e) {
             throw $e;
        }
    }

    /**
     * Handle a logout request. (F-USR-003)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logoutUser($request->user()); // Pass authenticated user

            return response()->json([
                'data' => ['message' => 'ログアウトしました。'],
                'meta' => ['timestamp' => now()->toIso8601String()]
            ], 200);

        } catch (\Exception $e) {
             throw $e;
        }
    }

    // ★★★ 以下を新規追加 (F-USR-009: アカウント削除) ★★★
    /**
     * Handle an account deletion request. (F-USR-009)
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            // AuthService の deleteAccount メソッドを呼び出す
            $this->authService->deleteAccount($request->user());

            // 成功したら 204 No Content を返す
            return response()->json(null, 204);

        } catch (\Exception $e) {
            throw $e; // エラーハンドラに委譲
        }
    }
    // ★★★ 追加ここまで ★★★


    /**
     * Handle password reset request (send link). (F-USR-004 Part 1)
     */
    public function sendResetLinkEmail(PasswordResetRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $status = $this->authService->sendPasswordResetLink($validatedData); // Service handles logic

            if ($status !== Password::RESET_LINK_SENT) {
                 // Handle failure case based on status
                 // Just return a generic success to prevent email enumeration
            }

            return response()->json([
                'data' => ['message' => 'パスワードリセット用のメールを送信しました。'],
                'meta' => ['timestamp' => now()->toIso8601String()]
            ], 200);

        } catch (\Exception $e) {
             throw $e;
        }
    }

     /**
     * Handle password reset execution. (F-USR-004 Part 2)
     */
    public function resetPassword(PasswordUpdateRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
             $status = $this->authService->resetPassword($validatedData); // Service handles logic

            if ($status !== Password::PASSWORD_RESET) {
                 // Handle different failure statuses (INVALID_TOKEN, INVALID_USER etc.)
                  abort(400, $this->getResetPasswordError($status)); // Return error based on status
            }

             // Password was reset successfully
             return response()->json([
                 'data' => ['message' => 'パスワードが正常にリセットされました。'],
                 'meta' => ['timestamp' => now()->toIso8601String()]
             ], 200);

        } catch (\Exception $e) {
             throw $e;
        }
    }

     /**
     * Helper to get error message based on PasswordBroker status.
     */
    protected function getResetPasswordError(string $status): string {
         return match ($status) {
             Password::INVALID_TOKEN => 'E-400-06: パスワードリセットトークンが無効または期限切れです',
             Password::INVALID_USER => 'E-404-01: ユーザーが見つかりません',
             default => 'パスワードのリセットに失敗しました。',
         };
    }

}