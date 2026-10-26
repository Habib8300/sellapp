<?php

namespace App\Models;

use App\Enums\ProductFit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'name_bn',
        'slug',
        'sku',
        'description',
        'fabric_type',
        'gsm',
        'fit',
        'collar_type',
        'sleeve_type',
        'price',
        'compare_at_price',
        'is_active',
        'is_limited_edition',
        'is_preorder',
        'customization_enabled',
        'customization_price',
        'preorder_estimated_delivery_at',
        'preorder_close_at',
        'size_guide',
    ];

    protected function casts(): array
    {
        return [
            'fit' => ProductFit::class,
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_limited_edition' => 'boolean',
            'is_preorder' => 'boolean',
            'customization_enabled' => 'boolean',
            'customization_price' => 'decimal:2',
            'preorder_estimated_delivery_at' => 'date',
            'preorder_close_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class)->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function sizeCharts(): HasMany
    {
        return $this->hasMany(SizeChart::class)->orderBy('sort_order');
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->is_preorder && (! $this->preorder_close_at || $this->preorder_close_at->isToday() || $this->preorder_close_at->isFuture())) {
            return 'preorder';
        }

        $variants = $this->relationLoaded('variants')
            ? $this->variants->where('is_active', true)
            : $this->variants()->where('is_active', true)->get(['stock_quantity', 'low_stock_threshold']);

        if ($variants->isEmpty()) {
            return 'out_of_stock';
        }

        $stock = $variants->sum('stock_quantity');
        $threshold = $variants->sum('low_stock_threshold');

        return match (true) {
            $stock <= 0 => 'out_of_stock',
            $stock <= $threshold => 'low_stock',
            default => 'in_stock',
        };
    }
}
