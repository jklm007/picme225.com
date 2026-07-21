<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsAndLogsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_request_id'); // Linked to user_requests
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token')->unique(); // ULID or UUID
            $table->string('signature'); // HMAC-SHA256
            $table->enum('status', ['PENDING', 'VALIDATED', 'EXPIRED', 'CANCELLED'])->default('PENDING');
            $table->timestamp('expires_at');
            $table->timestamp('validated_at')->nullable();
            $table->string('validated_by_type')->nullable(); // 'provider', 'dispatcher', 'admin'
            $table->unsignedInteger('validated_by_id')->nullable();
            $table->text('qr_code_data')->nullable(); // Optional: cached QR content
            $table->timestamps();

            $table->foreign('user_request_id')->references('id')->on('user_requests')->onDelete('cascade');
        });

        Schema::create('ticket_validation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->string('scanned_by_type');
            $table->unsignedInteger('scanned_by_id');
            $table->string('status'); // SUCCESS, INVALID_SIGNATURE, EXPIRED, ALREADY_USED
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Location, device info
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
        });

        Schema::create('driver_assignment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_request_id');
            $table->unsignedInteger('dispatcher_id');
            $table->enum('assignment_mode', ['MANUAL', 'BROADCAST']);
            $table->unsignedInteger('provider_id')->nullable(); // For manual or the winner of broadcast
            $table->string('status'); // INITIATED, ACCEPTED, REJECTED, TIMEOUT
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('driver_assignment_logs');
        Schema::dropIfExists('ticket_validation_logs');
        Schema::dropIfExists('tickets');
    }
}
