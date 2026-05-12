<?php

namespace App\Mail\Admin;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Customer $customer) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pelanggan Baru: ' . $this->customer->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin.new-customer',
        );
    }
}
