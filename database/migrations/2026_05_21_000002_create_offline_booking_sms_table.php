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
        Schema::create('offline_booking_sms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('provider_id');
            $table->string('provider_phone', 20);
            $table->string('sms_code', 6);
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED', 'EXPIRED'])->default('PENDING');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('request_id');
            $table->index('provider_id');
            $table->index('sms_code');
            $table->index('status');
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_booking_sms');
    }
};
