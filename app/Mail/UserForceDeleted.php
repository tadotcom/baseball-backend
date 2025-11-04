<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Can be queued
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserForceDeleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user; // The user being notified

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
             from: config('mail.from.address', 'noreply@yourdomain.com'),
             subject: '【草野球マッチング】アカウント停止のお知らせ', //
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user_force_deleted', // Blade template path
            with: [
                'nickname' => $this->user->nickname,
            ],
            // text: 'emails.user_force_deleted_plain',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}