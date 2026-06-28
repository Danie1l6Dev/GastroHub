<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_edit_categories(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Especiales',
            'description' => 'Platos del dia',
            'sort_order' => 5,
            'is_active' => '1',
        ])->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', 'Especiales')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Especiales de la casa',
            'description' => 'Seleccion del chef',
            'sort_order' => 2,
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Especiales de la casa',
            'slug' => 'especiales-de-la-casa',
            'sort_order' => 2,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_create_product_with_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $category = Category::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Robalo al carbon',
            'description' => 'Robalo con vegetales asados.',
            'price' => 48000,
            'sort_order' => 1,
            'is_available' => '1',
            'is_featured' => '1',
            'image' => UploadedFile::fake()->image('robalo.jpg', 800, 500),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Robalo al carbon')->firstOrFail();

        $this->assertSame('robalo-al-carbon', $product->slug);
        $this->assertTrue($product->is_available);
        $this->assertTrue($product->is_featured);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_product_price_must_be_valid(): void
    {
        $admin = User::factory()->create();
        $category = Category::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->from(route('admin.products.create'))->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Precio invalido',
            'price' => 0,
            'sort_order' => 1,
        ])->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('price');
    }

    public function test_public_menu_shows_active_categories_and_sold_out_products(): void
    {
        RestaurantSetting::create([
            'name' => 'Brasa Norte',
            'slug' => 'brasa-norte',
            'primary_color' => '#059669',
            'secondary_color' => '#111827',
            'is_open' => true,
        ]);

        $active = Category::factory()->create(['name' => 'Parrilla', 'slug' => 'parrilla', 'is_active' => true, 'sort_order' => 1]);
        $inactive = Category::factory()->create(['name' => 'Oculta', 'slug' => 'oculta', 'is_active' => false, 'sort_order' => 2]);

        Product::factory()->for($active)->create(['name' => 'Costillas BBQ', 'price' => 39000, 'is_available' => true, 'sort_order' => 1]);
        Product::factory()->for($active)->create(['name' => 'Choripan', 'price' => 18000, 'is_available' => false, 'sort_order' => 2]);
        Product::factory()->for($inactive)->create(['name' => 'Producto oculto', 'price' => 10000, 'is_available' => true]);

        $this->get(route('menu'))
            ->assertOk()
            ->assertSee('Parrilla')
            ->assertSee('Costillas BBQ')
            ->assertSee('Choripan')
            ->assertSee('Agotado')
            ->assertDontSee('Oculta')
            ->assertDontSee('Producto oculto');
    }

    public function test_home_shows_only_featured_products(): void
    {
        RestaurantSetting::create([
            'name' => 'Brasa Norte',
            'slug' => 'brasa-norte',
            'primary_color' => '#059669',
            'secondary_color' => '#111827',
            'is_open' => true,
        ]);

        $category = Category::factory()->create(['is_active' => true]);

        Product::factory()->for($category)->create(['name' => 'Destacado real', 'is_featured' => true]);
        Product::factory()->for($category)->create(['name' => 'Producto comun', 'is_featured' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Destacado real')
            ->assertDontSee('Producto comun');
    }
}
