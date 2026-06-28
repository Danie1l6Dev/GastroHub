<?php

use App\Enums\TableStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dining_tables', function (Blueprint $table): void {
            $table->string('code')->nullable()->after('name');
            $table->string('current_status')->default(TableStatus::Available->value)->after('qr_token');
        });

        foreach (DB::table('dining_tables')->orderBy('id')->get() as $table) {
            DB::table('dining_tables')
                ->where('id', $table->id)
                ->update([
                    'code' => 'T'.str_pad((string) $table->id, 2, '0', STR_PAD_LEFT),
                    'qr_token' => $table->qr_token ?: Str::random(48),
                    'current_status' => $table->current_status ?? TableStatus::Available->value,
                ]);
        }

        Schema::table('dining_tables', function (Blueprint $table): void {
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('dining_tables', function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'current_status']);
        });
    }
};
