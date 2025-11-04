<?php

namespace App\Repositories;

use App\Models\DeviceToken;
use App\Models\User;

class DeviceTokenRepository
{
    /**
     * Create or update a device token record for a user.
     * Uses unique constraint on 'token' to decide update/insert.
     * @param User $user
     * @param string $token The FCM token.
     * @param string $deviceType 'ios' or 'android'.
     * @return DeviceToken The created or updated model.
     */
    public function upsertToken(User $user, string $token, string $deviceType): DeviceToken
    {
        // Use updateOrCreate:
        // 1st array: attributes to find the record (unique constraints)
        // 2nd array: attributes to set/update (including search attributes)
        return DeviceToken::updateOrCreate(
            ['token' => $token], // Find existing record by token (unique constraint)
            [
                'user_id' => $user->user_id, // Associate/Update user
                'device_type' => $deviceType, // Update device type if needed
                // 'updated_at' is handled automatically
            ]
        );

        // Alternative using firstOrNew / save:
        // $deviceToken = DeviceToken::firstOrNew(['token' => $token]);
        // $deviceToken->user_id = $user->user_id;
        // $deviceToken->device_type = $deviceType;
        // $deviceToken->save();
        // return $deviceToken;
    }

    /**
     * Remove a specific device token.
     * Useful if FCM reports the token as unregistered.
     * @param string $token
     * @return bool True if deleted, false otherwise.
     */
    public function deleteByToken(string $token): bool
    {
        $deletedCount = DeviceToken::where('token', $token)->delete();
        return $deletedCount > 0;
    }

    /**
     * Get all valid device tokens for a collection of users.
     * @param \Illuminate\Support\Collection<int, User> $users
     * @return \Illuminate\Support\Collection<int, string> Collection of token strings.
     */
    public function getTokensForUsers(\Illuminate\Support\Collection $users): \Illuminate\Support\Collection
    {
        if ($users->isEmpty()) {
            return collect();
        }
        $userIds = $users->pluck('user_id')->unique()->filter();
        if ($userIds->isEmpty()) {
            return collect();
        }

        // Fetch tokens only for the specified users
        return DeviceToken::whereIn('user_id', $userIds)->pluck('token');
    }

     /**
     * Get all valid device tokens for a single user.
     * @param User $user
     * @return \Illuminate\Support\Collection<int, string> Collection of token strings.
     */
    public function getTokensForUser(User $user): \Illuminate\Support\Collection
    {
        return $user->deviceTokens()->pluck('token'); // Use relationship
    }

}