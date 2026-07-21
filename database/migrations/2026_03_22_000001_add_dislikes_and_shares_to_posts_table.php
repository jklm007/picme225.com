<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDislikesAndSharesToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'dislikes_count')) {
                $table->integer('dislikes_count')->default(0)->after('likes_count');
            }
            if (!Schema::hasColumn('posts', 'shares_count')) {
                $table->integer('shares_count')->default(0)->after('comments_count');
            }
        });

        Schema::create('post_dislikes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->integer('user_id');
            $table->timestamps();
            
            $table->unique(['post_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_dislikes');
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['dislikes_count', 'shares_count']);
        });
    }
}
