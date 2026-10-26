<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'department',
        'batch',
        'description',
        'product_id',
        'deadline',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['deadline' => 'datetime', 'is_active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function campaignOrders(): HasMany
    {
        return $this->hasMany(CampaignOrder::class);
    }
}
