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
        Schema::dropIfExists('subscription_plan_service_types');
        Schema::create('subscription_plan_service_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_plan_id');
            $table->unsignedBigInteger('service_type_id');
            $table->enum('commission_type', ['percentage', 'fixed']);
            $table->decimal('commission_value', 10, 2);
            $table->timestamps();

            $table->foreign('subscription_plan_id', 'sp_id_fk')->references('id')->on('subscription_plans')->onDelete('cascade');
            $table->foreign('service_type_id', 'st_id_fk')->references('id')->on('service_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plan_service_types');
    }
};
