<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_guests', function (Blueprint $table): void {
            $table->boolean('is_ready')->default(false)->after('status');
            $table->timestamp('ready_at')->nullable()->after('is_ready');
        });

        Schema::table('table_sessions', function (Blueprint $table): void {
            $table->timestamp('confirmed_at')->nullable()->after('closed_at');
            $table->foreignId('confirmed_by_guest_id')
                ->nullable()
                ->after('confirmed_at')
                ->constrained('table_guests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('table_sessions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('confirmed_by_guest_id');
            $table->dropColumn('confirmed_at');
        });

        Schema::table('table_guests', function (Blueprint $table): void {
            $table->dropColumn(['is_ready', 'ready_at']);
        });
    }
};
