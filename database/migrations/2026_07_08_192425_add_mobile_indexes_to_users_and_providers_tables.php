<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMobileIndexesToUsersAndProvidersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->index('mobile');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        Schema::table('providers', function (Blueprint $table) {
            try {
                $table->index('mobile');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['mobile']);
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->dropIndex(['mobile']);
        });
    }
}
