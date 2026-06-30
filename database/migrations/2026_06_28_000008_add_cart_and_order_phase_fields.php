<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedInteger('subtotal')->default(0)->after('status');
            $table->timestamp('placed_at')->nullable()->after('notes');
        });

        DB::table('orders')->update([
            'subtotal' => DB::raw('total'),
            'placed_at' => DB::raw('created_at'),
        ]);

        DB::table('orders')
            ->whereIn('status', ['pending', 'confirmed'])
            ->update(['status' => 'new']);

        Schema::table('order_items', function (Blueprint $table): void {
            $table->unsignedInteger('line_total')->default(0)->after('quantity');
            $table->string('notes', 160)->nullable()->after('line_total');
        });

        DB::table('order_items')->update([
            'line_total' => DB::raw('subtotal'),
        ]);

        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('table_guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('line_total');
            $table->string('notes', 160)->nullable();
            $table->timestamps();

            $table->unique(['table_guest_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn(['line_total', 'notes']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['subtotal', 'placed_at']);
        });
    }
};
