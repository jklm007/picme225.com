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
        Schema::create('pdp_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['COMMUNAL', 'INTER_COMMUNAL'])->default('COMMUNAL');
            $table->enum('status', ['PROPOSED', 'VOTING', 'APPROVED', 'REJECTED'])->default('PROPOSED');
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('total_votes')->default(0);
            $table->integer('positive_votes')->default(0);
            $table->integer('negative_votes')->default(0);
            $table->decimal('base_price_per_segment', 10, 2)->nullable()->comment('Prix de base entre deux arrêts');
            $table->decimal('detour_price_per_km', 10, 2)->nullable()->comment('Prix au km pour détour porte-à-porte');
            $table->integer('max_detour_communal')->default(5)->comment('Distance maximale de détour pour service communal (km)');
            $table->integer('max_detour_intercommunal')->default(10)->comment('Distance maximale de détour pour service inter-communal (km)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdp_routes');
    }
};

