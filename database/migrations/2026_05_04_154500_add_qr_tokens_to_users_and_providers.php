<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQrTokensToUsersAndProviders extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('qr_id')->unique()->nullable()->after('id');
            $table->string('qr_token')->unique()->nullable()->after('qr_id');
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->string('qr_id')->unique()->nullable()->after('id');
            $table->string('qr_token')->unique()->nullable()->after('qr_id');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['qr_id', 'qr_token']);
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['qr_id', 'qr_token']);
        });
    }
}
