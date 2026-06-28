<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(12000, 65000),
            'is_available' => true,
            'is_featured' => false,
            'position' => fake()->numberBetween(0, 20),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
