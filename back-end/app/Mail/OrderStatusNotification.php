<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    protected $order;
    public function __construct(Order $order)
    {
        //
        $this->order = $order->load(
            'status',
            'paymentStatus',
            'user',
            'orderItems.product',
            'orderItems.variant.attributValues.attribute',
            'city',
            'municipality'
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $ref = $this->order->order_number;
        $code = $this->order->status->code ?? null;

        $subject = match ($code) {
            'validated' => "Votre commande #{$ref} a été validée",
            'delivered' => "Votre commande #{$ref} a été livrée",
            'cancelled', 'canceled' => "Votre commande #{$ref} a été annulée",
            default => "Mise à jour de votre commande #{$ref}",
        };

        return new Envelope(subject: $subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.order_status',
            with: [
                'order' => $this->order,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
