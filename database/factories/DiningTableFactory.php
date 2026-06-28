<?php

namespace Database\Factories;

use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<DiningTable> */
class DiningTableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Mesa '.fake()->unique()->numberBetween(1, 50),
            'qr_token' => (string) Str::uuid(),
            'capacity' => fake()->numberBetween(2, 8),
            'is_active' => true,
        ];
    }
}
