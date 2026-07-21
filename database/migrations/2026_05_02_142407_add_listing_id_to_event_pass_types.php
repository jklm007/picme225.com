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
        Schema::table('event_pass_types', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->nullable()->after('event_id');
        });

        Schema::table('transport_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->nullable()->after('transport_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_pass_types', function (Blueprint $table) {
            $table->dropColumn('listing_id');
        });

        Schema::table('transport_tickets', function (Blueprint $table) {
            $table->dropColumn('listing_id');
        });
    }
};
