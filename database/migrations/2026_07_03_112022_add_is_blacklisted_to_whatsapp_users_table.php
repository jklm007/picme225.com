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
        Schema::table('whatsapp_users', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_users', 'is_blacklisted')) {
                $table->boolean('is_blacklisted')->default(false)->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_users', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_users', 'is_blacklisted')) {
                $table->dropColumn('is_blacklisted');
            }
        });
    }
};
