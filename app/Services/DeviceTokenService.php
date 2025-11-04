<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use App\Repositories\DeviceTokenRepository; //
use Illuminate\Support\Facades\Log;

class DeviceTokenService
{
    protected DeviceTokenRepository $deviceTokenRepository;

    public function __construct(DeviceTokenRepository $deviceTokenRepository)
    {
        $this->deviceTokenRepository = $deviceTokenRepository;
    }

    /**
     * Register or update a device token for a user.
     * Uses upsert logic based on the unique token.
     * @param User $user
     * @param string $token
     * @param string $deviceType
     * @return DeviceToken
     * @throws \Exception If DB operation fails unexpectedly.
     */
    public function registerOrUpdateToken(User $user, string $token, string $deviceType): DeviceToken
    {
        Log::info("[DeviceTokenService] Registering/Updating token.", [
            'user_id' => $user->user_id,
            'device_type' => $deviceType,
            // Avoid logging the full token in production if possible for security
            'token_snippet' => substr($token, 0, 10) . '...'
        ]);

        try {
            $deviceToken = $this->deviceTokenRepository->upsertToken($user, $token, $deviceType);
            Log::debug("[DeviceTokenService] Upsert successful.", ['device_token_id' => $deviceToken->device_token_id]);
            return $deviceToken;
        } catch (\Exception $e) {
             Log::error("[DeviceTokenService] Failed to upsert device token.", [
                 'user_id' => $user->user_id,
                 'error' => $e->getMessage()
             ]);
             // Rethrow exception to be handled by the controller/handler
             throw new \Exception("デバイストークンの登録に失敗しました。", 0, $e);
        }
    }

     /**
      * Remove unregistered tokens based on feedback from FCM (Optional).
      * @param array $unregisteredTokens
      * @return void
      */
     public function removeUnregisteredTokens(array $unregisteredTokens): void
     {
         if (empty($unregisteredTokens)) return;

         Log::info("[DeviceTokenService] Removing unregistered FCM tokens.", ['count' => count($unregisteredTokens)]);
         foreach ($unregisteredTokens as $token) {
             try {
                 $this->deviceTokenRepository->deleteByToken($token);
             } catch (\Exception $e) {
                 Log::error("[DeviceTokenService] Failed to delete unregistered token.", ['token_snippet' => substr($token, 0, 10).'...', 'error' => $e->getMessage()]);
                 // Continue processing other tokens
             }
         }
     }
}