<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n========================================\n";
echo "   STATISTIQUES BASE DE DONNÉES PICME\n";
echo "========================================\n\n";

// Users
$usersCount = DB::table('users')->count();
echo "👤 USERS (Clients): " . $usersCount . "\n";

// Providers (Drivers)
$providersCount = DB::table('providers')->count();
echo "🚗 PROVIDERS (Drivers): " . $providersCount . "\n";

// Admins
$adminsCount = DB::table('admins')->count();
echo "👨‍💼 ADMINS: " . $adminsCount . "\n";

// Dispatchers
try {
    $dispatchersCount = DB::table('dispatchers')->count();
    echo "📞 DISPATCHERS: " . $dispatchersCount . "\n";
} catch (Exception $e) {
    echo "📞 DISPATCHERS: Table non trouvée\n";
}

// Fleet Owners
try {
    $fleetOwnersCount = DB::table('fleet_owners')->count();
    echo "🚙 FLEET OWNERS: " . $fleetOwnersCount . "\n";
} catch (Exception $e) {
    echo "🚙 FLEET OWNERS: Table non trouvée\n";
}

// OAuth Clients
$oauthClientsCount = DB::table('oauth_clients')->count();
echo "🔑 OAUTH CLIENTS: " . $oauthClientsCount . "\n";

// OAuth Access Tokens
$oauthTokensCount = DB::table('oauth_access_tokens')->count();
echo "🎫 OAUTH ACCESS TOKENS: " . $oauthTokensCount . "\n";

echo "\n========================================\n";
echo "   DÉTAILS OAUTH CLIENTS\n";
echo "========================================\n\n";

$clients = DB::table('oauth_clients')->get();
foreach ($clients as $client) {
    echo "ID: {$client->id}\n";
    echo "  Name: {$client->name}\n";
    echo "  Secret: " . substr($client->secret, 0, 20) . "...\n";
    echo "  Redirect: {$client->redirect}\n";
    echo "  Personal Access Client: " . ($client->personal_access_client ? 'Yes' : 'No') . "\n";
    echo "  Password Client: " . ($client->password_client ? 'Yes' : 'No') . "\n";
    echo "  Revoked: " . ($client->revoked ? 'Yes' : 'No') . "\n";
    echo "  ---\n";
}

echo "\n========================================\n";
echo "   RÉPARTITION DES USERS PAR TYPE\n";
echo "========================================\n\n";

try {
    $usersByType = DB::table('users')
        ->select('user_type', DB::raw('count(*) as total'))
        ->groupBy('user_type')
        ->get();

    foreach ($usersByType as $type) {
        echo "  {$type->user_type}: {$type->total}\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
echo "   PROVIDERS PAR STATUT\n";
echo "========================================\n\n";

try {
    $providersByStatus = DB::table('providers')
        ->select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->get();

    foreach ($providersByStatus as $status) {
        echo "  Status {$status->status}: {$status->total}\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}

echo "\n========================================\n\n";
