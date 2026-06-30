<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\TableSession;
use App\Services\OrderStatusService;
use App\Services\OrderTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderStatusService $orderStatuses,
        private readonly OrderTicketService $orderTickets,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in($this->orderStatuses->statuses())],
            'table_id' => ['nullable', 'integer', Rule::exists('dining_tables', 'id')],
            'date' => ['nullable', Rule::in(['today', 'all'])],
        ]);

        $filters['date'] ??= 'today';

        return view('admin.orders.index', [
            'tableGroups' => $this->orderTickets->tableGroups($filters),
            'tables' => DiningTable::query()->orderBy('name')->get(),
            'filters' => $filters,
            'orderStatuses' => $this->orderStatuses,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->orderStatuses->statuses())],
        ]);

        $this->orderStatuses->transition($order, $validated['status']);

        return back()->with('status', 'Estado del pedido actualizado.');
    }

    public function updateSessionMainStatus(Request $request, TableSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->orderStatuses->statuses())],
        ]);

        $mainOrders = $session->orders()
            ->where(function ($query) use ($session): void {
                $query->whereNull('placed_at')
                    ->orWhere('placed_at', '<=', $session->confirmed_at);
            })
            ->get();

        $this->orderStatuses->transitionMany($mainOrders, $validated['status']);

        return back()->with('status', 'Estado del pedido general actualizado.');
    }
}
