<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->boolean('is_subscription_trip')->default(false)->after('use_wallet');
        });
    }

    public function down(): void
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropColumn('is_subscription_trip');
        });
    }
};
