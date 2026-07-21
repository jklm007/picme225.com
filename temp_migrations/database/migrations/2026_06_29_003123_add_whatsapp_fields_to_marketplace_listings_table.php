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
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->string('source')->default('web'); // 'web', 'mobile', 'whatsapp'
            $table->unsignedBigInteger('whatsapp_message_id')->nullable();
            $table->float('ai_confidence_score')->nullable();
            
            $table->foreign('whatsapp_message_id')->references('id')->on('whatsapp_messages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropForeign(['whatsapp_message_id']);
            $table->dropColumn(['source', 'whatsapp_message_id', 'ai_confidence_score']);
        });
    }
};
