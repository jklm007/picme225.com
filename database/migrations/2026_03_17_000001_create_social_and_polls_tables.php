<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialAndPollsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Table for social posts
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->comment('Null for system/news posts');
            $table->enum('type', ['TRIP', 'NEWS', 'VIRAL', 'POLL', 'SOCIAL'])->default('SOCIAL');
            $table->enum('source', ['INTERNAL', 'ABIDJAN_NET', 'LINFODROME', 'KOACI', 'TIKTOK', 'YOUTUBE'])->default('INTERNAL');
            $table->string('category')->nullable()->comment('TRAFFIC, ACCIDENT, COMMUNITY, BUZZ');
            $table->unsignedInteger('trip_id')->nullable()->comment('Linked to user_requests or active_shared_rides');
            $table->string('trip_type')->nullable();
            $table->text('content');
            $table->string('media_url')->nullable();
            $table->string('external_link')->nullable();
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Table for likes
        Schema::create('post_likes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->integer('user_id');
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
        });

        // Table for comments
        Schema::create('post_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->integer('user_id');
            $table->text('comment');
            $table->timestamps();
        });

        // Table for Polls
        Schema::create('polls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id')->nullable();
            $table->string('question');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table for Poll Options
        Schema::create('poll_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('poll_id');
            $table->string('option_text');
            $table->integer('votes_count')->default(0);
            $table->timestamps();
        });

        // Table for Poll Votes
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('poll_id');
            $table->integer('poll_option_id');
            $table->integer('user_id');
            $table->timestamps();

            $table->unique(['poll_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
        Schema::dropIfExists('post_comments');
        Schema::dropIfExists('post_likes');
        Schema::dropIfExists('posts');
    }
}
