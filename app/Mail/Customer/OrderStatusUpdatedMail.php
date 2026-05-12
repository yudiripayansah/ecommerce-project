<?php

namespace App\Mail\Customer;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $oldStatus) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update pesanan ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.customer.order-status-updated',
        );
    }
}
