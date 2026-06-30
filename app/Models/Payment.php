<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'table_session_id',
        'table_guest_id',
        'scope',
        'type',
        'amount',
        'status',
        'paid_at',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
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
}
