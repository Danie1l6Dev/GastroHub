<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'position',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            $source = $category->isDirty('name') && ! $category->isDirty('slug')
                ? $category->name
                : ($category->slug ?: $category->name);

            $category->slug = static::uniqueSlug($source, $category->id);
            $category->position = $category->sort_order ?? $category->position ?? 0;
            $category->sort_order = $category->sort_order ?? $category->position ?? 0;
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order')->orderBy('name');
    }

    public function visibleProducts(): HasMany
    {
        return $this->products();
    }

    public function availableProducts(): HasMany
    {
        return $this->products()->where('is_available', true);
    }

    private static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'categoria';
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
