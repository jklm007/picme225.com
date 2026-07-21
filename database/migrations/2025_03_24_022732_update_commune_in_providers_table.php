<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Provider;
use App\Service\GoogleMapsService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // IMPORTANT: RETIREZ DB::beginTransaction(); et le bloc try/catch ici.
        // Laravel gère déjà les transactions pour les migrations.

        // Step 1: Ensure the 'commune' column is nullable and has the desired length.
        // Cette étape suppose que 'commune' a été AJOUTÉE par une migration PRÉCÉDENTE.
        Schema::table('providers', function (Blueprint $table) {
            $table->string('commune', 1000)->nullable()->change();
        });

        // Step 2: Populate the 'commune' column using reverse geocoding.
        $googleMapsService = new GoogleMapsService();

        Provider::chunk(200, function ($providers) use ($googleMapsService) {
            foreach ($providers as $provider) {
                if ($provider->latitude === null || $provider->longitude === null) {
                    \Log::warning("Provider {$provider->id} has NULL coordinates. Skipping geocoding.");
                    continue;
                }

                $commune = $googleMapsService->reverseGeocode($provider->latitude, $provider->longitude);

                if ($commune) {
                    $provider->commune = $commune;
                    $provider->save();
                    \Log::info("Provider ID: " . $provider->id . " - Commune updated to: " . $commune);
                    \Log::info("Commune Length (chars): " . mb_strlen($commune, 'UTF-8'));
                } else {
                    \Log::error("Failed to get commune for provider ID: {$provider->id} at Lat: {$provider->latitude}, Lng: {$provider->longitude}");
                }
            }
        });

        // Step 3: Convert any remaining NULL 'commune' values to empty strings.
        DB::table('providers')
            ->whereNull('commune')
            ->update(['commune' => '']);
        \Log::info("All remaining NULL 'commune' values have been updated to empty strings.");

        // Step 4: Finally, make the 'commune' column NOT NULL.
        Schema::table('providers', function (Blueprint $table) {
            $table->string('commune', 1000)->nullable(false)->change();
        });

        // IMPORTANT: RETIREZ DB::commit(); ici.
        \Log::info("Migration 'update_commune_in_providers_table' completed successfully.");

    } // IMPORTANT: RETIREZ le bloc catch (\Exception $e) ici.

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Dans le cas d'un rollback, vous voudriez revenir à l'état précédent.
        // Si la colonne commune a été rendue NOT NULL, on la rendra nullable.
        // Si elle a été créée par cette migration, on la supprimerait.
        // Ici, votre migration ne fait que des modifications, donc on annule les modifications.
        Schema::table('providers', function (Blueprint $table) {
            // Remettre la colonne nullable pour permettre les NULLs lors d'un rollback.
            $table->string('commune', 1000)->nullable()->change(); // Revert to nullable
        });
        \Log::info("Migration 'update_commune_in_providers_table' rolled back.");
    }
};
