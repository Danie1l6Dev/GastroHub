<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\Order;
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

        return view('admin.dashboard', [
            'cards' => [
                ['label' => 'Pedidos nuevos', 'value' => $newOrders],
                ['label' => 'Pedidos preparando', 'value' => $preparingOrders],
                ['label' => 'Mesas activas', 'value' => $activeTables],
                ['label' => 'Ventas simuladas del dia', 'value' => '$'.number_format((int) $todaySales, 0, ',', '.')],
            ],
        ]);
    }
}
