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
        Schema::table('chat_quotes', function (Blueprint $table) {
            $table->json('completion_images')->nullable()->after('status');
            $table->json('dispute_images')->nullable()->after('completion_images');
            $table->text('dispute_reason')->nullable()->after('dispute_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_quotes', function (Blueprint $table) {
            $table->dropColumn(['completion_images', 'dispute_images', 'dispute_reason']);
        });
    }
};
