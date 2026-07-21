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
        Schema::table('active_shared_rides', function (Blueprint $table) {
            if (!Schema::hasColumn('active_shared_rides', 'price_per_seat')) {
                $table->decimal('price_per_seat', 10, 2)->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('active_shared_rides', function (Blueprint $table) {
            $table->dropColumn('price_per_seat');
        });
    }
};
