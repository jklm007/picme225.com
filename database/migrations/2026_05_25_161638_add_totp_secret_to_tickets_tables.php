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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('totp_secret')->nullable()->after('token');
        });

        Schema::table('transport_tickets', function (Blueprint $table) {
            $table->string('totp_secret')->nullable()->after('qr_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('totp_secret');
        });

        Schema::table('transport_tickets', function (Blueprint $table) {
            $table->dropColumn('totp_secret');
        });
    }
};
