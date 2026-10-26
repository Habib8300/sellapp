<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeChart extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'chest_inches',
        'length_inches',
        'shoulder_inches',
        'chest_cm',
        'length_cm',
        'shoulder_cm',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'chest_inches' => 'decimal:2',
            'length_inches' => 'decimal:2',
            'shoulder_inches' => 'decimal:2',
            'chest_cm' => 'decimal:2',
            'length_cm' => 'decimal:2',
            'shoulder_cm' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
