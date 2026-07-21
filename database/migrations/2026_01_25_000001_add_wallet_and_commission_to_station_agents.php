<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWalletAndCommissionToStationAgents extends Migration
{
    public function up()
    {
        // 1. Ajouter le solde au profil de l'agent
        if (Schema::hasTable('station_agents')) {
            Schema::table('station_agents', function (Blueprint $table) {
                if (!Schema::hasColumn('station_agents', 'wallet_balance')) {
                    $table->decimal('wallet_balance', 10, 2)->default(0.00)->after('is_active');
                }
                if (!Schema::hasColumn('station_agents', 'commission_per_passenger')) {
                    $table->integer('commission_per_passenger')->default(50)->after('wallet_balance');
                }
                if (!Schema::hasColumn('station_agents', 'commission_per_parcel')) {
                    $table->integer('commission_per_parcel')->default(100)->after('commission_per_passenger');
                }
            });
        }

        // 2. Créer la table des transactions de commission pour la transparence
        if (!Schema::hasTable('agent_commission_logs')) {
            Schema::create('agent_commission_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->foreignId('station_agent_id')->constrained('station_agents')->onDelete('cascade');
                $table->unsignedInteger('fleet_id')->nullable(); // Patron qui reçoit l'argent
                $table->enum('type', ['PASSENGER', 'PARCEL_RECEIVE', 'PARCEL_SEND', 'WITHDRAWAL']);
                $table->decimal('amount', 10, 2);
                $table->string('reference_id'); // ID du trajet ou du colis
                $table->string('description')->nullable();
                $table->timestamps();


                $table->foreign('fleet_id')->references('id')->on('fleets')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('agent_commission_logs');
        if (Schema::hasTable('station_agents')) {
            Schema::table('station_agents', function (Blueprint $table) {
                $table->dropColumn(['wallet_balance', 'commission_per_passenger', 'commission_per_parcel']);
            });
        }
    }
}
