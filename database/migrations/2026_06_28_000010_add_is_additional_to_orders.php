<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->boolean('is_additional')->default(false)->after('placed_at');
        });

        $confirmedSessions = DB::table('table_sessions')
            ->whereNotNull('confirmed_at')
            ->pluck('confirmed_at', 'id');

        DB::table('orders')
            ->whereIn('table_session_id', $confirmedSessions->keys())
            ->whereNotNull('placed_at')
            ->orderBy('id')
            ->get(['id', 'table_session_id', 'placed_at'])
            ->each(function (object $order) use ($confirmedSessions): void {
                $confirmedAt = $confirmedSessions->get($order->table_session_id);

                if ($confirmedAt && Carbon::parse($order->placed_at)->greaterThan(Carbon::parse($confirmedAt))) {
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['is_additional' => true]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('is_additional');
        });
    }
};
