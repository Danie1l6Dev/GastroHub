<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table): void {
            $table->string('slug')->after('name')->default('gastrohub-bistro');
            $table->string('logo_path')->nullable()->after('description');
            $table->string('cover_image_path')->nullable()->after('logo_path');
            $table->string('primary_color', 7)->default('#CD0508')->after('cover_image_path');
            $table->string('secondary_color', 7)->default('#000000')->after('primary_color');
            $table->string('instagram_url')->nullable()->after('opening_hours');
            $table->boolean('is_open')->default(true)->after('instagram_url');
        });

        Schema::table('restaurant_settings', function (Blueprint $table): void {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug',
                'logo_path',
                'cover_image_path',
                'primary_color',
                'secondary_color',
                'instagram_url',
                'is_open',
            ]);
        });
    }
};
