<?php

namespace Tests\Feature;

use App\Enums\TableStatus;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\TableGuest;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminOrderPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_order_panel(): void
    {
        $order = $this->createOrder();

        $this->get(route('admin.orders.index'))
            ->assertRedirect(route('login'));

        $this->patch(route('admin.orders.status', $order), [
            'status' => 'preparing',
        ])->assertRedirect(route('login'));
    }

    public function test_admin_can_list_orders(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createOrder([
            'status' => 'new',
            'subtotal' => 32000,
            'total' => 32000,
        ], tableName: 'Mesa Barra', guestAlias: 'Daniel', itemName: 'Ramen costeño', itemNotes: 'Sin picante');

        $this->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Mesa Barra')
            ->assertSee('Daniel')
            ->assertSee('Ramen costeño')
            ->assertSee('Sin picante')
            ->assertSee('$32.000')
            ->assertSee('Nuevo');
    }

    public function test_admin_can_filter_orders(): void
    {
        $this->actingAs(User::factory()->create());

        $table = DiningTable::factory()->create(['name' => 'Mesa Filtro']);
        $visibleOrder = $this->createOrder([
            'status' => 'preparing',
            'placed_at' => now(),
        ], table: $table, guestAlias: 'Visible', itemName: 'Hamburguesa');
        $this->createOrder([
            'status' => 'new',
            'placed_at' => now(),
        ], guestAlias: 'Oculto estado', itemName: 'Pizza');
        $this->createOrder([
            'status' => 'preparing',
            'placed_at' => now()->subDay(),
        ], table: $table, guestAlias: 'Oculto fecha', itemName: 'Pasta');

        $this->get(route('admin.orders.index', [
            'status' => 'preparing',
            'table_id' => $table->id,
            'date' => 'today',
        ]))
            ->assertOk()
            ->assertSee('session-'.$visibleOrder->table_session_id.'-main')
            ->assertSee('Visible')
            ->assertDontSee('Oculto estado')
            ->assertDontSee('Oculto fecha');

        $this->get(route('admin.orders.index', [
            'status' => 'preparing',
            'table_id' => $table->id,
            'date' => 'all',
        ]))
            ->assertOk()
            ->assertSee('Oculto fecha');
    }

    public function test_admin_can_apply_valid_order_status_transitions(): void
    {
        $this->actingAs(User::factory()->create());

        $newOrder = $this->createOrder(['status' => 'new']);
        $preparingOrder = $this->createOrder(['status' => 'preparing']);
        $cancelOrder = $this->createOrder(['status' => 'new']);

        $this->patch(route('admin.orders.status', $newOrder), [
            'status' => 'preparing',
        ])->assertSessionHasNoErrors();

        $this->patch(route('admin.orders.status', $preparingOrder), [
            'status' => 'delivered',
        ])->assertSessionHasNoErrors();

        $this->patch(route('admin.orders.status', $cancelOrder), [
            'status' => 'cancelled',
        ])->assertSessionHasNoErrors();

        $this->assertSame('preparing', $newOrder->fresh()->status);
        $this->assertSame('delivered', $preparingOrder->fresh()->status);
        $this->assertSame('cancelled', $cancelOrder->fresh()->status);
    }

    public function test_admin_panel_groups_confirmed_table_orders_and_marks_extras(): void
    {
        $this->actingAs(User::factory()->create());

        $table = DiningTable::factory()->create(['name' => 'Mesa Compartida']);
        $session = TableSession::create([
            'dining_table_id' => $table->id,
            'status' => 'open',
            'opened_at' => now()->subMinutes(20),
            'confirmed_at' => now()->subMinutes(10),
        ]);

        $this->createOrderInSession($session, 'Daniel', 'Brownie', 9000, 1, null, now()->subMinutes(15));
        $this->createOrderInSession($session, 'Laura', 'Brownie', 9000, 1, null, now()->subMinutes(14));
        $this->createOrderInSession($session, 'Sofia', 'Brownie', 9000, 1, 'Calentar', now()->subMinutes(13));
        $this->createOrderInSession($session, 'Daniel', 'Limonada', 7000, 1, null, now()->subMinutes(2));

        $this->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Mesa Compartida')
            ->assertSee('mesas encontradas')
            ->assertSee('Pedido general')
            ->assertSee('Adicional')
            ->assertSee('1 x Limonada')
            ->assertSeeInOrder([
                '2 x Brownie',
                '1x Daniel, 1x Laura',
                '1 x Brownie',
                '1x Sofia',
                'Nota: Calentar',
            ]);
    }

    public function test_admin_can_update_the_general_table_order_status(): void
    {
        $this->actingAs(User::factory()->create());

        $session = TableSession::create([
            'dining_table_id' => DiningTable::factory()->create()->id,
            'status' => 'open',
            'opened_at' => now()->subMinutes(20),
            'confirmed_at' => now()->subMinutes(10),
        ]);
        $firstOrder = $this->createOrderInSession($session, 'Daniel', 'Brownie', 9000, 1, null, now()->subMinutes(15));
        $secondOrder = $this->createOrderInSession($session, 'Laura', 'Cafe', 5000, 1, null, now()->subMinutes(14));

        $this->patch(route('admin.orders.sessions.main.status', $session), [
            'status' => 'preparing',
        ])->assertSessionHasNoErrors();

        $this->assertSame('preparing', $firstOrder->fresh()->status);
        $this->assertSame('preparing', $secondOrder->fresh()->status);
    }

    public function test_admin_cannot_apply_invalid_order_status_transition(): void
    {
        $this->actingAs(User::factory()->create());

        $order = $this->createOrder(['status' => 'delivered']);

        $this->from(route('admin.orders.index'))
            ->patch(route('admin.orders.status', $order), [
                'status' => 'new',
            ])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHasErrors('status');

        $this->assertSame('delivered', $order->fresh()->status);
    }

    public function test_dashboard_uses_real_order_metrics(): void
    {
        $this->actingAs(User::factory()->create());

        DiningTable::factory()->create(['current_status' => TableStatus::Occupied]);
        DiningTable::factory()->create(['current_status' => TableStatus::PaymentPending]);
        DiningTable::factory()->create(['current_status' => TableStatus::Available]);

        $this->createOrder(['status' => 'new', 'subtotal' => 12000, 'total' => 12000]);
        $this->createOrder(['status' => 'preparing', 'subtotal' => 18000, 'total' => 18000]);
        $this->createOrder(['status' => 'cancelled', 'subtotal' => 50000, 'total' => 50000]);
        $closedOrder = $this->createOrder(['status' => 'delivered', 'subtotal' => 30000, 'total' => 30000]);
        $closedOrder->tableSession->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
        ])->save();
        $this->createOrder([
            'status' => 'delivered',
            'subtotal' => 9000,
            'total' => 9000,
            'placed_at' => now()->subDay(),
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pedidos nuevos')
            ->assertSee('Pedidos preparando')
            ->assertSee('Mesas activas')
            ->assertSee('Ventas del dia')
            ->assertSee('1')
            ->assertSee('2')
            ->assertSee('$30.000');
    }

    private function createOrder(
        array $attributes = [],
        ?DiningTable $table = null,
        string $tableName = 'Mesa 1',
        string $guestAlias = 'Laura',
        string $itemName = 'Arepa rellena',
        ?string $itemNotes = null
    ): Order {
        $table ??= DiningTable::factory()->create([
            'name' => $tableName,
            'current_status' => TableStatus::Occupied,
        ]);

        $placedAt = $attributes['placed_at'] ?? now()->subMinute();
        $confirmedAt = $attributes['confirmed_at'] ?? $placedAt->copy()->addMinute();
        unset($attributes['confirmed_at']);

        $session = TableSession::create([
            'dining_table_id' => $table->id,
            'status' => 'open',
            'opened_at' => now()->subMinutes(5),
            'confirmed_at' => $confirmedAt,
        ]);

        $guest = TableGuest::create([
            'table_session_id' => $session->id,
            'alias' => $guestAlias,
            'status' => 'active',
            'is_ready' => true,
            'ready_at' => now(),
        ]);

        $order = Order::create(array_merge([
            'table_session_id' => $session->id,
            'table_guest_id' => $guest->id,
            'status' => 'new',
            'subtotal' => 24000,
            'total' => 24000,
            'notes' => null,
            'placed_at' => $placedAt,
        ], array_merge($attributes, ['placed_at' => $placedAt])));

        $product = Product::factory()
            ->for(Category::factory())
            ->create(['name' => $itemName, 'price' => (int) ($order->subtotal ?: $order->total)]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $itemName,
            'unit_price' => (int) ($order->subtotal ?: $order->total),
            'quantity' => 1,
            'line_total' => (int) ($order->subtotal ?: $order->total),
            'subtotal' => (int) ($order->subtotal ?: $order->total),
            'notes' => $itemNotes,
        ]);

        return $order;
    }

    private function createOrderInSession(
        TableSession $session,
        string $guestAlias,
        string $itemName,
        int $unitPrice,
        int $quantity,
        ?string $itemNotes,
        mixed $placedAt,
        string $status = 'new'
    ): Order {
        $guest = TableGuest::create([
            'table_session_id' => $session->id,
            'alias' => $guestAlias,
            'status' => 'active',
            'is_ready' => true,
            'ready_at' => now(),
        ]);

        $order = Order::create([
            'table_session_id' => $session->id,
            'table_guest_id' => $guest->id,
            'status' => $status,
            'subtotal' => $unitPrice * $quantity,
            'total' => $unitPrice * $quantity,
            'placed_at' => $placedAt,
            'is_additional' => $session->confirmed_at
                && Carbon::parse($placedAt)->greaterThan($session->confirmed_at),
        ]);

        $product = Product::factory()
            ->for(Category::factory())
            ->create(['name' => $itemName, 'price' => $unitPrice]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $itemName,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $unitPrice * $quantity,
            'subtotal' => $unitPrice * $quantity,
            'notes' => $itemNotes,
        ]);

        return $order;
    }
}
