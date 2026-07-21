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
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->change();
            $table->dateTime('published_at')->nullable()->after('content');
            $table->date('publication_date')->nullable()->after('published_at');
            $table->time('publication_time')->nullable()->after('publication_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable(false)->change();
            $table->dropColumn(['published_at', 'publication_date', 'publication_time']);
        });
    }
};
