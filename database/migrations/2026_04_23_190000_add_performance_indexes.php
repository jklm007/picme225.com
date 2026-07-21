<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PERFORMANCE: Add composite indexes to speed up the most frequent queries.
 * These indexes target:
 *   1. The Social Feed (corridorFeed) — the most queried endpoint
 *   2. Stories listing (getStories)
 *   3. PostLike / PostComment lookups (toggle operations)
 *   4. User search
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── POSTS TABLE (Social Feed) ───
        Schema::table('posts', function (Blueprint $table) {
            // Main feed query: WHERE status='ACTIVE' AND deleted_at IS NULL ORDER BY created_at DESC
            // This composite index covers the corridorFeed query perfectly
            try {
                $table->index(['status', 'deleted_at', 'created_at'], 'idx_posts_feed_main');
            } catch (\Exception $e) {}

            // Feed filtered by route corridor
            try {
                $table->index(['pdp_route_id', 'status', 'created_at'], 'idx_posts_corridor_feed');
            } catch (\Exception $e) {}

            // Stories query: type IN (SOCIAL_PIC, SOCIAL_VID) + status + expires_at
            try {
                $table->index(['type', 'status', 'expires_at'], 'idx_posts_stories');
            } catch (\Exception $e) {}
        });

        // ─── POST_LIKES TABLE ───
        Schema::table('post_likes', function (Blueprint $table) {
            try {
                $table->unique(['post_id', 'user_id'], 'idx_post_likes_unique');
            } catch (\Exception $e) {}
        });

        // ─── POST_COMMENTS TABLE ───
        Schema::table('post_comments', function (Blueprint $table) {
            try {
                $table->index(['post_id', 'created_at'], 'idx_comments_by_post');
            } catch (\Exception $e) {}
        });

        // ─── USERS TABLE (Search) ───
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->index(['first_name', 'last_name'], 'idx_users_name_search');
            } catch (\Exception $e) {}
            try {
                $table->index('mobile', 'idx_users_mobile');
            } catch (\Exception $e) {}
        });

        // ─── RIDE_BOOKINGS TABLE ───
        Schema::table('ride_bookings', function (Blueprint $table) {
            try {
                $table->index(['active_shared_ride_id', 'status'], 'idx_bookings_ride_status');
            } catch (\Exception $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('idx_posts_feed_main');
            $table->dropIndex('idx_posts_corridor_feed');
            $table->dropIndex('idx_posts_stories');
        });
        Schema::table('post_likes', function (Blueprint $table) {
            $table->dropIndex('idx_post_likes_unique');
        });
        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropIndex('idx_comments_by_post');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_name_search');
            $table->dropIndex('idx_users_mobile');
        });
        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_ride_status');
        });
    }
};
