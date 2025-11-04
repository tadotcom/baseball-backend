<?php

namespace App\Mail;

use App\Models\User; // The recipient
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Definitely queue bulk emails
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminBulkEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user; // The recipient
    public string $emailSubject; // Subject defined by admin
    public string $emailBody; // Body (HTML) defined by admin

    /**
     * Create a new message instance.
     * Receives recipient user, subject, and body from the admin request.
     */
    public function __construct(User $user, string $subject, string $body)
    {
        $this->user = $user;
        $this->emailSubject = $subject;
        $this->emailBody = $body; // This is the raw HTML body from admin
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
             from: config('mail.from.address', 'noreply@yourdomain.com'),
             subject: '【草野球マッチング】' . $this->emailSubject, // Use admin-defined subject
             // Reply-To might be useful here if admin wants replies
             // replyTo: [new Address('admin@yourdomain.com', '運営事務局')],
        );
    }

    /**
     * Get the message content definition.
     * We pass the raw HTML body directly to the view.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_bulk_email', // Blade template path
            with: [
                'nickname' => $this->user->nickname,
                'title' => $this->emailSubject, // Pass subject as title too
                'body' => $this->emailBody, // Pass the raw HTML body
            ],
            // text: 'emails.admin_bulk_email_plain', // Need a way to convert HTML to plain text
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