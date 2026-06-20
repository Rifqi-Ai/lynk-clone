<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreatorSaleNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "💰 New sale: {$this->order->product->title} — Rp ".number_format($this->order->total, 0, ',', '.'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.creator-sale',
            with: [
                'order' => $this->order,
                'product' => $this->order->product,
                'buyer' => $this->order->buyer,
            ],
        );
    }
}
