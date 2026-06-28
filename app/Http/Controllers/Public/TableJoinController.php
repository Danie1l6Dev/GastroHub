<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\RestaurantSetting;
use Illuminate\View\View;

class TableJoinController extends Controller
{
    public function __invoke(DiningTable $table): View
    {
        abort_unless($table->is_active, 404);

        return view('public.table-placeholder', [
            'table' => $table,
            'restaurant' => RestaurantSetting::first(),
        ]);
    }
}
