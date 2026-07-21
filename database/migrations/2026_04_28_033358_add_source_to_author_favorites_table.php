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
        Schema::table('author_favorites', function (Blueprint $table) {
            $table->unsignedInteger('author_id')->nullable()->change();
            $table->string('source_name')->nullable()->after('author_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('author_favorites', function (Blueprint $table) {
            $table->unsignedInteger('author_id')->nullable(false)->change();
            $table->dropColumn('source_name');
        });
    }
};
