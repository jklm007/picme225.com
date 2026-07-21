<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   CORRECTION DOUBLONS & CRÉATION FLEET DE TEST             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// ============================================
// ÉTAPE 1 : ANALYSE DES DOUBLONS
// ============================================

echo "📊 ÉTAPE 1 : Analyse des doublons de mobile\n";
echo "════════════════════════════════════════════════════════════\n\n";

$fleets = DB::table('fleets')->get();

echo "Fleets actuels :\n";
foreach ($fleets as $fleet) {
    $mobile = $fleet->mobile ?: 'NON DÉFINI';
    echo "  - ID {$fleet->id}: {$fleet->name} → Mobile: {$mobile}\n";
}

echo "\n";

// Normaliser les numéros pour détecter les doublons
$mobileMap = [];
foreach ($fleets as $fleet) {
    if (!$fleet->mobile)
        continue;

    // Normaliser le numéro
    $normalized = preg_replace('/^\\+/', '', $fleet->mobile);
    $normalized = preg_replace('/^00225/', '', $normalized);
    $normalized = preg_replace('/^225/', '', $normalized);

    if (!isset($mobileMap[$normalized])) {
        $mobileMap[$normalized] = [];
    }
    $mobileMap[$normalized][] = $fleet;
}

// Trouver les doublons
$duplicates = array_filter($mobileMap, function ($fleets) {
    return count($fleets) > 1;
});

if (count($duplicates) > 0) {
    echo "⚠️  DOUBLONS DÉTECTÉS :\n\n";

    foreach ($duplicates as $normalized => $duplicateFleets) {
        echo "  Numéro normalisé: {$normalized}\n";
        foreach ($duplicateFleets as $fleet) {
            echo "    → Fleet ID {$fleet->id}: {$fleet->name} (Mobile: {$fleet->mobile})\n";
        }
        echo "\n";
    }
} else {
    echo "✅ Aucun doublon détecté\n\n";
}

// ============================================
// ÉTAPE 2 : CORRECTION DES DOUBLONS
// ============================================

echo "🔧 ÉTAPE 2 : Correction des doublons\n";
echo "════════════════════════════════════════════════════════════\n\n";

if (count($duplicates) > 0) {
    foreach ($duplicates as $normalized => $duplicateFleets) {
        // Garder le premier avec user_id, sinon le premier tout court
        $toKeep = null;
        $toUpdate = [];

        foreach ($duplicateFleets as $fleet) {
            if ($toKeep === null) {
                $toKeep = $fleet;
            } elseif (isset($fleet->user_id) && $fleet->user_id && (!isset($toKeep->user_id) || !$toKeep->user_id)) {
                $toUpdate[] = $toKeep;
                $toKeep = $fleet;
            } else {
                $toUpdate[] = $fleet;
            }
        }

        echo "  📌 Pour le numéro {$normalized} :\n";
        echo "     ✅ Garder : Fleet ID {$toKeep->id} ({$toKeep->name})\n";

        foreach ($toUpdate as $fleet) {
            // Générer un nouveau numéro unique
            $newMobile = '225' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

            DB::table('fleets')
                ->where('id', $fleet->id)
                ->update(['mobile' => $newMobile]);

            echo "     🔄 Modifier : Fleet ID {$fleet->id} ({$fleet->name})\n";
            echo "        Ancien mobile: {$fleet->mobile}\n";
            echo "        Nouveau mobile: {$newMobile}\n";
        }
        echo "\n";
    }

    echo "✅ Doublons corrigés avec succès!\n\n";
} else {
    echo "✅ Aucune correction nécessaire\n\n";
}

// ============================================
// ÉTAPE 3 : CRÉATION DU FLEET DE TEST
// ============================================

echo "🏗️  ÉTAPE 3 : Création du Fleet de test\n";
echo "════════════════════════════════════════════════════════════\n\n";

// Vérifier si un fleet de test existe déjà
$testFleet = DB::table('fleets')->where('email', 'test.fleet@picme.com')->first();

if ($testFleet) {
    echo "⚠️  Un Fleet de test existe déjà (ID: {$testFleet->id})\n";
    echo "   Voulez-vous le supprimer et en créer un nouveau ? (y/n): ";

    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);

    if ($line === 'y') {
        // Supprimer l'ancien
        DB::table('fleets')->where('id', $testFleet->id)->delete();

        // Supprimer le user lié si existe
        if (isset($testFleet->user_id) && $testFleet->user_id) {
            DB::table('users')->where('id', $testFleet->user_id)->delete();
        }

        echo "   ✅ Ancien Fleet de test supprimé\n\n";
        $testFleet = null;
    } else {
        echo "   ℹ️  Conservation du Fleet existant\n\n";
    }
}

