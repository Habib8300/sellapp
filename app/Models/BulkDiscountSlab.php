<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkDiscountSlab extends Model
{
    protected $fillable = ['minimum_quantity', 'discount_percent', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'discount_percent' => 'decimal:2'];
    }
}
