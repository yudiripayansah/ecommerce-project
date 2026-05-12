<?php

namespace App\Mail\Customer;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Customer $customer) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Selamat datang di ' . config('app.name') . '!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.customer.welcome',
        );
    }
}
