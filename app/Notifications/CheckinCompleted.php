<?php

namespace App\Notifications;

use App\Models\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Queue check-in confirmation
use Illuminate\Notifications\Notification;
// TODO: Import FCM channel package specifics

class CheckinCompleted extends Notification implements ShouldQueue
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
        return ['fcm'];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable) // Return type depends on FCM package
    {
        // Title and Body based on PUSH-006 template
        $title = 'チェックイン完了';
        $body = "「{$this->game->place_name}」へのチェックインが完了しました。";

        // --- Placeholder ---
         \Log::info("[Notification] Sending PUSH-006 to User {$notifiable->user_id}: Title='{$title}', Body='{$body}'");
         // MUST BE REPLACED with actual FCM message object. Normal priority.
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
            'message' => "「{$this->game->place_name}」へのチェックインが完了しました。",
        ];
    }
}