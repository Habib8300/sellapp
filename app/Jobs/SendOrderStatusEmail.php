<?php

namespace App\Jobs;

use App\Mail\OrderStatusMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $orderId) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $order = Order::query()->findOrFail($this->orderId);

        if (blank($order->customer_email)) {
            return;
        }

        Mail::to($order->customer_email)->send(new OrderStatusMail($order->load('items')));
    }
}
