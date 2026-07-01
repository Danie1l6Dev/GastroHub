<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RestaurantSetting extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'description',
        'logo_path',
        'cover_image_path',
        'primary_color',
        'secondary_color',
        'address',
        'phone',
        'opening_hours',
        'instagram_url',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RestaurantSetting $setting): void {
            if (! $setting->slug) {
                $setting->slug = Str::slug($setting->name);
            }
        });
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function coverImageUrl(): string
    {
        return $this->cover_image_path
            ? Storage::disk('public')->url($this->cover_image_path)
            : asset('images/restaurant-hero.png');
    }

    public function safePrimaryColor(): string
    {
        return $this->safeColor($this->primary_color, '#CD0508');
    }

    public function safeSecondaryColor(): string
    {
        return $this->safeColor($this->secondary_color, '#000000');
    }

    private function safeColor(?string $color, string $fallback): string
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $color) === 1 ? $color : $fallback;
    }
}
