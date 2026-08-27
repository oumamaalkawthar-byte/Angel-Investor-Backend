<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SiteMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $mailSubject;
    public string $headline;
    public array $lines;
    public string $intro;
    public string $footer;

    protected ?string $attachmentPath = null;
    protected ?string $attachmentName = null;

    public function __construct(
        string $subject,
        string $headline = '',
        array $lines = [],
        string $intro = '',
        string $footer = ''
    ) {
        $this->mailSubject = $subject;
        $this->headline = $headline ?: $subject;
        $this->lines = $lines;
        $this->intro = $intro;
        $this->footer = $footer;
    }

    public function attachFile(string $path, ?string $name = null): self
    {
        $this->attachmentPath = $path;
        $this->attachmentName = $name;
        return $this;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.site-mail');
    }

    public function attachments(): array
    {
        if ($this->attachmentPath && is_file($this->attachmentPath)) {
            return [
                Attachment::fromPath($this->attachmentPath)
                    ->as($this->attachmentName ?: basename($this->attachmentPath)),
            ];
        }

        return [];
    }
}
