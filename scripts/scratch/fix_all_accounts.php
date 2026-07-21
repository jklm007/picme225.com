<?php
// Script de réparation GLOBAL pour TOUS les comptes de test
require __DIR__ . '/bootstrap/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Fleet;
use App\StationAgent;
use App\PdpStop;
use App\InterurbanCompany;

// CORRECTION DE SCHEMA (Au cas où)
try {
    \DB::statement("ALTER TABLE users MODIFY COLUMN user_type VARCHAR(50) DEFAULT 'USER'");
    echo "Schema users corrigé.\n";
} catch (\Exception $e) {
}

$password = bcrypt('123456');

// ==========================================
// 1. REPARATION SMALL OWNER (+22502020202)
// ==========================================
echo "\n--- REPARATION SMALL OWNER ---\n";
$mobileSmall = '+22502020202';
$cleanSmall = '02020202';

// Gestion Fleet
$fleetSmall = Fleet::where('mobile', $mobileSmall)->orWhere('mobile', $cleanSmall)->first();
if (!$fleetSmall) {
    echo "Création Fleet Small Owner...\n";
    $fleetSmall = Fleet::create([
        'name' => 'Small Owner',
        'email' => 'small@picme.com',
        'password' => $password,
        'mobile' => $mobileSmall,
        // 'type' => 'STANDARD', // Désactivé pour éviter erreur 1265
        'company' => 'Small Trans'
    ]);
} else {
    // Force update password
    $fleetSmall->password = $password;
    $fleetSmall->save();
    echo "Fleet Small Owner OK (Psswd reset).\n";
}

// Gestion User
$userSmall = User::where('mobile', $mobileSmall)->orWhere('mobile', $cleanSmall)->first();
if (!$userSmall) {
    echo "Création User Small Owner...\n";
    $userSmall = User::create([
        'first_name' => 'Small',
        'last_name' => 'Owner',
        'email' => 'smalluser@picme.com',
        'mobile' => $mobileSmall,
        'password' => $password,
        'payment_mode' => 'CASH'
    ]);
} else {
    $userSmall->password = $password;
    $userSmall->save();
    echo "User Small Owner OK (Psswd reset).\n";
}

// Liaison
$userSmall->update(['user_type' => 'FLEET_OWNER', 'fleet_id' => $fleetSmall->id]);
$fleetSmall->update(['user_id' => $userSmall->id]);
echo "Liaison Small Owner OK.\n";


// ==========================================
// 2. REPARATION AGENT (+22503030303)
// ==========================================
echo "\n--- REPARATION AGENT ---\n";
$mobileAgent = '+22503030303';
$cleanAgent = '03030303';

// Pré-requis: Stop et Compagnie
$stop = PdpStop::firstOrCreate(['name' => 'Gare Nord'], ['location_name' => 'Abidjan Nord', 'latitude' => 5.35, 'longitude' => -4.0, 'is_active' => 1]);
$company = InterurbanCompany::firstOrCreate(['name' => 'Transport Express'], ['status' => 'active', 'logo' => 'default.png']);

// Gestion User Agent D'ABORD (pour avoir l'ID)
$userAgent = User::where('mobile', $mobileAgent)->orWhere('mobile', $cleanAgent)->first();
if (!$userAgent) {
    echo "Création User Agent...\n";
    $userAgent = User::create([
        'first_name' => 'Agent',
        'last_name' => 'Gare',
        'email' => 'agentuser@picme.com',
        'mobile' => $mobileAgent,
        'password' => $password,
        'payment_mode' => 'CASH'
    ]);
} else {
    $userAgent->password = $password;
    $userAgent->save();
    echo "User Agent OK (Psswd reset).\n";
}

// Gestion Agent ENSUITE
$agent = StationAgent::where('user_id', $userAgent->id)->first();
if (!$agent) {
    echo "Création Agent lié au User {$userAgent->id}...\n";
    $agent = new StationAgent();
    $agent->user_id = $userAgent->id;
    $agent->agent_code = 'AGT-' . rand(1000, 9999);
    $agent->pdp_stop_id = $stop->id;
    $agent->interurban_company_id = $company->id;
    $agent->is_active = true;
    $agent->save();
} else {
    echo "Agent OK.\n";
}

// Liaison inverse
$userAgent->update(['user_type' => 'STATION_AGENT', 'station_agent_id' => $agent->id]);
echo "Liaison Agent OK.\n";

echo "\n--- REPARATION BIG OWNER (+22501010101) ---\n";
// Assurer que le Big Owner est aussi à jour avec le bon password
$mobileBig = '+22501010101';
$userBig = User::where('mobile', $mobileBig)->first();
if ($userBig) {
    $userBig->password = $password;
    $userBig->save();
    echo "Big Owner Psswd Reset OK.\n";
}

echo "\n--- TERMINÉ : TOUS LES COMPTES SONT RÉPARÉS ---\n";
