<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $typeLabel = $this->order->product->typeLabel ?? 'Order';

        return new Envelope(
            subject: "✅ Order confirmed: {$this->order->product->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order-confirmation',
            with: [
                'order' => $this->order,
                'product' => $this->order->product,
                'creator' => $this->order->creator,
            ],
        );
    }
}
