<?php

namespace App\Mail;

use App\Models\Appointments;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCustomerMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Appointments $appointment,
        public readonly string $messageSubject,
        public readonly string $messageBody,
        public readonly string $senderName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->messageSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.customer-appointment-message');
    }
}
