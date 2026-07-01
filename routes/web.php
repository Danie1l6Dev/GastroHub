<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiningTableController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RestaurantSettingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Public\RestaurantController;
use App\Http\Controllers\Public\TableJoinController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RestaurantController::class, 'home'])->name('home');
Route::get('/menu', [RestaurantController::class, 'menu'])->name('menu');
Route::get('/table/{qrToken}', TableJoinController::class)->name('tables.join');
Route::post('/table/{qrToken}/account-mode', [TableJoinController::class, 'accountMode'])->name('tables.account-mode');
Route::post('/table/{qrToken}', [TableJoinController::class, 'join'])->name('tables.join.store');
Route::post('/table/{qrToken}/release', [TableJoinController::class, 'release'])->name('tables.guest.release');
Route::post('/table/{qrToken}/guests/{guest:guest_token}/select', [TableJoinController::class, 'selectGuest'])->name('tables.guests.select');
Route::get('/table/{qrToken}/state', [TableJoinController::class, 'state'])->name('tables.state');
Route::post('/table/{qrToken}/items', [TableJoinController::class, 'item'])->name('tables.items');
Route::post('/table/{qrToken}/cart/clear', [TableJoinController::class, 'clearCart'])->name('tables.cart.clear');
Route::post('/table/{qrToken}/ready', [TableJoinController::class, 'ready'])->name('tables.ready');
Route::post('/table/{qrToken}/confirm', [TableJoinController::class, 'confirm'])->name('tables.confirm');
Route::get('/mesa/{qrToken}', TableJoinController::class)->name('tables.legacy-join');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->middleware('auth')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/settings', [RestaurantSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [RestaurantSettingController::class, 'update'])->name('settings.update');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::patch('orders/sessions/{session}/main/status', [OrderController::class, 'updateSessionMainStatus'])->name('orders.sessions.main.status');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('tables/{table}/regenerate-token', [DiningTableController::class, 'regenerateToken'])->name('tables.regenerate-token');
    Route::post('tables/{table}/close-session', [DiningTableController::class, 'closeSession'])->name('tables.close-session');
    Route::get('tables/{table}/qr.svg', [DiningTableController::class, 'downloadQr'])->name('tables.qr.download');
    Route::get('tables/{table}/print', [DiningTableController::class, 'printQr'])->name('tables.qr.print');
    Route::resource('tables', DiningTableController::class)->except(['show'])->parameters([
        'tables' => 'table',
    ]);
});
