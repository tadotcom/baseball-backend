<?php

namespace App\Notifications;

use App\Models\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Send asynchronously (triggered by batch)
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon; // For date formatting
// TODO: Import FCM channel package specifics

class GameReminder extends Notification implements ShouldQueue
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
        // Title and Body based on PUSH-003 template
        $title = 'まもなく試合開始です';
        $formattedTime = Carbon::parse($this->game->game_date_time)->format('H:i'); // Format time
        $body = "「{$this->game->place_name}」 ( {$formattedTime} )";

        // --- Placeholder ---
         \Log::info("[Notification] Sending PUSH-003 to User {$notifiable->user_id}: Title='{$title}', Body='{$body}'");
         // MUST BE REPLACED with actual FCM message object.
         // Consider setting high priority depending on package.
         // Example Kreait: ->withHighestPossiblePriority()
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
            'message' => "まもなく試合開始です。「{$this->game->place_name}」",
        ];
    }
}