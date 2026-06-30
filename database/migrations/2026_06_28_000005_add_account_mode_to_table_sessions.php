<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_sessions', function (Blueprint $table): void {
            $table->string('account_mode')->nullable()->after('status');
        });

        DB::table('table_sessions')
            ->whereNull('account_mode')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('table_guests')
                    ->whereColumn('table_guests.table_session_id', 'table_sessions.id');
            })
            ->update(['account_mode' => 'separate']);
    }

    public function down(): void
    {
        Schema::table('table_sessions', function (Blueprint $table): void {
            $table->dropColumn('account_mode');
        });
    }
};
