<?php

namespace App\Models;

use App\Enums\TableAccountMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableSession extends Model
{
    protected $fillable = [
        'dining_table_id',
        'status',
        'account_mode',
        'opened_at',
        'closed_at',
        'confirmed_at',
        'confirmed_by_guest_id',
    ];

    protected function casts(): array
    {
        return [
            'account_mode' => TableAccountMode::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(TableGuest::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function confirmedByGuest(): BelongsTo
    {
        return $this->belongsTo(TableGuest::class, 'confirmed_by_guest_id');
    }
}
