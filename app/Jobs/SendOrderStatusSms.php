<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SendOrderStatusSms implements ShouldQueue
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
        $settings = Setting::query()->whereIn('key', ['sms_gateway_url', 'sms_api_key', 'sms_sender_id'])
            ->pluck('value', 'key');
        $endpoint = $settings->get('sms_gateway_url');
        $apiKey = $settings->get('sms_api_key');

        if (blank($endpoint) || blank($apiKey)) {
            return;
        }

        $status = str_replace('_', ' ', $order->status);
        $message = "Dhakebir Jersey House: Order {$order->order_number} is now {$status}.";
        $response = Http::asForm()->timeout(15)->post($endpoint, [
            'api_key' => $apiKey,
            'sender_id' => $settings->get('sms_sender_id'),
            'number' => $order->customer_phone,
            'message' => $message,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('SMS gateway returned HTTP '.$response->status().'.');
        }
    }
}
