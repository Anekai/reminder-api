<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovaSolicitacaoSuporteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $title;
    public $description;
    public $type;
    public $priority;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $title, $description, $type, $priority)
    {
        $this->user = $user;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->priority = $priority;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . $this->priority . '][' . $this->type . '] Nova solicitacao de suporte',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.nova-solicitacao-suporte',
        );
    }
}
