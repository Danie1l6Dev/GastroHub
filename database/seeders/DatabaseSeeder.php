<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        RestaurantSetting::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'GastroHub Bistro',
                'tagline' => 'Sabores frescos, cuenta clara',
                'description' => 'Un prototipo de restaurante con menu digital, mesas por QR y pedidos por persona.',
                'address' => 'Calle 10 # 24-18',
                'phone' => '+57 300 555 0101',
                'opening_hours' => 'Lunes a sabado, 12:00 p. m. - 10:00 p. m.',
            ]
        );

        User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password'),
        ]);

        $starters = Category::firstOrCreate(
            ['name' => 'Entradas'],
            ['description' => 'Platos ligeros para compartir.', 'position' => 1, 'is_active' => true]
        );

        $mains = Category::firstOrCreate(
            ['name' => 'Fuertes'],
            ['description' => 'Opciones principales de la casa.', 'position' => 2, 'is_active' => true]
        );

        $drinks = Category::firstOrCreate(
            ['name' => 'Bebidas'],
            ['description' => 'Jugos, sodas y bebidas calientes.', 'position' => 3, 'is_active' => true]
        );

        $products = [
            [$starters->id, 'Croquetas de yuca', 'Yuca dorada con salsa cremosa de cilantro.', 18000, 1],
            [$starters->id, 'Tostadas de maiz', 'Maiz crocante con pico fresco y queso costeño.', 16000, 2],
            [$mains->id, 'Arroz meloso de mar', 'Arroz cremoso con pesca del dia y vegetales.', 42000, 1],
            [$mains->id, 'Pollo a la brasa', 'Pollo jugoso con papas rusticas y ensalada.', 36000, 2],
            [$mains->id, 'Pasta de la casa', 'Pasta corta con tomate asado, albahaca y queso.', 32000, 3],
            [$drinks->id, 'Limonada de hierbabuena', 'Limonada natural con hielo y hierbabuena.', 9000, 1],
            [$drinks->id, 'Cafe frio', 'Cafe suave con leche y hielo.', 11000, 2],
        ];

        foreach ($products as [$categoryId, $name, $description, $price, $position]) {
            Product::firstOrCreate(
                ['name' => $name],
                [
                    'category_id' => $categoryId,
                    'description' => $description,
                    'price' => $price,
                    'position' => $position,
                    'is_available' => true,
                ]
            );
        }

        foreach (range(1, 6) as $number) {
            DiningTable::firstOrCreate(
                ['name' => 'Mesa '.$number],
                ['capacity' => $number <= 2 ? 4 : 6, 'is_active' => true]
            );
        }
    }
}
