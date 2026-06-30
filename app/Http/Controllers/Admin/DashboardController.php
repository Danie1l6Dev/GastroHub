<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderTicketService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly OrderTicketService $orderTickets) {}

    public function __invoke(): View
    {
        $newOrders = $this->orderTickets->tickets(['date' => 'all', 'status' => 'new'])->count();
        $preparingOrders = $this->orderTickets->tickets(['date' => 'all', 'status' => 'preparing'])->count();
        $activeTables = DiningTable::query()
            ->whereIn('current_status', [TableStatus::Occupied->value, TableStatus::PaymentPending->value])
            ->count();
        $todaySales = Order::query()
            ->whereDate('placed_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('subtotal');
        $deliveredOrders = Order::query()->where('status', 'delivered')->count();
        $availableProducts = Product::query()->where('is_available', true)->count();
        $recentOrders = Order::query()
            ->with(['tableSession.diningTable', 'tableGuest', 'items'])
            ->latest('placed_at')
            ->take(5)
            ->get();
        $tableStatusCounts = DiningTable::query()
            ->selectRaw('current_status, count(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

        return view('admin.dashboard', [
            'cards' => [
                ['label' => 'Pedidos nuevos', 'value' => $newOrders, 'hint' => 'Esperando cocina', 'tone' => 'warning'],
                ['label' => 'Pedidos preparando', 'value' => $preparingOrders, 'hint' => 'En cocina ahora', 'tone' => 'info'],
                ['label' => 'Mesas activas', 'value' => $activeTables, 'hint' => 'Ocupadas o por cerrar', 'tone' => 'dark'],
                ['label' => 'Ventas simuladas del dia', 'value' => '$'.number_format((int) $todaySales, 0, ',', '.'), 'hint' => 'Pedidos no cancelados', 'tone' => 'success'],
                ['label' => 'Entregados', 'value' => $deliveredOrders, 'hint' => 'Historico entregado', 'tone' => 'success'],
                ['label' => 'Productos disponibles', 'value' => $availableProducts, 'hint' => 'Listos para vender', 'tone' => 'neutral'],
            ],
            'recentOrders' => $recentOrders,
            'tableStatusCounts' => $tableStatusCounts,
        ]);
    }
}
