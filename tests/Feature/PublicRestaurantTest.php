<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRestaurantTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_restaurant_and_featured_products(): void
    {
        RestaurantSetting::create(['name' => 'Mesa Clara']);
        $category = Category::factory()->create(['name' => 'Fuertes']);
        Product::factory()->for($category)->create(['name' => 'Arroz meloso', 'price' => 42000]);
        DiningTable::factory()->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('Mesa Clara')
            ->assertSee('Arroz meloso')
            ->assertSee('Ver menu');
    }

    public function test_menu_shows_only_active_categories_and_available_products(): void
    {
        RestaurantSetting::create(['name' => 'Mesa Clara']);
        $active = Category::factory()->create(['name' => 'Entradas', 'is_active' => true]);
        $inactive = Category::factory()->create(['name' => 'Oculta', 'is_active' => false]);
        Product::factory()->for($active)->create(['name' => 'Croquetas', 'is_available' => true]);
        Product::factory()->for($active)->create(['name' => 'Agotado', 'is_available' => false]);
        Product::factory()->for($inactive)->create(['name' => 'Invisible', 'is_available' => true]);

        $this->get('/menu')
            ->assertOk()
            ->assertSee('Entradas')
            ->assertSee('Croquetas')
            ->assertDontSee('Agotado')
            ->assertDontSee('Invisible');
    }

    public function test_table_qr_route_resolves_active_table(): void
    {
        RestaurantSetting::create(['name' => 'Mesa Clara']);
        $table = DiningTable::factory()->create(['name' => 'Mesa 9']);

        $this->get(route('tables.join', $table))
            ->assertOk()
            ->assertSee('Mesa 9')
            ->assertSee('QR de mesa');
    }
}
