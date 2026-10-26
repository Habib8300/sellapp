<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'color',
        'color_code',
        'price_adjustment',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (self $variant): void {
            if ($variant->stock_quantity > 0) {
                $variant->recordStockMovement(0, $variant->stock_quantity, 'Initial stock');
            }
        });

        static::updated(function (self $variant): void {
            if ($variant->wasChanged('stock_quantity')) {
                $variant->recordStockMovement(
                    (int) $variant->getOriginal('stock_quantity'),
                    (int) $variant->stock_quantity,
                    'Admin stock adjustment',
                );
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    private function recordStockMovement(int $before, int $after, string $reason): void
    {
        $this->stockMovements()->create([
            'user_id' => auth()->id(),
            'quantity_change' => $after - $before,
            'stock_before' => $before,
            'stock_after' => $after,
            'reason' => $reason,
        ]);
    }
}
