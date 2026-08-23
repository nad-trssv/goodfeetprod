<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TechnicalSupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $employee,
        public readonly string $senderEmail,
        public readonly string $companyName,
        public readonly string $categoryLabel,
        public readonly string $requestSubject,
        public readonly string $requestMessage,
        public readonly ?string $pageUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->senderEmail, $this->companyName),
            replyTo: [new Address($this->employee->email, $this->employee->name)],
            subject: '[Support] '.$this->companyName.' — '.$this->requestSubject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.technical-support');
    }
}
