<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_guests', function (Blueprint $table): void {
            $table->string('guest_token')->nullable()->unique()->after('alias');
            $table->timestamp('joined_at')->nullable()->after('guest_token');
        });

        DB::table('table_guests')
            ->orderBy('id')
            ->get(['id', 'created_at'])
            ->each(function (object $guest): void {
                do {
                    $token = Str::random(48);
                } while (DB::table('table_guests')->where('guest_token', $token)->exists());

                DB::table('table_guests')
                    ->where('id', $guest->id)
                    ->update([
                        'guest_token' => $token,
                        'joined_at' => $guest->created_at ?? now(),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('table_guests', function (Blueprint $table): void {
            $table->dropUnique(['guest_token']);
            $table->dropColumn(['guest_token', 'joined_at']);
        });
    }
};
