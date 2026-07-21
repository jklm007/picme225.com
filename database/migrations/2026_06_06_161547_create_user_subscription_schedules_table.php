<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscription_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('service_id')->nullable();
            
            $table->string('s_address');
            $table->double('s_lat', 15, 8);
            $table->double('s_lng', 15, 8);
            
            $table->string('d_address');
            $table->double('d_lat', 15, 8);
            $table->double('d_lng', 15, 8);
            
            $table->time('pickup_time');
            $table->time('return_time')->nullable();
            
            $table->string('active_days')->default('[]'); // JSON array of days e.g. ["MON", "TUE"]
            $table->decimal('monthly_price', 10, 2)->default(0);
            
            $table->enum('status', ['ACTIVE', 'PAUSED', 'CANCELLED'])->default('ACTIVE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscription_schedules');
    }
};
