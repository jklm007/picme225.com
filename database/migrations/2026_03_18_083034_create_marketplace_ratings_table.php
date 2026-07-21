<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->comment('User who rated');
            $table->unsignedInteger('seller_id')->comment('Seller being rated');
            $table->unsignedBigInteger('listing_id');
            $table->tinyInteger('rating')->default(5);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('listing_id')->references('id')->on('marketplace_listings');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_ratings');
    }
};
