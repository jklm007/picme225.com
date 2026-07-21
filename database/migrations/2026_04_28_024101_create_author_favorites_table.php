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
        Schema::create('author_favorites', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id'); // L'utilisateur qui met en favori
            $table->integer('author_id'); // L'ID de l'auteur (User ou Provider)
            $table->string('author_type'); // 'USER' ou 'PROVIDER'
            $table->timestamps();
            
            $table->unique(['user_id', 'author_id', 'author_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_favorites');
    }
};
