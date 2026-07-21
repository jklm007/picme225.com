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
            $table->string('author_type')->default('USER')->after('user_id');
        });
        
        // Update existing posts that are likely from providers (e.g. TRIP types with no matching user)
        // For now, let's just assume everything is USER and let new ones be correct.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('author_type');
        });
    }
};
