<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TableGuest extends Model
{
    protected $fillable = [
        'table_session_id',
        'alias',
        'guest_token',
        'joined_at',
        'status',
        'is_ready',
        'ready_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'is_ready' => 'boolean',
            'ready_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TableGuest $guest): void {
            $guest->guest_token ??= Str::random(48);
            $guest->joined_at ??= now();
        });
    }

    public function tableSession(): BelongsTo
    {
        return $this->belongsTo(TableSession::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
