<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'status',
        'amount',
        'transaction_reference',
        'provider_data',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'provider_data' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
