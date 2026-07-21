<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDualRoleSupport extends Migration
{
    public function up()
    {
        // 1. Ajouter user_type et fleet_id à la table users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'user_type')) {
                    $table->enum('user_type', ['USER', 'FLEET_OWNER', 'STATION_AGENT', 'DUAL'])->default('USER')->after('id');
                }
                if (!Schema::hasColumn('users', 'fleet_id')) {
                    // fleets.id est UNSIGNED INTEGER
                    $table->unsignedInteger('fleet_id')->nullable()->after('user_type');
                    $table->foreign('fleet_id')->references('id')->on('fleets')->onDelete('set null');
                }
                if (!Schema::hasColumn('users', 'station_agent_id')) {
                    // station_agents.id est UNSIGNED BIG INTEGER
                    $table->unsignedBigInteger('station_agent_id')->nullable()->after('fleet_id');
                    $table->foreign('station_agent_id')->references('id')->on('station_agents')->onDelete('set null');
                }
            });
        }

        // 2. Ajouter user_id aux tables fleets et station_agents
        if (Schema::hasTable('fleets')) {
            Schema::table('fleets', function (Blueprint $table) {
                if (!Schema::hasColumn('fleets', 'user_id')) {
                    // users.id est UNSIGNED BIG INTEGER
                    $table->unsignedBigInteger('user_id')->nullable()->after('id');
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }

        if (Schema::hasTable('station_agents')) {
            Schema::table('station_agents', function (Blueprint $table) {
                if (!Schema::hasColumn('station_agents', 'user_id')) {
                    // users.id est UNSIGNED BIG INTEGER
                    // Relation déjà définie dans CreateStationAgentsTable ? 
                    // Vérifions : CreateStationAgentsTable a déjà 'user_id'
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['fleet_id']);
                $table->dropForeign(['station_agent_id']);
                $table->dropColumn(['user_type', 'fleet_id', 'station_agent_id']);
            });
        }

        if (Schema::hasTable('fleets')) {
            Schema::table('fleets', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
}
