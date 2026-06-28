<?php

namespace Database\Factories;

use App\Enums\TableStatus;
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
            'code' => 'T'.fake()->unique()->numerify('###'),
            'qr_token' => Str::random(48),
            'capacity' => fake()->numberBetween(2, 8),
            'is_active' => true,
            'current_status' => TableStatus::Available,
        ];
    }
}
