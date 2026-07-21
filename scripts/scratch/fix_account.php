<?php
// Script de réparation de compte pour +22501010101
require __DIR__ . '/bootstrap/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Fleet;

$mobile = '+22501010101';
$cleanMobile = '01010101';

echo "--- REPARATION DU COMPTE BIG OWNER ---\n";

// CORRECTION DE SCHEMA FORCEE
try {
    echo "Correction de la colonne user_type...\n";
    \DB::statement("ALTER TABLE users MODIFY COLUMN user_type VARCHAR(50) DEFAULT 'USER'");
    echo "Colonne user_type corrigée en VARCHAR(50).\n";
} catch (\Exception $e) {
    echo "Exception schema (ignorée): " . $e->getMessage() . "\n";
}

// 1. Trouver le User
$user = User::where('mobile', $mobile)
    ->orWhere('mobile', $cleanMobile)
    ->orWhere('mobile', '225' . $cleanMobile)
    ->orWhere('mobile', '+' . $cleanMobile)
    ->first();

// 2. Trouver le Fleet
$fleet = Fleet::where('mobile', $mobile)
    ->orWhere('mobile', '01010101')
    ->first();

if (!$fleet) {
    echo "ERREUR: Compte Fleet non trouvé pour $mobile !\n";
    // Création de secours
    $fleet = Fleet::create([
        'name' => 'Big Owner',
        'email' => 'owner@picme.com', // Fallback
        'password' => bcrypt('123456'),
        'mobile' => '+22501010101',
        // 'type' => 'MANAGED', // Removed to avoid truncation error if enum doesn't match
        'company' => 'PicMe Transport'
    ]);
    echo "Compte Fleet CREE.\n";
} else {
    echo "Compte Fleet TROUVE (ID: {$fleet->id}).\n";
}

if (!$user) {
    echo "Compte User non trouvé. Création...\n";
    $user = User::create([
        'first_name' => 'Big',
        'last_name' => 'Owner',
        'email' => 'bigowner@test.com',
        'mobile' => '+22501010101',
        'password' => bcrypt('123456'),
        'payment_mode' => 'CASH'
    ]);
} else {
    echo "Compte User TROUVE (ID: {$user->id}).\n";
}

// 3. FORCER LE LIEN ET LE TYPE
$user->user_type = 'FLEET_OWNER';
$user->fleet_id = $fleet->id;
$user->save();

$fleet->user_id = $user->id;
$fleet->save();

echo "SUCCES: Compte {$user->mobile} mis à jour en FLEET_OWNER (Fleet ID: {$user->fleet_id}).\n";
echo "Le Dashboard Fleet devrait s'ouvrir maintenant.\n";
