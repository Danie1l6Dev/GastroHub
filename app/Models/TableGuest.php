<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableGuest extends Model
{
    protected $fillable = [
        'table_session_id',
        'alias',
        'status',
        'is_ready',
        'ready_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'is_ready' => 'boolean',
            'ready_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function tableSession(): BelongsTo
    {
        return $this->belongsTo(TableSession::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
