<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToPostLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_likes', function (Blueprint $count) {
            if (!Schema::hasColumn('post_likes', 'type')) {
                $count->string('type')->default('LIKE'); // LIKE, DISLIKE, FAVORITE
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
        Schema::table('post_likes', function (Blueprint $count) {
            $count->dropColumn('type');
        });
    }
}
