<?php

namespace App\Notifications;

use App\Models\Game; // Game model
use App\Models\User; // User model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Send asynchronously
use Illuminate\Notifications\Notification;
// TODO: Import FCM channel package specifics

class GameParticipantRegistered extends Notification implements ShouldQueue
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
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // TODO: Replace 'fcm' with the actual channel name
        return ['fcm']; // Specify FCM channel
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable) // Return type depends on FCM package
    {
        // Title and Body based on PUSH-002 template
        $title = '参加登録完了';
        $body = "「{$this->game->place_name}」への参加登録が完了しました。";

        // --- Placeholder ---
         \Log::info("[Notification] Sending PUSH-002 to User {$notifiable->user_id}: Title='{$title}', Body='{$body}'");
         // MUST BE REPLACED with actual FCM message object based on chosen package
         return null;
         // --- End Placeholder ---
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'game_id' => $this->game->game_id,
            'message' => "「{$this->game->place_name}」への参加登録が完了しました。",
        ];
    }
}