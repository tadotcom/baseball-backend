<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Queue bulk pushes
use Illuminate\Notifications\Notification;
// TODO: Import FCM channel package specifics

class AdminBulkPush extends Notification implements ShouldQueue
{
    use Queueable;

    public string $pushTitle;
    public string $pushBody;

    /**
     * Create a new notification instance.
     * Receives title and body from admin input.
     */
    public function __construct(string $title, string $body)
    {
        $this->pushTitle = $title;
        $this->pushBody = $body;
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
        // Title and Body based on PUSH-007 template (admin input)
        $title = $this->pushTitle;
        $body = $this->pushBody;

        // --- Placeholder ---
         \Log::info("[Notification] Sending PUSH-007 to User {$notifiable->user_id}: Title='{$title}', Body='{$body}'");
         // MUST BE REPLACED with actual FCM message object.
         // Set high priority.
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
            'title' => $this->pushTitle,
            'message' => $this->pushBody,
        ];
    }
}