<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiningTableController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Public\RestaurantController;
use App\Http\Controllers\Public\TableJoinController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RestaurantController::class, 'home'])->name('home');
Route::get('/menu', [RestaurantController::class, 'menu'])->name('menu');
Route::get('/mesa/{table:qr_token}', TableJoinController::class)->name('tables.join');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->middleware('auth')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('tables', DiningTableController::class)->except(['show'])->parameters([
        'tables' => 'table',
    ]);
});
