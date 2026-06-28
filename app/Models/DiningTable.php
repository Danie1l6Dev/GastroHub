<?php

namespace App\Models;

use App\Enums\TableStatus;
use Database\Factories\DiningTableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiningTable extends Model
{
    /** @use HasFactory<DiningTableFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'qr_token',
        'capacity',
        'is_active',
        'current_status',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'current_status' => TableStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DiningTable $table): void {
            $table->qr_token ??= static::generateQrToken();
            $table->current_status ??= TableStatus::Available;
        });
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TableSession::class);
    }

    public function qrUrl(): string
    {
        return route('tables.join', $this->qr_token);
    }

    public function regenerateQrToken(): void
    {
        DB::transaction(function (): void {
            do {
                $token = static::generateQrToken();
            } while (static::where('qr_token', $token)->whereKeyNot($this->id)->exists());

            $this->sessions()
                ->where('status', 'open')
                ->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);

            $this->forceFill([
                'qr_token' => $token,
                'current_status' => TableStatus::Available,
            ])->save();
        });
    }

    public static function generateQrToken(): string
    {
        return Str::random(48);
    }
}
