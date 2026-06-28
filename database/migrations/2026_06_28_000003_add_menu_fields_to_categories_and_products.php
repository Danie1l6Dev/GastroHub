<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('name');
            $table->unsignedInteger('sort_order')->default(0)->after('description');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('name');
            $table->string('image_path')->nullable()->after('price');
            $table->boolean('is_featured')->default(false)->after('is_available');
            $table->unsignedInteger('sort_order')->default(0)->after('is_featured');
        });

        $this->backfillCategoryFields();
        $this->backfillProductFields();

        Schema::table('categories', function (Blueprint $table): void {
            $table->unique('slug');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'image_path', 'is_featured', 'sort_order']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'sort_order']);
        });
    }

    private function backfillCategoryFields(): void
    {
        $used = [];

        foreach (DB::table('categories')->orderBy('id')->get() as $category) {
            $slug = $this->uniqueSlug($category->name, $used);
            $used[] = $slug;

            DB::table('categories')
                ->where('id', $category->id)
                ->update([
                    'slug' => $slug,
                    'sort_order' => $category->position ?? 0,
                ]);
        }
    }

    private function backfillProductFields(): void
    {
        $used = [];

        foreach (DB::table('products')->orderBy('id')->get() as $product) {
            $slug = $this->uniqueSlug($product->name, $used);
            $used[] = $slug;

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'slug' => $slug,
                    'sort_order' => $product->position ?? 0,
                ]);
        }
    }

    private function uniqueSlug(string $value, array $used): string
    {
        $base = Str::slug($value) ?: 'item';
        $slug = $base;
        $counter = 2;

        while (in_array($slug, $used, true)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
};
