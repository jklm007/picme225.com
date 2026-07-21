<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSharedRideConfiguration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ajouter une colonne pour indiquer si un service type supporte le mode partagé
        Schema::table('service_types', function (Blueprint $table) {
            if (!Schema::hasColumn('service_types', 'is_shared')) {
                $table->boolean('is_shared')->default(false)->after('status');
            }
            if (!Schema::hasColumn('service_types', 'shared_type')) {
                $table->enum('shared_type', ['communal', 'intercommunal', 'both'])->nullable()->after('is_shared');
            }
            if (!Schema::hasColumn('service_types', 'shared_communal_base')) {
                $table->decimal('shared_communal_base', 10, 2)->nullable()->after('shared_type')->comment('Tarif de base pour trajet communal');
            }
            if (!Schema::hasColumn('service_types', 'shared_intercommunal_base')) {
                $table->decimal('shared_intercommunal_base', 10, 2)->nullable()->after('shared_communal_base')->comment('Tarif de base pour trajet intercommunal');
            }
            if (!Schema::hasColumn('service_types', 'shared_intercommunal_per_km')) {
                $table->decimal('shared_intercommunal_per_km', 10, 2)->nullable()->after('shared_intercommunal_base')->comment('Tarif par km pour intercommunal');
            }
        });

        // Ajouter les paramètres globaux pour le mode partagé
        DB::table('settings')->insert([
            [
                'key' => 'shared_max_passengers',
                'value' => '4',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'shared_max_detour_minutes',
                'value' => '10',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'shared_max_waiting_minutes',
                'value' => '5',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'shared_discount_per_passenger',
                'value' => '10',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'shared_max_discount',
                'value' => '30',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // Créer un service type exemple pour taxi partagé
        DB::table('service_types')->insert([
            'name' => 'Taxi Partagé',
            'provider_name' => 'Chauffeur Partagé',
            'image' => 'shared_taxi.png',
            'capacity' => 4,
            'fixed' => 0,
            'price' => 0,
            'minute' => 0,
            'hour' => 0,
            'distance' => 0,
            'calculator' => 'SHARED',
            'description' => 'Service de taxi partagé pour trajets communaux et intercommunaux',
            'status' => 1,
            'is_shared' => true,
            'shared_type' => 'both',
            'shared_communal_base' => 500.00,
            'shared_intercommunal_base' => 1000.00,
            'shared_intercommunal_per_km' => 200.00,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn([
                'is_shared',
                'shared_type',
                'shared_communal_base',
                'shared_intercommunal_base',
                'shared_intercommunal_per_km'
            ]);
        });

        DB::table('settings')->whereIn('key', [
            'shared_max_passengers',
            'shared_max_detour_minutes',
            'shared_max_waiting_minutes',
            'shared_discount_per_passenger',
            'shared_max_discount'
        ])->delete();

        DB::table('service_types')->where('calculator', 'SHARED')->delete();
    }
}
