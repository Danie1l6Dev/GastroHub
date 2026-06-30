<?php

namespace Tests\Feature;

use App\Enums\TableStatus;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_table(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.tables.store'), [
            'name' => 'Mesa Terraza',
            'code' => 'T-10',
            'capacity' => 4,
            'is_active' => '1',
            'current_status' => TableStatus::Available->value,
        ])->assertRedirect(route('admin.tables.index'));

        $table = DiningTable::where('code', 'T-10')->firstOrFail();

        $this->assertNotEmpty($table->qr_token);
        $this->assertSame(TableStatus::Available, $table->current_status);
    }

    public function test_duplicate_qr_tokens_are_rejected(): void
    {
        DiningTable::factory()->create(['qr_token' => 'duplicated-token']);

        $this->expectException(QueryException::class);

        DiningTable::factory()->create(['qr_token' => 'duplicated-token']);
    }

    public function test_customer_can_access_with_valid_token(): void
    {
        RestaurantSetting::create([
            'name' => 'Brasa Norte',
            'slug' => 'brasa-norte',
            'primary_color' => '#059669',
            'secondary_color' => '#111827',
            'is_open' => true,
        ]);
        $table = DiningTable::factory()->create(['name' => 'Mesa 3', 'is_active' => true]);

        $this->get(route('tables.join', $table->qr_token))
            ->assertOk()
            ->assertSee('Mesa 3')
            ->assertSee('Pago en conjunto')
            ->assertSee('Cuentas separadas')
            ->assertSee('Nombre o alias')
            ->assertDontSee('email');
    }

    public function test_customer_can_continue_with_alias(): void
    {
        $table = DiningTable::factory()->create(['name' => 'Mesa 4', 'is_active' => true]);
        $this->chooseSeparateAccountMode($table);

        $this->post(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertRedirect(route('tables.join', $table->qr_token));

        $this->get(route('tables.join', $table->qr_token))
            ->assertOk()
            ->assertSee('Ingresaste como')
            ->assertSee('Laura')
            ->assertSee('Ver menu');
    }

    public function test_alias_is_required_to_continue(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);

        $this->from(route('tables.join', $table->qr_token))
            ->post(route('tables.join.store', $table->qr_token), [
                'alias' => '',
            ])->assertRedirect(route('tables.join', $table->qr_token))
            ->assertSessionHasErrors('alias');
    }

    public function test_table_state_shows_guests_items_subtotals_and_total(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'name' => 'Croquetas',
            'price' => 18000,
            'is_available' => true,
        ]);
        $this->chooseSeparateAccountMode($table);

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk();

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk()
            ->assertJsonPath('guests.0.alias', 'Laura')
            ->assertJsonPath('guests.0.items.0.name', 'Croquetas')
            ->assertJsonPath('guests.0.items.0.quantity', 1)
            ->assertJsonPath('categories.0.products.0.image_url', $product->imageUrl())
            ->assertJsonPath('guests.0.subtotal', 18000)
            ->assertJsonPath('total', 18000);
    }

    public function test_sold_out_product_cannot_be_added_to_table(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'price' => 18000,
            'is_available' => false,
        ]);
        $this->chooseSeparateAccountMode($table);

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk();

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertUnprocessable();
    }

    public function test_device_can_release_current_guest_and_join_another_person(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $firstProduct = Product::factory()->create([
            'name' => 'Croquetas',
            'price' => 18000,
            'is_available' => true,
        ]);
        $secondProduct = Product::factory()->create([
            'name' => 'Limonada',
            'price' => 9000,
            'is_available' => true,
        ]);
        $this->chooseSeparateAccountMode($table);

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk()
            ->assertJsonPath('current_guest_id', 1);

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $firstProduct->id,
            'delta' => 1,
        ])->assertOk()
            ->assertJsonPath('guests.0.alias', 'Laura')
            ->assertJsonPath('guests.0.subtotal', 18000);

        $this->postJson(route('tables.guest.release', $table->qr_token))
            ->assertOk()
            ->assertJsonPath('current_guest_id', null)
            ->assertJsonCount(1, 'guests')
            ->assertJsonPath('guests.0.alias', 'Laura')
            ->assertJsonPath('total', 18000);

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Ana',
        ])->assertOk()
            ->assertJsonPath('guests.1.alias', 'Ana');

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $secondProduct->id,
            'delta' => 1,
        ])->assertOk()
            ->assertJsonPath('guests.0.alias', 'Laura')
            ->assertJsonPath('guests.0.subtotal', 18000)
            ->assertJsonPath('guests.1.alias', 'Ana')
            ->assertJsonPath('guests.1.subtotal', 9000)
            ->assertJsonPath('total', 27000);
    }

    public function test_device_can_select_an_existing_guest_and_edit_their_order(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $croquetas = Product::factory()->create([
            'name' => 'Croquetas',
            'price' => 18000,
            'is_available' => true,
        ]);
        $limonada = Product::factory()->create([
            'name' => 'Limonada',
            'price' => 9000,
            'is_available' => true,
        ]);
        $this->chooseSeparateAccountMode($table);

        $joinLaura = $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk();
        $lauraId = $joinLaura->json('current_guest_id');

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $croquetas->id,
            'delta' => 1,
        ])->assertOk()
            ->assertJsonPath('guests.0.alias', 'Laura')
            ->assertJsonPath('guests.0.subtotal', 18000);

        $this->postJson(route('tables.guest.release', $table->qr_token))->assertOk();

        $joinAna = $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Ana',
        ])->assertOk();
        $anaId = $joinAna->json('current_guest_id');

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $limonada->id,
            'delta' => 1,
        ])->assertOk()
            ->assertJsonPath('guests.1.alias', 'Ana')
            ->assertJsonPath('guests.1.subtotal', 9000);

        $this->postJson(route('tables.guests.select', [$table->qr_token, $lauraId]))
            ->assertOk()
            ->assertJsonPath('current_guest_id', $lauraId)
            ->assertJsonPath('guests.0.alias', 'Laura');

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $limonada->id,
            'delta' => 1,
        ])->assertOk()
            ->assertJsonPath('current_guest_id', $lauraId)
            ->assertJsonPath('guests.0.alias', 'Laura')
            ->assertJsonPath('guests.0.subtotal', 27000)
            ->assertJsonPath('guests.1.alias', 'Ana')
            ->assertJsonPath('guests.1.subtotal', 9000)
            ->assertJsonPath('total', 36000);
    }

    public function test_joint_payment_mode_allows_only_one_person_to_order(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);

        $this->postJson(route('tables.account-mode', $table->qr_token), [
            'account_mode' => 'joint',
        ])->assertOk()
            ->assertJsonPath('account_mode', 'joint')
            ->assertJsonPath('requires_account_mode', false);

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk()
            ->assertJsonPath('guests.0.alias', 'Laura')
            ->assertJsonCount(1, 'guests');

        $this->postJson(route('tables.guest.release', $table->qr_token))->assertOk();

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Ana',
        ])->assertUnprocessable();

        $this->getJson(route('tables.state', $table->qr_token))
            ->assertOk()
            ->assertJsonPath('account_mode', 'joint')
            ->assertJsonCount(1, 'guests')
            ->assertJsonPath('guests.0.alias', 'Laura');
    }

    public function test_joint_payment_mode_warns_other_devices_when_order_has_items(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'name' => 'Ceviche',
            'price' => 24000,
            'is_available' => true,
        ]);

        $this->postJson(route('tables.account-mode', $table->qr_token), [
            'account_mode' => 'joint',
        ])->assertOk();

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk();

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk()
            ->assertJsonPath('joint_order_locked', false)
            ->assertJsonPath('joint_order_owner_alias', 'Laura');

        $this->flushSession();

        $this->getJson(route('tables.state', $table->qr_token))
            ->assertOk()
            ->assertJsonPath('account_mode', 'joint')
            ->assertJsonPath('current_guest_id', null)
            ->assertJsonPath('joint_order_locked', true)
            ->assertJsonPath('joint_order_owner_alias', 'Laura')
            ->assertJsonPath('total', 24000);
    }

    public function test_joint_payment_first_guest_can_confirm_final_order(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'name' => 'Cazuela',
            'price' => 32000,
            'is_available' => true,
        ]);

        $this->postJson(route('tables.account-mode', $table->qr_token), [
            'account_mode' => 'joint',
        ])->assertOk();

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Daniel',
        ])->assertOk()
            ->assertJsonPath('device_can_confirm_order', true)
            ->assertJsonPath('can_confirm_order', false);

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk()
            ->assertJsonPath('device_can_confirm_order', true)
            ->assertJsonPath('has_items', true)
            ->assertJsonPath('can_confirm_order', false);

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => true,
        ])->assertOk()
            ->assertJsonPath('guests.0.alias', 'Daniel')
            ->assertJsonPath('guests.0.is_ready', true)
            ->assertJsonPath('all_guests_ready', true)
            ->assertJsonPath('device_can_confirm_order', true)
            ->assertJsonPath('can_confirm_order', true);

        $this->postJson(route('tables.confirm', $table->qr_token))
            ->assertOk()
            ->assertJsonPath('order_confirmed', true)
            ->assertJsonPath('confirmed_by_alias', 'Daniel')
            ->assertJsonPath('can_confirm_order', false);
    }

    public function test_stale_coordinator_marker_does_not_block_joint_payment_first_guest(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'price' => 32000,
            'is_available' => true,
        ]);

        $this->postJson(route('tables.account-mode', $table->qr_token), [
            'account_mode' => 'joint',
        ])->assertOk();

        $joinDaniel = $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Daniel',
        ])->assertOk();
        $danielId = $joinDaniel->json('current_guest_id');

        $this->app['session']->put('tables.'.$table->id.'.coordinator_guest_id', $danielId + 999);

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk();

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => true,
        ])->assertOk()
            ->assertJsonPath('current_guest_id', $danielId)
            ->assertJsonPath('all_guests_ready', true)
            ->assertJsonPath('device_can_confirm_order', true)
            ->assertJsonPath('can_confirm_order', true);

        $this->postJson(route('tables.confirm', $table->qr_token))
            ->assertOk()
            ->assertJsonPath('order_confirmed', true)
            ->assertJsonPath('confirmed_by_alias', 'Daniel');
    }

    public function test_people_mark_ready_and_first_guest_confirms_final_order(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'name' => 'Arroz cremoso',
            'price' => 28000,
            'is_available' => true,
        ]);
        $this->chooseSeparateAccountMode($table);

        $joinLaura = $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk();
        $lauraId = $joinLaura->json('current_guest_id');

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk();

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => true,
        ])->assertOk()
            ->assertJsonPath('guests.0.is_ready', true)
            ->assertJsonPath('all_guests_ready', true)
            ->assertJsonPath('can_confirm_order', true);

        $this->app['session']->forget('tables.'.$table->id.'.coordinator_guest_id');

        $this->getJson(route('tables.state', $table->qr_token))
            ->assertOk()
            ->assertJsonPath('device_can_confirm_order', true)
            ->assertJsonPath('can_confirm_order', true);

        $this->postJson(route('tables.guest.release', $table->qr_token))->assertOk();
        $this->app['session']->forget('tables.'.$table->id.'.coordinator_guest_id');

        $this->app['session']->forget('tables.'.$table->id.'.has_participated');

        $this->getJson(route('tables.state', $table->qr_token))
            ->assertOk()
            ->assertJsonPath('current_guest_id', null)
            ->assertJsonPath('all_guests_ready', true)
            ->assertJsonPath('device_can_confirm_order', true)
            ->assertJsonPath('can_confirm_order', true);

        $this->postJson(route('tables.guests.select', [$table->qr_token, $lauraId]))
            ->assertOk();

        $this->postJson(route('tables.guest.release', $table->qr_token))->assertOk();

        $joinAna = $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Ana',
        ])->assertOk();
        $anaId = $joinAna->json('current_guest_id');

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => true,
        ])->assertOk()
            ->assertJsonPath('all_guests_ready', true)
            ->assertJsonPath('current_guest_id', $anaId)
            ->assertJsonPath('device_can_confirm_order', true)
            ->assertJsonPath('can_confirm_order', true);

        $this->postJson(route('tables.confirm', $table->qr_token))
            ->assertOk()
            ->assertJsonPath('order_confirmed', true)
            ->assertJsonPath('confirmed_by_alias', 'Laura')
            ->assertJsonPath('can_confirm_order', false);

        $this->postJson(route('tables.guest.release', $table->qr_token))->assertOk();

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Carlos',
        ])->assertUnprocessable();

        $this->flushSession();

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Maria',
        ])->assertUnprocessable();

        $this->assertDatabaseHas('orders', [
            'status' => 'confirmed',
            'total' => 28000,
        ]);
    }

    public function test_other_device_cannot_confirm_even_when_it_edits_a_ready_guest(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'price' => 28000,
            'is_available' => true,
        ]);
        $this->chooseSeparateAccountMode($table);

        $joinLaura = $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk();
        $lauraId = $joinLaura->json('current_guest_id');

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk();

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => true,
        ])->assertOk();

        $this->flushSession();

        $joinAna = $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Ana',
        ])->assertOk();
        $anaId = $joinAna->json('current_guest_id');

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => true,
        ])->assertOk()
            ->assertJsonPath('all_guests_ready', true)
            ->assertJsonPath('device_can_confirm_order', false)
            ->assertJsonPath('can_confirm_order', false);

        $this->postJson(route('tables.guests.select', [$table->qr_token, $lauraId]))
            ->assertOk()
            ->assertJsonPath('current_guest_id', $lauraId)
            ->assertJsonPath('device_can_confirm_order', false)
            ->assertJsonPath('can_confirm_order', false);

        $this->postJson(route('tables.confirm', $table->qr_token))->assertForbidden();
    }

    public function test_device_without_first_guest_ownership_cannot_confirm_after_everyone_is_ready(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'price' => 28000,
            'is_available' => true,
        ]);
        $this->chooseSeparateAccountMode($table);

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk();

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk();

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => true,
        ])->assertOk();

        $this->postJson(route('tables.guest.release', $table->qr_token))->assertOk();

        $joinAna = $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Ana',
        ])->assertOk();
        $anaId = $joinAna->json('current_guest_id');

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => true,
        ])->assertOk();

        $this->app['session']->forget([
            'tables.'.$table->id.'.coordinator_guest_id',
            'tables.'.$table->id.'.owned_guest_ids',
        ]);

        $this->getJson(route('tables.state', $table->qr_token))
            ->assertOk()
            ->assertJsonPath('current_guest_id', $anaId)
            ->assertJsonPath('device_can_confirm_order', false)
            ->assertJsonPath('can_confirm_order', false);

        $this->postJson(route('tables.confirm', $table->qr_token))->assertForbidden();
    }

    public function test_new_alias_blocks_final_confirmation_until_that_person_is_ready(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'price' => 18000,
            'is_available' => true,
        ]);
        $this->chooseSeparateAccountMode($table);

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk();

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk();

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => true,
        ])->assertOk()
            ->assertJsonPath('all_guests_ready', true)
            ->assertJsonPath('can_confirm_order', true);

        $this->postJson(route('tables.guest.release', $table->qr_token))->assertOk()
            ->assertJsonPath('all_guests_ready', true);

        $this->getJson(route('tables.state', $table->qr_token))
            ->assertOk()
            ->assertJsonPath('all_guests_ready', true)
            ->assertJsonCount(1, 'guests');

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Ana',
        ])->assertOk()
            ->assertJsonCount(2, 'guests')
            ->assertJsonPath('guests.1.is_ready', false)
            ->assertJsonPath('all_guests_ready', false)
            ->assertJsonPath('can_confirm_order', false);
    }

    public function test_ready_guest_must_edit_again_before_changing_items(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'price' => 18000,
            'is_available' => true,
        ]);
        $this->chooseSeparateAccountMode($table);

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk();

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk();

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => true,
        ])->assertOk();

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertUnprocessable();

        $this->postJson(route('tables.ready', $table->qr_token), [
            'is_ready' => false,
        ])->assertOk()
            ->assertJsonPath('guests.0.is_ready', false);

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk()
            ->assertJsonPath('guests.0.items.0.quantity', 2);
    }

    public function test_invalid_token_is_rejected_with_clear_error(): void
    {
        $this->get(route('tables.join', 'invalid-token'))
            ->assertNotFound()
            ->assertSee('No pudimos abrir este QR')
            ->assertSee('no es valido');
    }

    public function test_inactive_table_is_rejected(): void
    {
        $table = DiningTable::factory()->create(['is_active' => false]);

        $this->get(route('tables.join', $table->qr_token))
            ->assertForbidden()
            ->assertSee('Esta mesa no esta disponible');
    }

    public function test_admin_can_regenerate_qr_token(): void
    {
        $admin = User::factory()->create();
        $table = DiningTable::factory()->create();
        $oldToken = $table->qr_token;

        $this->actingAs($admin)
            ->post(route('admin.tables.regenerate-token', $table))
            ->assertRedirect(route('admin.tables.index'));

        $table->refresh();

        $this->assertNotSame($oldToken, $table->qr_token);
        $this->assertSame(48, strlen($table->qr_token));
    }

    public function test_regenerating_qr_token_closes_current_table_session(): void
    {
        $admin = User::factory()->create();
        $table = DiningTable::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'name' => 'Arepa de choclo',
            'price' => 16000,
            'is_available' => true,
        ]);
        $this->chooseSeparateAccountMode($table);

        $this->postJson(route('tables.join.store', $table->qr_token), [
            'alias' => 'Laura',
        ])->assertOk();

        $this->postJson(route('tables.items', $table->qr_token), [
            'product_id' => $product->id,
            'delta' => 1,
        ])->assertOk()
            ->assertJsonPath('total', 16000);

        $oldToken = $table->qr_token;

        $this->actingAs($admin)
            ->post(route('admin.tables.regenerate-token', $table))
            ->assertRedirect(route('admin.tables.index'));

        $table->refresh();

        $this->assertNotSame($oldToken, $table->qr_token);
        $this->assertSame(TableStatus::Available, $table->current_status);
        $this->assertDatabaseHas('table_sessions', [
            'dining_table_id' => $table->id,
            'status' => 'closed',
        ]);
        $this->assertNotNull(TableSession::where('dining_table_id', $table->id)->where('status', 'closed')->first()?->closed_at);

        $this->get(route('tables.join', $oldToken))->assertNotFound();

        $this->getJson(route('tables.state', $table->qr_token))
            ->assertOk()
            ->assertJsonCount(0, 'guests')
            ->assertJsonPath('total', 0);
    }

    public function test_admin_can_download_qr_svg(): void
    {
        $admin = User::factory()->create();
        $table = DiningTable::factory()->create(['code' => 'T-QR']);

        $this->actingAs($admin)
            ->get(route('admin.tables.qr.download', $table))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml')
            ->assertSee('<svg', false);
    }

    private function chooseSeparateAccountMode(DiningTable $table): void
    {
        $this->postJson(route('tables.account-mode', $table->qr_token), [
            'account_mode' => 'separate',
        ])->assertOk();
    }
}
