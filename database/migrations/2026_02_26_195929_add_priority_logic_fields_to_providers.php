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
            $table->integer('daily_cancelled_count')->default(0);
            $table->integer('daily_timeout_count')->default(0);
            $table->integer('completion_streak')->default(0);
            $table->date('last_priority_action_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['daily_cancelled_count', 'daily_timeout_count', 'completion_streak', 'last_priority_action_at']);
        });
    }
};
