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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whatsapp_user_id');
            $table->string('group_id')->nullable();
            $table->text('content')->nullable();
            $table->json('medias')->nullable();
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'ignored'])->default('pending');
            $table->text('error_log')->nullable();
            $table->timestamps();
            
            $table->foreign('whatsapp_user_id')->references('id')->on('whatsapp_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
