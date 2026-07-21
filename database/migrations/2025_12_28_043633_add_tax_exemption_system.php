<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxExemptionSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('tax_exemption_config');
        Schema::dropIfExists('tax_exemption_fund');

        Schema::create('tax_exemption_fund', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payment_id')->nullable();
            $table->decimal('virtual_tva_amount', 15, 2); // Montant TVA "virtuel" économisé
            $table->enum('allocation_type', [
                'treasury_reserve',      // Réserve pour payer la TVA après exonération
                'driver_bonus',          // Bonus aux chauffeurs (réduction commission)
                'platform_development',  // Développement plateforme
                'marketing_growth',      // Marketing et acquisition
                'insurance_pool',        // Renforcement pool assurance
                'cooperative_fund'       // Fonds coopératif
            ]);
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('exemption_end_date')->nullable(); // Date de fin d'exonération
            $table->timestamps();
            
            $table->foreign('payment_id')->references('id')->on('user_request_payments')->onDelete('set null');
        });
        
        // Table de configuration de l'exonération
        Schema::create('tax_exemption_config', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months')->default(36); // 3 ans
            $table->json('allocation_percentages'); // Répartition des fonds
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_exemption_fund');
        Schema::dropIfExists('tax_exemption_config');
    }
}
