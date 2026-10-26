<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'department',
        'batch',
        'quantity',
        'details',
        'status',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }
}
