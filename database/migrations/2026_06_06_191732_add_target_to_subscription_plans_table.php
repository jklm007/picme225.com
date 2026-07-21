<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->enum('target', ['provider', 'user'])->default('provider')->after('name');
        });

        // Update existing user plans to target='user'
        DB::table('subscription_plans')
            ->whereIn('name', [
                'WORK PASS — Mensuel',
                'SCHOOL PASS — Mensuel',
                'WORK PASS — Hebdomadaire',
                'PREMIUM PASS — Mensuel'
            ])
            ->update(['target' => 'user']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('target');
        });
    }
};