if (!$testFleet) {
    // Créer le nouveau Fleet de test
    $testPassword = Hash::make('FleetTest2026!');
    $testMobile = '22599999999';

    $fleetId = DB::table('fleets')->insertGetId([
        'name' => 'Fleet Owner Test',
        'email' => 'test.fleet@picme.com',
        'mobile' => $testMobile,
        'password' => $testPassword,
        'company' => 'Test Transport Company',
        'type' => 'COMPANY',
        'logo' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "✅ Fleet de test créé avec succès!\n\n";
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║           CREDENTIALS DU FLEET DE TEST                     ║\n";
    echo "╠════════════════════════════════════════════════════════════╣\n";
    echo "║  Fleet ID      : {$fleetId}                                          ║\n";
    echo "║  Nom           : Fleet Owner Test                          ║\n";
    echo "║  Email         : test.fleet@picme.com                      ║\n";
    echo "║  Mobile        : {$testMobile}                                  ║\n";
    echo "║  Password      : FleetTest2026!                            ║\n";
    echo "║  Company       : Test Transport Company                    ║\n";
    echo "║  Type          : COMPANY                                   ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    echo "\n";

    $testFleet = DB::table('fleets')->where('id', $fleetId)->first();
}

// ============================================
// ÉTAPE 4 : VÉRIFICATION PASSPORT
// ============================================

echo "🔐 ÉTAPE 4 : Vérification Laravel Passport\n";
echo "════════════════════════════════════════════════════════════\n\n";

// Vérifier les clients OAuth
$oauthClients = DB::table('oauth_clients')->get();

echo "Clients OAuth Passport :\n";
foreach ($oauthClients as $client) {
    $type = $client->password_client ? 'Password Client' : ($client->personal_access_client ? 'Personal Access Client' : 'Other');
    echo "  ✅ ID {$client->id}: {$client->name} ({$type})\n";
}
echo "\n";

// Vérifier que les clés Passport existent
$privateKeyPath = storage_path('oauth-private.key');
$publicKeyPath = storage_path('oauth-public.key');

if (file_exists($privateKeyPath) && file_exists($publicKeyPath)) {
    echo "✅ Clés Passport trouvées\n";
    echo "   - Private key: {$privateKeyPath}\n";
    echo "   - Public key: {$publicKeyPath}\n\n";
} else {
    echo "⚠️  Clés Passport manquantes!\n";
    echo "   Exécutez: php artisan passport:keys\n\n";
}

// ============================================
// ÉTAPE 5 : TEST DE CONNEXION SIMULÉ
// ============================================

echo "🧪 ÉTAPE 5 : Simulation de connexion\n";
echo "════════════════════════════════════════════════════════════\n\n";

if ($testFleet) {
    echo "Pour tester la connexion avec le Fleet de test :\n\n";

    echo "1️⃣  Via Postman ou cURL :\n";
    echo "────────────────────────────────────────────────────────────\n";
    echo "POST http://localhost:8000/api/unified-login\n";
    echo "Content-Type: application/json\n\n";
    echo "{\n";
    echo "  \"mobile\": \"{$testFleet->mobile}\",\n";
    echo "  \"password\": \"FleetTest2026!\",\n";
    echo "  \"device_id\": \"test_device_123\",\n";
    echo "  \"device_type\": \"android\",\n";
    echo "  \"device_token\": \"test_fcm_token_456\"\n";
    echo "}\n\n";

    echo "2️⃣  Réponse attendue :\n";
    echo "────────────────────────────────────────────────────────────\n";
    echo "{\n";
    echo "  \"access_token\": \"eyJ0eXAiOiJKV1QiLCJhbGc...\",\n";
    echo "  \"token_type\": \"Bearer\",\n";
    echo "  \"account_type\": \"FLEET_OWNER\",\n";
    echo "  \"user\": { ... },\n";
    echo "  \"available_roles\": [\"USER\", \"FLEET_OWNER\"],\n";
    echo "  \"fleet_data\": {\n";
    echo "    \"id\": {$testFleet->id},\n";
    echo "    \"name\": \"{$testFleet->name}\",\n";
    echo "    \"type\": \"COMPANY\",\n";
    echo "    \"company\": \"{$testFleet->company}\"\n";
    echo "  }\n";
    echo "}\n\n";

    echo "3️⃣  Accès au Dashboard Fleet :\n";
    echo "────────────────────────────────────────────────────────────\n";
    echo "GET http://localhost:8000/api/fleet/dashboard\n";
    echo "Authorization: Bearer {access_token}\n\n";
}

// ============================================
// ÉTAPE 6 : RÉSUMÉ FINAL
// ============================================

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    RÉSUMÉ FINAL                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$allFleets = DB::table('fleets')->get();

echo "📊 Total Fleets : " . $allFleets->count() . "\n\n";

foreach ($allFleets as $fleet) {
    $hasUser = isset($fleet->user_id) && $fleet->user_id ? "✅ User ID {$fleet->user_id}" : "⚠️  Pas de User";
    $mobile = $fleet->mobile ?: '❌ NON DÉFINI';

    echo "  🏢 Fleet ID {$fleet->id}: {$fleet->name}\n";
    echo "     Mobile: {$mobile}\n";
    echo "     Email: {$fleet->email}\n";
    echo "     Liaison: {$hasUser}\n";
    echo "\n";
}

echo "════════════════════════════════════════════════════════════\n";
echo "✅ Configuration terminée avec succès!\n";
echo "════════════════════════════════════════════════════════════\n\n";

// Sauvegarder les credentials dans un fichier
$credentialsFile = __DIR__ . '/FLEET_TEST_CREDENTIALS.txt';
$credentials = "╔════════════════════════════════════════════════════════════╗\n";
$credentials .= "║         CREDENTIALS FLEET DE TEST - PICME PRO              ║\n";
$credentials .= "╠════════════════════════════════════════════════════════════╣\n";
$credentials .= "║  Mobile        : {$testFleet->mobile}                                  ║\n";
$credentials .= "║  Password      : FleetTest2026!                            ║\n";
$credentials .= "║  Email         : test.fleet@picme.com                      ║\n";
$credentials .= "║  Fleet ID      : {$testFleet->id}                                          ║\n";
$credentials .= "║                                                            ║\n";
$credentials .= "║  Endpoint      : POST /api/unified-login                   ║\n";
$credentials .= "║  Dashboard     : GET /api/fleet/dashboard                  ║\n";
$credentials .= "╚════════════════════════════════════════════════════════════╝\n";

file_put_contents($credentialsFile, $credentials);

echo "💾 Credentials sauvegardés dans: FLEET_TEST_CREDENTIALS.txt\n\n";
