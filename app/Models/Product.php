<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'image_path',
        'is_available',
        'is_featured',
        'position',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'price' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $source = $product->isDirty('name') && ! $product->isDirty('slug')
                ? $product->name
                : ($product->slug ?: $product->name);

            $product->slug = static::uniqueSlug($source, $product->id);
            $product->position = $product->sort_order ?? $product->position ?? 0;
            $product->sort_order = $product->sort_order ?? $product->position ?? 0;
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function formattedPrice(): string
    {
        return '$'.number_format($this->price, 0, ',', '.');
    }

    public function imageUrl(): string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : asset('images/restaurant-hero.png');
    }

    private static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'producto';
        $slug = $base;
        $counter = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
