<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: (Setting::query()->where('key', 'brand_name')->value('value') ?: 'DCU Jersey House')
                .' — '.$this->order->order_number.' update',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.order-status');
    }
}
