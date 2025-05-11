<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UpcomingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $body;
    public $type;
    public $itemId;
    public $itemTitle;
    public $timeUntil;

    /**
     * Create a new message instance.
     */
    public function __construct($title, $body, $type, $itemId, $itemTitle, $timeUntil)
    {
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
        $this->itemId = $itemId;
        $this->itemTitle = $itemTitle;
        $this->timeUntil = $timeUntil;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.upcoming-notification',
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
