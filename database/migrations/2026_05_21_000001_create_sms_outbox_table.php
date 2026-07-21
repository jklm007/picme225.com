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
        Schema::create('sms_outbox', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone_number', 20);
            $table->text('message');
            $table->string('network', 20)->nullable();
            $table->enum('status', ['PENDING', 'SENDING', 'SENT', 'FAILED'])->default('PENDING');
            $table->integer('attempts')->default(0);
            $table->unsignedInteger('gateway_node_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('phone_number');
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_outbox');
    }
};
