<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('type')->default('individual')->after('table_guest_id');
            $table->string('status')->default('paid')->after('amount');
            $table->string('reference')->nullable()->after('paid_at');
        });

        DB::table('payments')->update([
            'type' => DB::raw('scope'),
            'status' => 'paid',
        ]);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn(['type', 'status', 'reference']);
        });
    }
};
