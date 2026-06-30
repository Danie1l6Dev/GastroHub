<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'table_guest_id',
        'product_id',
        'product_name',
        'unit_price',
        'quantity',
        'line_total',
        'notes',
    ];

    public function tableGuest(): BelongsTo
    {
        return $this->belongsTo(TableGuest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
