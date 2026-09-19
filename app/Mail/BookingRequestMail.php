<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRequestMail extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Booking $booking)
    {
    }

    /**
     * Get the message envelope.
     * Replying to this email goes straight to the customer.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Booking Request: ' . $this->booking->package_name . ' - ' . $this->booking->name,
            replyTo: [new Address($this->booking->email, $this->booking->name)],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-request',
        );
    }
}
