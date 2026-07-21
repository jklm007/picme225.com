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
        Schema::dropIfExists('insurance_claims');
        Schema::create('insurance_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->decimal('amount_requested', 10, 2);
            $table->decimal('amount_approved', 10, 2)->default(0);
            $table->text('incident_description');
            $table->string('incident_location')->nullable();
            $table->timestamp('incident_date');
            $table->string('document_url')->nullable(); // Proof of accident/repair
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'DISPATCHED_TO_DAO'])->default('PENDING');
            $table->text('admin_comment')->nullable();
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_claims');
    }
};
