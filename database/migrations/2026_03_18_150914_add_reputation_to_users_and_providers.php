<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('social_points')->default(0);
            $table->decimal('social_rating', 3, 2)->default(5.00);
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->integer('social_points')->default(0);
            $table->decimal('social_rating', 3, 2)->default(5.00);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['social_points', 'social_rating']);
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['social_points', 'social_rating']);
        });
    }
};
