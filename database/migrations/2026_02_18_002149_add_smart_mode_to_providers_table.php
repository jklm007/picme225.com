<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->boolean('is_smart_mode')->default(false);
            $table->enum('smart_mode_type', ['HOME', 'ZONE', 'COMMUNE'])->default('HOME');
            $table->double('smart_dest_lat')->nullable();
            $table->double('smart_dest_lng')->nullable();
            $table->string('smart_dest_address')->nullable();
            $table->double('smart_zone_radius')->default(5.0);
            $table->text('smart_communes')->nullable(); // JSON array
            $table->integer('smart_quota_count')->default(0);
            $table->timestamp('smart_last_used_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn([
                'is_smart_mode',
                'smart_mode_type',
                'smart_dest_lat',
                'smart_dest_lng',
                'smart_dest_address',
                'smart_zone_radius',
                'smart_communes',
                'smart_quota_count',
                'smart_last_used_at'
            ]);
        });
    }
};
