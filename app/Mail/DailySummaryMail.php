<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySummaryMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $user;

    public $summary;

    public $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $summary = [], $customMessage = null)
    {
        $this->user = $user;
        $this->summary = $summary;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        return $this->markdown('emails.daily-summary')
            ->with([
                'user' => $this->user,
                'summary' => $this->summary,
                'customMessage' => $this->customMessage,
            ])
            ->subject('Daily Summary');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Summary Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.daily-summary',
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
