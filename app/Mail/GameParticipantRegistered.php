<?php

namespace App\Mail;

use App\Models\Game;
use App\Models\Participation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; //
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon; // For date formatting

class GameParticipantRegistered extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Game $game;
    // Optional: Pass the specific participation details if needed
    // public Participation $participation;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Game $game /*, Participation $participation*/)
    {
        $this->user = $user;
        $this->game = $game;
        // $this->participation = $participation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
             from: config('mail.from.address', 'noreply@yourdomain.com'),
             subject: '【草野球マッチング】試合参加登録完了のお知らせ', //
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.game_participant_registered', // Blade template path
            with: [
                'nickname' => $this->user->nickname,
                'place_name' => $this->game->place_name,
                // Format date/time for display
                'game_date_time' => Carbon::parse($this->game->game_date_time)->format('Y年m月d日 H:i'),
                'address' => $this->game->address,
                'fee' => $this->game->fee ?? 0, // Handle null fee
            ],
             // text: 'emails.game_participant_registered_plain',
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