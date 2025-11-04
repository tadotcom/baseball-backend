<?php

namespace App\Mail;

use App\Models\Game;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Important event, queue it
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class GameCancelled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user; // The recipient (participant)
    public Game $game;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Game $game)
    {
        $this->user = $user;
        $this->game = $game;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
             from: config('mail.from.address', 'noreply@yourdomain.com'),
             subject: '【草野球マッチング】試合中止のお知らせ', //
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.game_cancelled', // Blade template path
            with: [
                'nickname' => $this->user->nickname,
                'place_name' => $this->game->place_name,
                'game_date_time' => Carbon::parse($this->game->game_date_time)->format('Y年m月d日 H:i'),
            ],
            // text: 'emails.game_cancelled_plain',
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