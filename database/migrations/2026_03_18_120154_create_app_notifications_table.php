<?xml version="1.0" encoding="utf-8"?>
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table centralisée pour l'historique des notifications in-app (Social & Marketplace).
     */
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('GENERAL'); // SOCIAL, MARKETPLACE, WALLET, SYSTEM
            $table->string('action_id')->nullable()->comment('ID du post, de la vente ou du trajet lié');
            $table->string('action_type')->nullable(); // POST, MARKETPLACE_LISTING, RENTAL_BOOKING
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
