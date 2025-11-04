<?php

namespace App\Mail;

use App\Models\User; // Assuming User model is in App\Models
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // For asynchronous sending
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountRegistered extends Mailable implements ShouldQueue // Implement ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user; // Public property to pass user data to the view

    /**
     * Create a new message instance.
     * Receives the User object upon registration.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     * Defines subject and sender.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address', 'noreply@yourdomain.com'), //
            subject: '【草野球マッチング】アカウント登録完了', //
        );
    }

    /**
     * Get the message content definition.
     * Specifies the Blade view and passes data.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.account_registered', // Blade template path
            with: [ // Data passed to the view
                'nickname' => $this->user->nickname,
            ],
            // Optional: Define text fallback
            // text: 'emails.account_registered_plain',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}