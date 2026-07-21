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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('kyc_status', ['UNVERIFIED', 'PENDING', 'APPROVED', 'REJECTED'])->default('UNVERIFIED')->after('subscription_expires_at');
            $table->string('kyc_document_type')->nullable()->after('kyc_status'); // ID_CARD, PASSPORT, LICENSE
            $table->string('kyc_document_front')->nullable()->after('kyc_document_type');
            $table->string('kyc_document_back')->nullable()->after('kyc_document_front');
            $table->string('kyc_license_number')->nullable()->after('kyc_document_back');
            $table->text('kyc_rejected_reason')->nullable()->after('kyc_license_number');
            $table->dateTime('kyc_verified_at')->nullable()->after('kyc_rejected_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_status', 'kyc_document_type', 'kyc_document_front', 
                'kyc_document_back', 'kyc_license_number', 'kyc_rejected_reason', 'kyc_verified_at'
            ]);
        });
    }
};
