<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class BookingAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

     public $email, $price, $service_name, $client_name, $client_email, $client_phone, $mastername, $booking_date, $booking_start, $booking_end, $company_address, $price_can_change, $company_phone, $company_email;

     public function __construct( $email, $service_name, $client_name, $client_email, $client_phone, $mastername, $booking_date, $booking_start, $booking_end, $company_address, $price_can_change, $company_phone, $company_email, $price)
     {
        $this->email = $email;
        $this->service_name = $service_name;
        $this->client_name = $client_name;
        $this->client_email = $client_email;
        $this->client_phone = $client_phone;
        $this->mastername = $mastername;
        $this->booking_date = $booking_date;
        $this->booking_start = $booking_start;
        $this->booking_end = $booking_end;
        $this->company_address = $company_address;
        $this->price_can_change = $price_can_change;
        $this->company_phone = $company_phone;
        $this->company_email = $company_email;
        $this->price = $price;
     }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('info@goodfeet.ee', 'GoodFeet OU'),
            subject: 'Teie teenusele on tehtud broneering!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bookingAdmin',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
        ];
    }
}
