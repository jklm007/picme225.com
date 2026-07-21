<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCleanupFieldsToMarketplaceListings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketplace_listings', function (Blueprint $blueprint) {
            $blueprint->timestamp('cleanup_prompt_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketplace_listings', function (Blueprint $blueprint) {
            $blueprint->dropColumn('cleanup_prompt_at');
        });
    }
}
