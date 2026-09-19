<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Contact $contact)
    {
    }

    /**
     * Get the message envelope.
     * Replying to this email goes straight to the customer.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Contact Message: ' . $this->contact->subject,
            replyTo: [new Address($this->contact->email, $this->contact->name)],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message',
        );
    }
}
