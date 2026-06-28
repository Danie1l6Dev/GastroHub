<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\TableSession;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'categoryCount' => Category::count(),
            'productCount' => Product::count(),
            'tableCount' => DiningTable::count(),
            'openSessionCount' => TableSession::where('status', 'open')->count(),
            'latestProducts' => Product::with('category')->latest()->take(5)->get(),
        ]);
    }
}
