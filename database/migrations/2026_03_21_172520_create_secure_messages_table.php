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
        // Table des messages internes sécurisés
        Schema::create('secure_messages', function (Blueprint $table) {
            $table->id();
            
            // L'expéditeur
            $table->string('sender_type');
            $table->unsignedBigInteger('sender_id');
            
            // Le destinataire
            $table->string('receiver_type');
            $table->unsignedBigInteger('receiver_id');
            
            // Annonce concernée (optionnel, pour lier la discussion à un bien/service)
            $table->unsignedBigInteger('announcement_id')->nullable();
            
            // Contenu du message
            $table->text('message');
            
            // Flags de modération et état
            $table->boolean('is_read')->default(false);
            $table->boolean('is_flagged')->default(false); // Vrai si le système détecte un numéro de téléphone/email
            $table->boolean('is_blocked')->default(false); // Vrai si un admin ou le filtre auto bloque ce message
            
            $table->timestamps();
            
            // Foreign key pour announcement_id
            // $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secure_messages');
    }
};
