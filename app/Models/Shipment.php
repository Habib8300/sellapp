<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'courier',
        'tracking_number',
        'tracking_url',
        'status',
        'cod_amount',
        'booked_at',
    ];

    protected function casts(): array
    {
        return ['cod_amount' => 'decimal:2', 'booked_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
