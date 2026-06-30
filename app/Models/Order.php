<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'table_session_id',
        'table_guest_id',
        'status',
        'subtotal',
        'total',
        'notes',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
        ];
    }

    public function tableSession(): BelongsTo
    {
        return $this->belongsTo(TableSession::class);
    }

    public function tableGuest(): BelongsTo
    {
        return $this->belongsTo(TableGuest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
