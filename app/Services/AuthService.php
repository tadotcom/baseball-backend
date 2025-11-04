<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash; // Password hashing
use Illuminate\Support\Facades\Password; // Password Broker facade
use Illuminate\Support\Facades\Mail; // Mail facade
use App\Mail\AccountRegistered; // Mailable
use App\Mail\PasswordResetLink; // Mailable
use App\Mail\PasswordResetSuccess; // Mailable
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent; // Event after reset
use Illuminate\Support\Facades\Log; // Logging

class AuthService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user. (F-USR-001)
     */
    public function registerUser(array $data): array
    {
        // Password hashing is handled by the User model $casts
        $user = $this->userRepository->create($data);

        if (!$user) {
            Log::error("[AuthService] User creation failed in repository.", $data);
            throw new \Exception("ユーザー登録中にエラーが発生しました。");
        }
        Log::info("[AuthService] User registered successfully.", ['user_id' => $user->user_id, 'email' => $user->email]);

        // Create Sanctum token (PAT)
        $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken; // Explicit 24h expiry

        // Queue the registration email
        try {
             Mail::to($user->email)->queue(new AccountRegistered($user));
             Log::info("[AuthService] AccountRegistered email queued.", ['user_id' => $user->user_id]);
        } catch (\Exception $e) {
             Log::error("[AuthService] Failed to queue AccountRegistered email.", ['user_id' => $user->user_id, 'error' => $e->getMessage()]);
             // Do not fail the registration if email queue fails
        }


        return ['user' => $user, 'token' => $token];
    }

    /**
     * Attempt to log in a user. (F-USR-002)
     */
    public function loginUser(array $credentials): ?array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        // Check if user exists and password is correct (ignoring soft-deleted users)
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            Log::warning("[AuthService] Login failed for email.", ['email' => $credentials['email']]);
            return null; // Indicate login failure
        }

        Log::info("[AuthService] User login successful.", ['user_id' => $user->user_id, 'email' => $user->email]);

        // Create new Sanctum token (PAT)
        $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken; // Explicit 24h expiry

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Log out a user by revoking their current token. (F-USR-003)
     */
    public function logoutUser(User $user): void
    {
        // Revoke the token that was used to authenticate the current request
        $user->currentAccessToken()->delete();
        Log::info("[AuthService] User token revoked (logout).", ['user_id' => $user->user_id]);
    }

    /**
     * Send the password reset link email. (F-USR-004 Part 1)
     */
    public function sendPasswordResetLink(array $data): string
    {
        Log::info("[AuthService] Password reset requested.", ['email' => $data['email']]);
        
        $status = Password::broker()->sendResetLink(
            $data, 
            function (User $user, string $token) {
                 // TODO: Define frontend URL in config/env
                 $frontendUrl = config('app.frontend_url', 'http://localhost:8080'); // Example
                 $resetUrl = $frontendUrl . '/password/reset/' . $token . '?email=' . urlencode($user->email);

                 // Send the email synchronously
                 Mail::to($user->email)->send(new PasswordResetLink($user, $resetUrl));
                 Log::info("[AuthService] PasswordResetLink email sent.", ['user_id' => $user->user_id]);
            }
        );

        if ($status !== Password::RESET_LINK_SENT) {
             Log::warning("[AuthService] Failed to send password reset link.", ['email' => $data['email'], 'status' => $status]);
        }

        return $status;
    }

     /**
     * Reset the user's password using token. (F-USR-004 Part 2)
     */
    public function resetPassword(array $data): string
    {
         Log::info("[AuthService] Password reset attempt.", ['email' => $data['email']]);
         
         $status = Password::broker()->reset(
             $data,
             function (User $user, string $password) {
                 // Update the user's password (model handles hashing)
                 $user->forceFill([
                     'password' => $password,
                 ])->save();

                 event(new PasswordResetEvent($user));
                 Log::info("[AuthService] Password reset successful.", ['user_id' => $user->user_id]);

                 // Send success notification email (queued)
                 try {
                      Mail::to($user->email)->queue(new PasswordResetSuccess($user));
                      Log::info("[AuthService] PasswordResetSuccess email queued.", ['user_id' => $user->user_id]);
                 } catch (\Exception $e) {
                      Log::error("[AuthService] Failed to queue PasswordResetSuccess email.", ['user_id' => $user->user_id, 'error' => $e->getMessage()]);
                 }
             }
         );

         if ($status !== Password::PASSWORD_RESET) {
              Log::warning("[AuthService] Password reset failed.", ['email' => $data['email'], 'status' => $status]);
         }

         return $status;
    }

    // ★★★ 以下を新規追加 (F-USR-009: アカウント削除) ★★★
    /**
     * F-USR-009: アカウントを論理削除する
     *
     * @param User $user The authenticated user model.
     * @throws \Exception
     */
    public function deleteAccount(User $user): void
    {
        try {
            // 1. 全てのAPIトークンを無効化 (ログアウト)
            $user->tokens()->delete();
            Log::info('User tokens revoked for account deletion.', ['user_id' => $user->user_id]);

            // 2. ユーザーを論理削除 (SoftDeletes)
            // (Repository を介さず直接 Model を操作していますが、
            // $user は既に取得済みの Model インスタンスなのでこれでOKです)
            $user->delete();
            
            // 3. 操作ログを記録
            Log::info('User account deleted (soft)', [
                'user_id' => $user->user_id,
                'email' => $user->email,
            ]);

            // (オプション: MAIL-006 強制退会通知と同様のメールを送信)
            // Mail::to($user->email)->queue(new AccountDeleted($user));

        } catch (\Exception $e) {
            Log::error('Account deletion failed', [
                'user_id' => $user->user_id, 
                'exception' => $e->getMessage()
            ]);
            // E-500-03 (サーバーエラー)
            throw new \Exception('E-500-03: サーバーエラーが発生しました');
        }
    }
    // ★★★ 追加ここまで ★★★
}