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
        Schema::table('dao_votes', function (Blueprint $table) {
            $table->integer('votes_weight')->default(1)->after('token_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dao_votes', function (Blueprint $table) {
            $table->dropColumn('votes_weight');
        });
    }
};
