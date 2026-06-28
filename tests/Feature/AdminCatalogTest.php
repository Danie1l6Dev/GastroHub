<?php

namespace Tests\Feature;

use App\Enums\TableStatus;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_admin_can_create_category(): void
    {
        $this->post(route('admin.categories.store'), [
            'name' => 'Postres',
            'description' => 'Dulces de la casa',
            'sort_order' => 4,
            'is_active' => '1',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Postres',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_product(): void
    {
        $category = Category::factory()->create();

        $this->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Cafe frio',
            'description' => 'Cafe con leche y hielo',
            'price' => 11000,
            'sort_order' => 1,
            'is_available' => '1',
            'is_featured' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Cafe frio',
            'price' => 11000,
            'is_available' => true,
            'is_featured' => true,
        ]);
    }

    public function test_admin_can_create_table_with_qr_token(): void
    {
        $this->post(route('admin.tables.store'), [
            'name' => 'Mesa Terraza',
            'code' => 'TERRAZA',
            'capacity' => 4,
            'is_active' => '1',
            'current_status' => TableStatus::Available->value,
        ])->assertRedirect(route('admin.tables.index'));

        $table = DiningTable::where('name', 'Mesa Terraza')->first();

        $this->assertNotNull($table);
        $this->assertNotEmpty($table->qr_token);
    }

    public function test_admin_dashboard_loads_counts(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();
        DiningTable::factory()->create();

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard administrativo')
            ->assertSee('Pedidos nuevos');
    }
}
