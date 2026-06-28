<?php

namespace Database\Seeders;

use App\Enums\TableStatus;
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
                'slug' => 'gastrohub-bistro',
                'tagline' => 'Sabores frescos, cuenta clara',
                'description' => 'Cocina casual con ingredientes frescos, platos para compartir y una experiencia digital sencilla desde la mesa.',
                'primary_color' => '#059669',
                'secondary_color' => '#111827',
                'address' => 'Calle 10 # 24-18, Centro',
                'phone' => '+57 300 555 0101',
                'opening_hours' => 'Lunes a sabado, 12:00 p. m. - 10:00 p. m.',
                'instagram_url' => 'https://instagram.com/gastrohub_bistro',
                'is_open' => true,
            ]
        );

        User::updateOrCreate([
            'email' => 'admin@restaurante.test',
        ], [
            'name' => 'Administrador',
            'password' => Hash::make('password'),
        ]);

        $starters = Category::updateOrCreate(
            ['name' => 'Entradas'],
            ['slug' => 'entradas', 'description' => 'Platos ligeros para compartir.', 'position' => 1, 'sort_order' => 1, 'is_active' => true]
        );

        $mains = Category::updateOrCreate(
            ['name' => 'Fuertes'],
            ['slug' => 'fuertes', 'description' => 'Opciones principales de la casa.', 'position' => 2, 'sort_order' => 2, 'is_active' => true]
        );

        $drinks = Category::updateOrCreate(
            ['name' => 'Bebidas'],
            ['slug' => 'bebidas', 'description' => 'Jugos, sodas y bebidas calientes.', 'position' => 3, 'sort_order' => 3, 'is_active' => true]
        );

        $desserts = Category::updateOrCreate(
            ['name' => 'Postres'],
            ['slug' => 'postres', 'description' => 'Dulces para cerrar la mesa.', 'position' => 4, 'sort_order' => 4, 'is_active' => true]
        );

        $products = [
            [$starters->id, 'Croquetas de yuca', 'Yuca dorada con salsa cremosa de cilantro.', 18000, 1, true, true],
            [$starters->id, 'Tostadas de maiz', 'Maiz crocante con pico fresco y queso costeno.', 16000, 2, true, false],
            [$starters->id, 'Ceviche de mango', 'Mango biche, cebolla morada y leche de tigre suave.', 22000, 3, false, false],
            [$mains->id, 'Arroz meloso de mar', 'Arroz cremoso con pesca del dia y vegetales.', 42000, 1, true, true],
            [$mains->id, 'Pollo a la brasa', 'Pollo jugoso con papas rusticas y ensalada.', 36000, 2, true, true],
            [$mains->id, 'Pasta de la casa', 'Pasta corta con tomate asado, albahaca y queso.', 32000, 3, true, false],
            [$mains->id, 'Hamburguesa artesanal', 'Carne de res, queso fundido y pan brioche.', 34000, 4, false, false],
            [$drinks->id, 'Limonada de hierbabuena', 'Limonada natural con hielo y hierbabuena.', 9000, 1, true, false],
            [$drinks->id, 'Cafe frio', 'Cafe suave con leche y hielo.', 11000, 2, true, false],
            [$drinks->id, 'Soda de frutos rojos', 'Soda artesanal con frutos rojos y limon.', 12000, 3, true, false],
            [$desserts->id, 'Flan de caramelo', 'Flan cremoso con caramelo oscuro.', 14000, 1, true, true],
            [$desserts->id, 'Brownie tibio', 'Brownie de cacao con helado de vainilla.', 17000, 2, true, false],
        ];

        foreach ($products as [$categoryId, $name, $description, $price, $position, $isAvailable, $isFeatured]) {
            Product::updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $categoryId,
                    'slug' => str($name)->slug()->toString(),
                    'description' => $description,
                    'price' => $price,
                    'position' => $position,
                    'sort_order' => $position,
                    'is_available' => $isAvailable,
                    'is_featured' => $isFeatured,
                ]
            );
        }

        foreach (range(1, 6) as $number) {
            DiningTable::updateOrCreate(
                ['name' => 'Mesa '.$number],
                [
                    'code' => 'T'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'capacity' => $number <= 2 ? 4 : 6,
                    'is_active' => true,
                    'current_status' => TableStatus::Available,
                ]
            );
        }
    }
}
