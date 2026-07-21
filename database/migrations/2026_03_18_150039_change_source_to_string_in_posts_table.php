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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('source')->default('INTERNAL')->change();
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->enum('source', ['INTERNAL', 'ABIDJAN_NET', 'LINFODROME', 'KOACI', 'TIKTOK', 'YOUTUBE'])->default('INTERNAL')->change();
        });
    }
};
