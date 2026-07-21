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
        Schema::table('user_requests', function (Blueprint $table) {
            $table->dateTime('rental_start_date')->nullable()->after('rental_hours');
            $table->dateTime('rental_end_date')->nullable()->after('rental_start_date');
            $table->boolean('rental_with_driver')->default(true)->after('rental_end_date');
            
            // Urgence
            $table->unsignedInteger('hospital_id')->nullable()->after('rental_with_driver');
            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropForeign(['hospital_id']);
            $table->dropColumn([
                'rental_start_date', 
                'rental_end_date', 
                'rental_with_driver',
                'hospital_id'
            ]);
        });
    }
};
