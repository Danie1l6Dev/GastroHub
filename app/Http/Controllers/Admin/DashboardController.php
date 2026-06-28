<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'cards' => [
                ['label' => 'Pedidos nuevos', 'value' => 0],
                ['label' => 'Pedidos preparando', 'value' => 0],
                ['label' => 'Mesas activas', 'value' => 0],
                ['label' => 'Ventas simuladas del dia', 'value' => '$0'],
            ],
        ]);
    }
}
