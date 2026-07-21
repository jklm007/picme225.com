<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "\n========================================\n";
echo "   TEST DE CONNEXION FLEET OWNER\n";
echo "========================================\n\n";

// Récupérer tous les Fleets avec leurs informations
$fleets = DB::table('fleets')->get();

echo "📋 LISTE DES COMPTES FLEET DISPONIBLES:\n";
echo "========================================\n\n";

foreach ($fleets as $fleet) {
    echo "🏢 Fleet ID: {$fleet->id}\n";
    echo "   Nom: {$fleet->name}\n";
    echo "   Email: {$fleet->email}\n";
    echo "   Mobile: " . ($fleet->mobile ?: '❌ NON DÉFINI') . "\n";
    echo "   Company: " . ($fleet->company ?: 'N/A') . "\n";

    if (isset($fleet->type)) {
        echo "   Type: {$fleet->type}\n";
    }

    if (isset($fleet->user_id) && $fleet->user_id) {
        echo "   ✅ Lié au User ID: {$fleet->user_id}\n";

        // Récupérer les infos du user
        $user = DB::table('users')->where('id', $fleet->user_id)->first();
        if ($user) {
            echo "      → User: {$user->first_name} {$user->last_name}\n";
            echo "      → User Mobile: {$user->mobile}\n";
        }
    } else {
        echo "   ⚠️  Pas de User lié (sera créé automatiquement à la connexion)\n";
    }

    // Vérifier si le mot de passe existe
    if (!empty($fleet->password)) {
        echo "   ✅ Mot de passe défini\n";
    } else {
        echo "   ❌ PAS DE MOT DE PASSE!\n";
    }

    echo "   ---\n\n";
}

echo "\n========================================\n";
echo "   INSTRUCTIONS DE TEST\n";
echo "========================================\n\n";

echo "Pour tester la connexion, utilisez Postman ou curl :\n\n";

echo "POST http://localhost:8000/api/unified-login\n";
echo "Content-Type: application/json\n\n";

echo "Body:\n";
echo "{\n";
echo "  \"mobile\": \"22501010101\",\n";
echo "  \"password\": \"votre_mot_de_passe\",\n";
echo "  \"device_id\": \"test_device_123\",\n";
echo "  \"device_type\": \"android\",\n";
echo "  \"device_token\": \"test_fcm_token\"\n";
echo "}\n\n";

echo "========================================\n";
echo "   CRÉATION D'UN FLEET DE TEST\n";
echo "========================================\n\n";

// Vérifier si un fleet de test existe déjà
$testFleet = DB::table('fleets')->where('email', 'test.fleet@picme.com')->first();

if (!$testFleet) {
    echo "Voulez-vous créer un Fleet de test ? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);

    if (trim($line) == 'y') {
        $testPassword = Hash::make('password123');

        $fleetId = DB::table('fleets')->insertGetId([
            'name' => 'Test Fleet Owner',
            'email' => 'test.fleet@picme.com',
            'mobile' => '22507777777',
            'password' => $testPassword,
            'company' => 'Test Company',
            'type' => 'COMPANY',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "\n✅ Fleet de test créé avec succès!\n";
        echo "   ID: {$fleetId}\n";
        echo "   Mobile: 22507777777\n";
        echo "   Password: password123\n";
        echo "   Email: test.fleet@picme.com\n\n";

        echo "Vous pouvez maintenant vous connecter avec:\n";
        echo "  Mobile: 22507777777\n";
        echo "  Password: password123\n\n";
    }
    fclose($handle);
} else {
    echo "✅ Un Fleet de test existe déjà:\n";
    echo "   ID: {$testFleet->id}\n";
    echo "   Mobile: {$testFleet->mobile}\n";
    echo "   Email: {$testFleet->email}\n";
    echo "   Password: password123 (si non modifié)\n\n";
}

echo "\n========================================\n";
echo "   VÉRIFICATION DES ROUTES API\n";
echo "========================================\n\n";

// Vérifier que la route unified-login existe
$routeFile = file_get_contents(__DIR__ . '/routes/api.php');
if (strpos($routeFile, 'unified-login') !== false) {
    echo "✅ Route /api/unified-login existe\n";
} else {
    echo "❌ Route /api/unified-login NON TROUVÉE!\n";
}

// Vérifier les routes fleet
if (strpos($routeFile, "prefix' => 'fleet'") !== false) {
    echo "✅ Routes /api/fleet/* existent\n";
} else {
    echo "❌ Routes /api/fleet/* NON TROUVÉES!\n";
}

echo "\n========================================\n\n";
