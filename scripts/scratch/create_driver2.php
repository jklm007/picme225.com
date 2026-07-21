<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Supprimer l ancien driver de test
\App\Provider::where('email', 'testdriver@picme.ci')->delete();

// 2. Creer un vrai driver avec les bons champs
// Un driver a UN service_type_id principal sur la table providers
// Et peut avoir des entrees dans provider_services (ses vehicules/services actifs)

$driver = new \App\Provider();
$driver->first_name = 'Ahmed';
$driver->last_name = 'Konan';
$driver->mobile = '+2250777000001'; // Login par telephone
$driver->email = 'ahmed.konan@picme.ci';
$driver->password = bcrypt('Driver@123');
$driver->status = 'approved';
$driver->is_verified = 1;
$driver->latitude = 5.345000; // Cocody, proche du point de test
$driver->longitude = -4.024000;
$driver->available = 1; // En ligne
$driver->service_type_id = 15; // Woro-Woro (communal, Cocody)
$driver->commune = 'Cocody';
$driver->eco_wallet_balance = 50000;
$driver->rating = 4.8;
$driver->trust_score = 90;
$driver->save();

echo "Driver ID: " . $driver->id . "\n";

// 3. Creer l entree dans provider_services pour Woro-Woro (ID=15)
// Un driver peut avoir plusieurs vehicules dans provider_services
// mais est principalement rattache a UN type
$ps = \App\ProviderService::firstOrNew([
    'provider_id' => $driver->id,
    'service_type_id' => 15 // Woro-Woro
]);
$ps->status = 'active';
$ps->service_number = 'AB 1234 CK';
$ps->service_model = 'Toyota Corolla Blanc';
$ps->save();
echo "ProviderService Woro-Woro OK\n";

// Aussi ajouter Taxi Vtc pour tester arret_pdp taxi
$ps2 = \App\ProviderService::firstOrNew([
    'provider_id' => $driver->id,
    'service_type_id' => 1 // Taxi Vtc
]);
$ps2->status = 'active';
$ps2->service_number = 'AB 1234 CK';
$ps2->service_model = 'Toyota Corolla Blanc';
$ps2->save();
echo "ProviderService Taxi Vtc OK\n";

echo "\n=== DRIVER DE TEST CREE ===\n";
echo "Nom: Ahmed Konan\n";
echo "Telephone: +2250777000001\n";
echo "Mot de passe: Driver\@123\n";
echo "Position: Cocody (pres point test)\n";
echo "Services: Woro-Woro (ID=15) + Taxi Vtc (ID=1)\n";
echo "Statut: APPROUVE + EN LIGNE\n";
