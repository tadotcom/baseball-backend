<?php

namespace App\Notifications;

use App\Models\Game; // Game model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Send asynchronously
use Illuminate\Notifications\Messages\MailMessage; // Optional: If sending via email too
use Illuminate\Notifications\Notification;
// TODO: Import the correct FCM channel package (e.g., NotificationChannels/Fcm)
// use NotificationChannels\Fcm\FcmChannel;
// use NotificationChannels\Fcm\FcmMessage;
// use Kreait\Firebase\Messaging\CloudMessage; // Example using Kreait

class GameAdminRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public Game $game;

    /**
     * Create a new notification instance.
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    /**
     * Get the notification's delivery channels.
     * Specifies sending via FCM.
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // TODO: Replace 'fcm' with the actual channel name from the package used
        return ['fcm']; // Channel name depends on the FCM package installed
        // Example: return [FcmChannel::class];
    }

    /**
     * Get the FCM representation of the notification.
     * Defines the push notification payload.
     */
    public function toFcm(object $notifiable) // Return type depends on FCM package
    {
        // Title and Body based on PUSH-001 template
        $title = '試合登録完了';
        $body = "「{$this->game->place_name}」の試合を登録しました。";

        // --- Payload structure depends heavily on the FCM package used ---
        // Example using NotificationChannels/Fcm:
        /*
        return FcmMessage::create()
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($body))
            ->setData(['game_id' => $this->game->game_id, 'screen' => 'game_detail']); // Optional data payload
        */

        // Example using Kreait/Laravel-Firebase (might need a custom channel):
        /*
        return CloudMessage::withTarget('token', $notifiable->routeNotificationFor('fcm')) // Assumes routeNotificationFor defined in User model
             ->withNotification([
                  'title' => $title,
                  'body' => $body,
             ])
             ->withData(['game_id' => $this->game->game_id, 'screen' => 'game_detail']);
        */

        // --- Placeholder ---
         \Log::info("[Notification] Sending PUSH-001 to User {$notifiable->user_id}: Title='{$title}', Body='{$body}'");
         // Return a placeholder or throw error if package not configured
         // throw new \Exception("FCM Channel not configured for GameAdminRegistered");
         return null; // Placeholder - MUST BE REPLACED with actual FCM message object
         // --- End Placeholder ---
    }

    /**
     * Get the array representation of the notification. (Optional for database channel)
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'game_id' => $this->game->game_id,
            'message' => "「{$this->game->place_name}」の試合を登録しました。",
        ];
    }
}