<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashCollectionsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cash_collections')) {
            Schema::create('cash_collections', function (Blueprint $table) {
                $table->increments('id');
                $table->foreignId('agent_id')->constrained('station_agents')->onDelete('cascade');
                $table->unsignedInteger('request_id');
                $table->decimal('amount', 10, 2);
                $table->timestamp('collected_at');
                $table->boolean('reconciled')->default(false);
                $table->timestamp('reconciled_at')->nullable();
                $table->unsignedInteger('reconciled_by')->nullable();
                $table->timestamps();


                $table->foreign('request_id')->references('id')->on('user_requests')->onDelete('cascade');
            });
        }

        // Add created_by_agent to user_requests if not exists
        if (Schema::hasTable('user_requests')) {
            Schema::table('user_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('user_requests', 'created_by_agent')) {
                    $table->unsignedInteger('created_by_agent')->nullable()->after('provider_id');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('cash_collections');

        if (Schema::hasTable('user_requests')) {
            Schema::table('user_requests', function (Blueprint $table) {
                $table->dropColumn('created_by_agent');
            });
        }
    }
}
