<?php

namespace App\Services\Admin;

use App\Models\Game;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\GameRepository; // If targeting game participants
use App\Repositories\ParticipationRepository; // If targeting game participants
use App\Repositories\DeviceTokenRepository; // To get FCM tokens
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // Notification facade
use Illuminate\Support\Facades\Mail; // Mail facade
use App\Notifications\AdminBulkPush; // PUSH-007
use App\Mail\AdminBulkEmail; // MAIL-007
use Illuminate\Support\Collection; // For handling collections of users/tokens

class NotificationService
{
    protected UserRepository $userRepository;
    protected ParticipationRepository $participationRepository;
    protected DeviceTokenRepository $deviceTokenRepository;

    public function __construct(
        UserRepository $userRepository,
        ParticipationRepository $participationRepository,
        DeviceTokenRepository $deviceTokenRepository
    ) {
        $this->userRepository = $userRepository;
        $this->participationRepository = $participationRepository;
        $this->deviceTokenRepository = $deviceTokenRepository;
    }

    /**
     * Send bulk push notifications based on target criteria. (F-ADM-009)
     * @param string $title
     * @param string $body
     * @param array $target ('type' => 'all'|'game'|'users', 'game_id' => ?, 'user_ids' => [])
     * @return array{status: string, success_count: int, failure_count: int}
     */
    public function sendBulkPushNotification(string $title, string $body, array $target): array
    {
        Log::info("[Admin\NotificationService] Sending bulk push notification.", compact('title', 'target'));

        $targetUsers = $this->getTargetUsers($target);

        if ($targetUsers->isEmpty()) {
            Log::warning("[Admin\NotificationService] No target users found for push notification.", compact('target'));
             // Return success but indicate no recipients found -> Controller handles this
             // Abort here?
             abort(400, 'E-400-05: 配信対象が見つかりません');
        }

        // --- Sending Logic ---
        // Option 1: Send individually (simpler, less efficient for large numbers)
        $successCount = 0;
        $failureCount = 0;
        $unregisteredTokens = []; // To collect invalid tokens

        $usersToNotify = $targetUsers->loadMissing('deviceTokens'); // Eager load tokens

        foreach ($usersToNotify as $user) {
            // Check again if user has tokens after loading
            if ($user->deviceTokens->isEmpty()) {
                 Log::debug("[Admin\NotificationService] Skipping user (no tokens): {$user->user_id}");
                 continue;
            }
            try {
                 // Create and send the notification instance
                 // Using sendNow for potentially large immediate batches, queue might be better
                 Notification::sendNow($user, new AdminBulkPush($title, $body));
                 $successCount++;
                 Log::debug("[Admin\NotificationService] Push sent to user: {$user->user_id}");
            } catch (\Exception $e) {
                 $failureCount++;
                 Log::error("[Admin\NotificationService] Failed to send push to user.", [
                     'user_id' => $user->user_id,
                     'error' => $e->getMessage()
                 ]);
                 // TODO: Parse exception to check for unregistered tokens and add to $unregisteredTokens
                 // This depends heavily on the FCM package's exception handling
            }
        }

        // Option 2: Send using FCM multicast (more efficient, requires package support)
        // $tokens = $this->deviceTokenRepository->getTokensForUsers($targetUsers);
        // if ($tokens->isNotEmpty()) {
        //     // Use FCM package's multicast send method
        //     // Example: $result = Fcm::sendTo($tokens->toArray(), ...);
        //     // Parse $result for success/failure counts and unregistered tokens
        // }

         // --- Cleanup unregistered tokens ---
         // if (!empty($unregisteredTokens)) {
         //     app(DeviceTokenService::class)->removeUnregisteredTokens($unregisteredTokens);
         // }

        Log::info("[Admin\NotificationService] Bulk push completed.", [
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'target_count' => $targetUsers->count()
        ]);

        return [
            'status' => $failureCount === 0 ? 'success' : 'partial_failure',
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ];
    }

     /**
     * Send bulk emails based on target criteria. (F-ADM-010)
     * @param string $subject
     * @param string $body (HTML)
     * @param array $target ('type' => 'all'|'game'|'users', 'game_id' => ?, 'user_ids' => [])
     * @return array{status: string, queued_count: int}
     */
    public function sendBulkEmail(string $subject, string $body, array $target): array
    {
         Log::info("[Admin\NotificationService] Queueing bulk email.", compact('subject', 'target'));
         $targetUsers = $this->getTargetUsers($target);

         if ($targetUsers->isEmpty()) {
             Log::warning("[Admin\NotificationService] No target users found for bulk email.", compact('target'));
             abort(400, 'E-400-05: 配信対象が見つかりません');
         }

         $queuedCount = 0;
         foreach ($targetUsers as $user) {
             try {
                 // Queue the email
                 Mail::to($user->email)->queue(new AdminBulkEmail($user, $subject, $body));
                 $queuedCount++;
                 Log::debug("[Admin\NotificationService] Email queued for user: {$user->user_id}");
             } catch (\Exception $e) {
                  Log::error("[Admin\NotificationService] Failed to queue email for user.", [
                      'user_id' => $user->user_id,
                      'error' => $e->getMessage()
                  ]);
                  // Continue queuing for other users
             }
         }

          Log::info("[Admin\NotificationService] Bulk email queueing completed.", [
            'queued_count' => $queuedCount,
            'target_count' => $targetUsers->count()
        ]);

         // Consider it successful if at least one email was queued? Adjust logic as needed.
         return [
             'status' => $queuedCount > 0 ? 'success' : 'failed',
             'queued_count' => $queuedCount,
         ];
    }

    /**
     * Helper method to retrieve target User collection based on criteria.
     * @param array $target
     * @return Collection<int, User>
     */
    protected function getTargetUsers(array $target): Collection
    {
        $type = $target['type'] ?? 'all';
        $users = collect(); // Initialize empty collection

        switch ($type) {
            case 'game':
                $gameId = $target['game_id'] ?? null;
                if ($gameId) {
                    // Get participants of the specific game (excluding soft-deleted users)
                    $users = $this->participationRepository->getUsersByGame($gameId);
                     Log::debug("[Admin\NotificationService] Target type 'game': Found {$users->count()} participants for game {$gameId}.");
                }
                break;
            case 'users':
                $userIds = $target['user_ids'] ?? null;
                if (!empty($userIds) && is_array($userIds)) {
                     // Get specific users by ID (excluding soft-deleted users)
                     $users = User::whereIn('user_id', $userIds)->get();
                      Log::debug("[Admin\NotificationService] Target type 'users': Found {$users->count()} users from provided IDs.");
                }
                break;
            case 'all':
            default:
                 // Get all active users
                 // IMPORTANT: For large user bases, fetch in chunks or use a job queue system
                 // to avoid memory issues. This simple approach assumes a moderate number of users.
                 $users = User::whereNull('deleted_at')->get(); // Get only active users
                  Log::debug("[Admin\NotificationService] Target type 'all': Found {$users->count()} active users.");
                break;
        }

        return $users;
    }
}