<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'restaurant' => RestaurantSetting::first(),
            'featuredProducts' => Product::query()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('position')
                ->take(6)
                ->get(),
            'activeTables' => DiningTable::query()->where('is_active', true)->count(),
        ]);
    }

    public function menu(): View
    {
        return view('public.menu', [
            'restaurant' => RestaurantSetting::first(),
            'categories' => Category::query()
                ->where('is_active', true)
                ->with(['availableProducts'])
                ->orderBy('position')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
