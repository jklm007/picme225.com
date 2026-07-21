<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddGoldSubscriptionLevel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modifier l'enum pour ajouter 'gold'
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE providers MODIFY COLUMN subscription_level ENUM('none', 'standard', 'eco', 'pro', 'gold') DEFAULT 'none'");
        }
        
        // Créer un plan GOLD par défaut si la table subscription_plans existe
        if (Schema::hasTable('subscription_plans')) {
            DB::table('subscription_plans')->insert([
                'name' => 'GOLD',
                'price' => 20000, // 20,000 CFA/mois
                'commission_type' => 'fixed',
                'commission_value' => 50, // 50 CFA fixe par course
                'priority' => 100, // Priorité maximale
                'insurance_included' => 1,
                'staking_bonus_percentage' => 5.0,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer le plan GOLD
        if (Schema::hasTable('subscription_plans')) {
            DB::table('subscription_plans')->where('name', 'GOLD')->delete();
        }
        
        // Revenir à l'ancien enum
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE providers MODIFY COLUMN subscription_level ENUM('none', 'standard', 'eco', 'pro') DEFAULT 'none'");
        }
    }
}
