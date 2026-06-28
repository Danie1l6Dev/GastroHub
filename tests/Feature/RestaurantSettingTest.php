<?php

namespace Tests\Feature;

use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_displays_restaurant_identity(): void
    {
        RestaurantSetting::create([
            'name' => 'Brasa Norte',
            'slug' => 'brasa-norte',
            'description' => 'Cocina de brasa con ingredientes locales.',
            'primary_color' => '#dc2626',
            'secondary_color' => '#18181b',
            'address' => 'Carrera 7 # 20-15',
            'phone' => '+57 301 222 3344',
            'opening_hours' => 'Martes a domingo, 12:00 p. m. - 9:00 p. m.',
            'instagram_url' => 'https://instagram.com/brasanorte',
            'is_open' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Brasa Norte')
            ->assertSee('Cocina de brasa con ingredientes locales.')
            ->assertSee('Abierto ahora')
            ->assertSee('Carrera 7 # 20-15')
            ->assertSee('Ver menu');
    }

    public function test_guest_cannot_edit_restaurant_settings(): void
    {
        $this->get(route('admin.settings.edit'))
            ->assertRedirect(route('login'));

        $this->put(route('admin.settings.update'), [
            'name' => 'No permitido',
        ])->assertRedirect(route('login'));
    }

    public function test_admin_can_update_restaurant_settings_and_replace_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $setting = RestaurantSetting::create([
            'name' => 'Antiguo',
            'slug' => 'antiguo',
            'description' => 'Descripcion anterior',
            'logo_path' => 'restaurant/old-logo.png',
            'cover_image_path' => 'restaurant/old-cover.png',
            'primary_color' => '#059669',
            'secondary_color' => '#111827',
            'is_open' => true,
        ]);

        Storage::disk('public')->put($setting->logo_path, 'old logo');
        Storage::disk('public')->put($setting->cover_image_path, 'old cover');

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'name' => 'Brasa Norte',
            'description' => 'Cocina de brasa con ingredientes locales.',
            'address' => 'Carrera 7 # 20-15',
            'phone' => '+57 301 222 3344',
            'opening_hours' => 'Martes a domingo',
            'primary_color' => '#dc2626',
            'secondary_color' => '#18181b',
            'instagram_url' => 'https://instagram.com/brasanorte',
            'is_open' => '1',
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 700),
        ])->assertRedirect(route('admin.settings.edit'));

        $setting->refresh();

        $this->assertSame('Brasa Norte', $setting->name);
        $this->assertSame('brasa-norte', $setting->slug);
        $this->assertSame('#dc2626', $setting->primary_color);
        $this->assertTrue($setting->is_open);
        Storage::disk('public')->assertMissing('restaurant/old-logo.png');
        Storage::disk('public')->assertMissing('restaurant/old-cover.png');
        Storage::disk('public')->assertExists($setting->logo_path);
        Storage::disk('public')->assertExists($setting->cover_image_path);
    }

    public function test_admin_cannot_upload_invalid_files(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        RestaurantSetting::create([
            'name' => 'Brasa Norte',
            'slug' => 'brasa-norte',
            'primary_color' => '#059669',
            'secondary_color' => '#111827',
            'is_open' => true,
        ]);

        $this->actingAs($admin)->from(route('admin.settings.edit'))->put(route('admin.settings.update'), [
            'name' => 'Brasa Norte',
            'description' => 'Cocina local',
            'primary_color' => '#059669',
            'secondary_color' => '#111827',
            'logo' => UploadedFile::fake()->create('logo.pdf', 200, 'application/pdf'),
        ])->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors('logo');
    }
}
