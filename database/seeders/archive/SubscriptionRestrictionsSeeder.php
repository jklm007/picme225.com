<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceType;

class SubscriptionRestrictionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // On marque les services spécifiques comme nécessitant un abonnement Pro
        $servicesToProtect = [
            'Shared Ride',
            'Voyage',
            'Interurban',
            'Location'
        ];

        foreach ($servicesToProtect as $name) {
            ServiceType::where('name', 'LIKE', '%' . $name . '%')
                ->update(['requires_pro_subscription' => 1]);
        }
        
        echo "Services premium protégés par abonnement.\n";
    }
}
