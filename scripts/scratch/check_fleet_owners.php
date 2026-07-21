<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n========================================\n";
echo "   ANALYSE COMPTES FLEET OWNERS\n";
echo "========================================\n\n";

// Vérifier les Fleets
echo "📊 FLEETS dans la table 'fleets':\n";
echo "========================================\n";
$fleets = DB::table('fleets')->get();
echo "Total: " . $fleets->count() . "\n\n";

foreach ($fleets as $fleet) {
    echo "ID: {$fleet->id}\n";
    echo "  Name: {$fleet->name}\n";
    echo "  Email: {$fleet->email}\n";
    echo "  Mobile: {$fleet->mobile}\n";
    echo "  Company: {$fleet->company}\n";
    if (isset($fleet->type)) {
        echo "  Type: {$fleet->type}\n";
    }
    if (isset($fleet->user_id)) {
        echo "  User ID lié: {$fleet->user_id}\n";
    }
    echo "  ---\n";
}

echo "\n========================================\n";
echo "📊 USERS avec user_type = 'FLEET_OWNER':\n";
echo "========================================\n";
$fleetUsers = DB::table('users')->where('user_type', 'FLEET_OWNER')->get();
echo "Total: " . $fleetUsers->count() . "\n\n";

foreach ($fleetUsers as $user) {
    echo "ID: {$user->id}\n";
    echo "  Name: {$user->first_name} {$user->last_name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Mobile: {$user->mobile}\n";
    echo "  User Type: {$user->user_type}\n";
    if (isset($user->fleet_id)) {
        echo "  Fleet ID lié: {$user->fleet_id}\n";
    }
    echo "  ---\n";
}

echo "\n========================================\n";
echo "🔍 ANALYSE DE LIAISON:\n";
echo "========================================\n";

// Vérifier les liaisons
$fleetsWithUser = DB::table('fleets')->whereNotNull('user_id')->count();
$usersWithFleet = DB::table('users')->where('user_type', 'FLEET_OWNER')->whereNotNull('fleet_id')->count();

echo "Fleets avec user_id: {$fleetsWithUser}\n";
echo "Users FLEET_OWNER avec fleet_id: {$usersWithFleet}\n";

echo "\n========================================\n";
echo "💡 RECOMMANDATION:\n";
echo "========================================\n";

if ($fleets->count() > 0) {
    echo "✅ Vous avez déjà une table 'fleets' avec {$fleets->count()} fleet(s)\n";
    echo "✅ Le système UnifiedAuth gère automatiquement la connexion\n";
    echo "\n📱 Pour vous connecter:\n";
    echo "   - Utilisez votre numéro mobile Fleet\n";
    echo "   - Le système créera automatiquement un compte User lié\n";
    echo "   - Vous aurez accès aux deux rôles: USER et FLEET_OWNER\n";
} else {
    echo "⚠️  Aucun Fleet Owner trouvé dans la table 'fleets'\n";
    echo "   Vous devez créer un compte Fleet Owner via l'admin\n";
}

echo "\n========================================\n\n";
