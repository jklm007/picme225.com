<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_requests', function (Blueprint $table) {
            try {
                $table->index(['user_id', 'status'], 'idx_requests_user_status');
            } catch (\Exception $e) {}
            
            try {
                $table->index(['provider_id', 'status'], 'idx_requests_provider_status');
            } catch (\Exception $e) {}

            try {
                $table->index('created_at', 'idx_requests_created_at');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_requests', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_requests_user_status');
            } catch (\Exception $e) {}

            try {
                $table->dropIndex('idx_requests_provider_status');
            } catch (\Exception $e) {}

            try {
                $table->dropIndex('idx_requests_created_at');
            } catch (\Exception $e) {}
        });
    }
};
