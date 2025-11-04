<?php

namespace App\Mail;

use App\Models\User; // Assuming User model
use Illuminate\Bus\Queueable;
// Password reset link is usually sent synchronously, so ShouldQueue is omitted
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetLink extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $resetUrl; // The URL containing the reset token

    /**
     * Create a new message instance.
     * Receives the User and the generated reset URL.
     */
    public function __construct(User $user, string $resetUrl)
    {
        $this->user = $user;
        $this->resetUrl = $resetUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
             from: config('mail.from.address', 'noreply@yourdomain.com'),
             subject: '【草野球マッチング】パスワードリセットのご案内', //
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password_reset_link', // Blade template path
            with: [
                'nickname' => $this->user->nickname,
                'reset_url' => $this->resetUrl, // Pass the URL to the view
            ],
            // text: 'emails.password_reset_link_plain',
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