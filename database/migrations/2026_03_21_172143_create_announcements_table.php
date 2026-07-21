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
        Schema::create('announcement_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('announcement_categories')->onDelete('cascade');
            $table->string('creator_type'); // 'user' ou 'provider'
            $table->unsignedBigInteger('creator_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('depart')->nullable(); // Surtout pour Covoiturage/Convoi
            $table->string('arrivee')->nullable();
            $table->dateTime('departure_time')->nullable();
            $table->integer('seats')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->timestamps();
        });

        Schema::create('announcement_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('user_id'); // L'utilisateur qui like
            $table->timestamps();
        });

        Schema::create('announcement_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->onDelete('cascade');
            $table->string('sender_type'); // 'user' ou 'provider'
            $table->unsignedBigInteger('sender_id');
            $table->text('comment');
            $table->timestamps();
        });

        Schema::create('announcement_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('guest_id'); // 'user_id' qui reserve
            $table->integer('booked_seats')->default(1);
            $table->decimal('amount', 10, 2);
            $table->decimal('commission', 10, 2);
            $table->string('status')->default('pending'); // pending, escrow, paid, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_bookings');
        Schema::dropIfExists('announcement_comments');
        Schema::dropIfExists('announcement_likes');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('announcement_categories');
    }
};
