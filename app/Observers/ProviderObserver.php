<?php

namespace App\Observers;

use App\Models\Provider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProviderObserver
{
    /**
     * Handle the Provider "created" event.
     *
     * @param  \App\Models\Provider  $provider
     * @return void
     */
    public function created(Provider $provider)
    {
        // Règle Métier : Bonus Lancement (Temporaire)
        // Les 1000 premiers chauffeurs inscrits (par ServiceType) reçoivent 100 ECO.
        
        if ($provider->service_type_id) {
            $count = Provider::where('service_type_id', $provider->service_type_id)->count();

            if ($count <= 1000) {
                $provider->eco_wallet_balance = 100.0000;
                $provider->bonus_expires_at = Carbon::now()->addDays(90);
                $provider->save();

                Log::info("Bonus de lancement appliqué pour le Provider #{$provider->id} (ServiceType: {$provider->service_type_id})");
            }
        }
    }
}
